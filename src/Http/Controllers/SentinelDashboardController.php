<?php

namespace PicoBaz\Sentinel\Http\Controllers;

use Illuminate\Routing\Controller;
use PicoBaz\Sentinel\Facades\Sentinel;

class SentinelDashboardController extends Controller
{
    public function index()
    {
        $statistics = Sentinel::getStatistics();
        $recentLogs = Sentinel::getMetrics(null, 24);

        return view('sentinel::dashboard', compact('statistics', 'recentLogs'));
    }

    public function metrics($type = null)
    {
        $metrics = Sentinel::getMetrics($type, request('hours', 24));
        
        return response()->json($metrics);
    }
}
