<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with metrics and trend charts.
     */
    public function index(DashboardService $dashboardService): View
    {
        $metrics = $dashboardService->getMetrics();
        $dailyTrend = $dashboardService->getDailyTicketTrend(30);

        return view('dashboard', compact('metrics', 'dailyTrend'));
    }
}
