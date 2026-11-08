<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCollectorRequest;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CollectorManagementController extends Controller
{
    /**
     * Display a listing of the collectors.
     */
    public function collectors()
    {
        $collectors = User::byLocation()->with('country')->where('role', 'collector')->orderBy('created_at', 'desc')->get();
        return view('admin.collectors', compact('collectors'));
    }

    /**
     * Store a new collector.
     */
    public function storeCollector(StoreCollectorRequest $request)
    {
        $country = \App\Models\Country::where('name', 'LIKE', '%' . $request->activity_country . '%')->first();
        $authCountryId = Auth::user()->country_id;
        $countryId = $authCountryId ?: ($country ? $country->id : null);
        $activityCountry = $authCountryId ? Auth::user()->activity_country : ($country ? $country->name : $request->activity_country);

        $collector = User::create([
            'name' => strtoupper($request->name),
            'firstname' => $request->firstname,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'collector',
            'activity_zone' => $request->activity_zone,
            'activity_country' => $activityCountry,
            'country_id' => $countryId,
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_COLLECTOR',
            'description' => "Création du compte collecteur pour {$collector->firstname} {$collector->name} (Identifiant: {$collector->username}) pour le pays {$activityCountry}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.collectors')->with('success', 'Collecteur créé avec succès.');
    }

    /**
     * Update collector info.
     */
    public function updateCollector(Request $request, $id)
    {
        $collector = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'activity_zone' => ['required', 'string', 'max:255'],
            'activity_country' => [Auth::user()->country_id ? 'nullable' : 'required', 'string', 'max:255'],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ]);

        $country = \App\Models\Country::where('name', 'LIKE', '%' . $request->activity_country . '%')->first();
        $authCountryId = Auth::user()->country_id;
        $countryId = $authCountryId ?: ($country ? $country->id : null);
        $activityCountry = $authCountryId ? Auth::user()->activity_country : ($country ? $country->name : $request->activity_country);

        $data = [
            'name' => strtoupper($request->name),
            'firstname' => $request->firstname,
            'activity_zone' => $request->activity_zone,
            'activity_country' => $activityCountry,
            'country_id' => $countryId,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $collector->update($data);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_COLLECTOR',
            'description' => "Mise à jour des informations du collecteur {$collector->firstname} {$collector->name} pour le pays {$activityCountry}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.collectors')->with('success', 'Collecteur mis à jour avec succès.');
    }

    /**
     * Toggle active status for collector.
     */
    public function toggleCollector(Request $request, $id)
    {
        $collector = User::findOrFail($id);
        $collector->is_active = !$collector->is_active;
        $collector->save();

        $statusStr = $collector->is_active ? 'activé' : 'désactivé';

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'TOGGLE_COLLECTOR',
            'description' => "Le statut du compte collecteur {$collector->firstname} {$collector->name} a été {$statusStr}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.collectors')->with('success', "Le compte collecteur a été {$statusStr} avec succès.");
    }
}
