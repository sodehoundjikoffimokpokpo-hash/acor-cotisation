<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display geographical management screen.
     */
    public function locations()
    {
        $countries = \App\Models\Country::with('regions.zones')->get();
        $regions = \App\Models\Region::with('country')->get();
        return view('admin.locations', compact('countries', 'regions'));
    }

    /**
     * Store a new country.
     */
    public function storeCountry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:countries,name|max:255',
            'currency_code' => 'required|string|max:10',
        ]);

        $country = \App\Models\Country::create($request->only(['name', 'currency_code']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_COUNTRY',
            'description' => "Création du pays {$country->name} (Devise: {$country->currency_code}).",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', __('messages.country_create_success', ['name' => $country->name]));
    }

    /**
     * Store a new region.
     */
    public function storeRegion(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
        ]);

        $region = \App\Models\Region::create($request->only(['country_id', 'name']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_REGION',
            'description' => "Création de la région {$region->name} pour le pays ID {$region->country_id}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', __('messages.region_create_success', ['name' => $region->name]));
    }

    /**
     * Store a new zone.
     */
    public function storeZone(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
        ]);

        $zone = \App\Models\Zone::create($request->only(['region_id', 'name']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_ZONE',
            'description' => "Création de la zone {$zone->name} pour la région ID {$zone->region_id}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', __('messages.zone_create_success', ['name' => $zone->name]));
    }
}
