<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $activities = \Spatie\Activitylog\Models\Activity::whereHas('causer', function($q) {
                $q->byLocation();
            })
            ->latest()
            ->paginate(20);
        return view('admin.logs', compact('activities'));
    }
}
