<?php

namespace App\Http\Controllers;

use App\Models\SegmentationRun;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard: high-level stats + recent segmentation runs for the
     * logged-in user (or all runs, if admin).
     */
    public function index()
    {
        $query = SegmentationRun::query()->orderByDesc('created_at');

        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $recentRuns = $query->limit(8)->get();

        $totalRuns = (clone $query)->count();
        $completeRuns = (clone $query)->where('status', SegmentationRun::STATUS_COMPLETE)->count();
        $totalRecordsSegmented = (clone $query)
            ->where('status', SegmentationRun::STATUS_COMPLETE)
            ->sum('total_records');

        return view('dashboard', [
            'recentRuns' => $recentRuns,
            'totalRuns' => $totalRuns,
            'completeRuns' => $completeRuns,
            'totalRecordsSegmented' => $totalRecordsSegmented,
        ]);
    }

    /**
     * About / project history page — the "hire-me pitch" narrative page.
     */
    public function about()
    {
        return view('about');
    }
}
