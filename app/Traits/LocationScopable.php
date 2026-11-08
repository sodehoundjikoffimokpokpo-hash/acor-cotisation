<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait LocationScopable
{
    /**
     * Scope a query to only include items belonging to the active country, region or zone.
     */
    public function scopeByLocation(Builder $query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query;
        }

        // If user is super admin (global), check if session filters are set
        if ($user->isAdmin() && !$user->country_id) {
            if (session()->has('active_country_id')) {
                $query->where($this->getTable() . '.country_id', session('active_country_id'));
            }
            if (session()->has('active_region_id')) {
                $query->where($this->getTable() . '.region_id', session('active_region_id'));
            }
            if (session()->has('active_zone_id')) {
                $query->where($this->getTable() . '.zone_id', session('active_zone_id'));
            }
            return $query;
        }

        // For local managers (like secretary or local admin), filter by their profile location
        if ($user->zone_id) {
            return $query->where($this->getTable() . '.zone_id', $user->zone_id);
        }
        if ($user->region_id) {
            return $query->where($this->getTable() . '.region_id', $user->region_id);
        }
        if ($user->country_id) {
            return $query->where($this->getTable() . '.country_id', $user->country_id);
        }

        return $query;
    }
}
