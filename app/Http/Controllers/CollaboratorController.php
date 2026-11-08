<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class CollaboratorController extends Controller
{
    /**
     * Display rights management screen.
     */
    public function rights()
    {
        $users = User::byLocation()
            ->with('country')
            ->where(function ($query) {
                $query->where('role', '!=', 'admin')
                      ->orWhereNotNull('country_id');
            })
            ->orderBy('role', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        return view('admin.rights', compact('users', 'countries'));
    }

    /**
     * Toggle a specific permission for a user.
     */
    public function togglePermission(Request $request, $id, $permission)
    {
        $validPermissions = [
            'can_validate_contributions',
            'can_manage_collectors',
            'can_view_audit_logs',
            'can_manage_lots',
            'can_manage_locations',
            'can_manage_backups'
        ];

        if (!in_array($permission, $validPermissions)) {
            return back()->with('error', 'Permission non valide.');
        }

        $user = User::findOrFail($id);
        $user->{$permission} = !$user->{$permission};
        $user->save();

        $statusStr = $user->{$permission} ? 'octroyé' : 'retiré';
        $permissionLabel = '';

        switch ($permission) {
            case 'can_validate_contributions':
                $permissionLabel = 'Validation Financière';
                break;
            case 'can_manage_collectors':
                $permissionLabel = 'Gestion des Collecteurs';
                break;
            case 'can_view_audit_logs':
                $permissionLabel = 'Consultation de la Traçabilité';
                break;
            case 'can_manage_lots':
                $permissionLabel = 'Gestion des Lots & Tarifs';
                break;
            case 'can_manage_locations':
                $permissionLabel = 'Gestion Géographique';
                break;
            case 'can_manage_backups':
                $permissionLabel = 'Gestion des Sauvegardes';
                break;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'MANAGE_RIGHTS',
            'description' => "Le droit '{$permissionLabel}' a été {$statusStr} pour l'utilisateur {$user->firstname} {$user->name}.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Droit '{$permissionLabel}' mis à jour avec succès pour {$user->firstname} {$user->name}.");
    }

    /**
     * Store a new collaborator.
     */
    public function storeCollaborator(StoreCollaboratorRequest $request)
    {
        $authCountryId = Auth::user()->country_id;
        $countryId = $authCountryId ?: $request->country_id;
        $country = $countryId ? \App\Models\Country::find($countryId) : null;

        $user = User::create([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'username' => $request->username,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'activity_zone' => $request->title, // Mapped to activity_zone for custom employees
            'is_active' => true,
            'can_validate_contributions' => $request->has('can_validate_contributions') || $request->role === 'admin',
            'can_manage_collectors' => $request->has('can_manage_collectors') || $request->role === 'admin',
            'can_view_audit_logs' => $request->has('can_view_audit_logs') || $request->role === 'admin',
            'can_manage_lots' => $request->has('can_manage_lots') || $request->role === 'admin',
            'can_manage_locations' => $request->has('can_manage_locations') || $request->role === 'admin',
            'can_manage_backups' => $request->has('can_manage_backups') || $request->role === 'admin',
            'country_id' => $countryId,
            'activity_country' => $country ? $country->name : null,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_COLLABORATOR',
            'description' => "Création du collaborateur {$user->firstname} {$user->name} avec le rôle {$user->role} (Titre: {$request->title}) pour le pays " . ($country ? $country->name : 'Global/Tous') . ".",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Le collaborateur {$user->firstname} {$user->name} a été créé avec succès.");
    }
}
