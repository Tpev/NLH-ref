<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReferralReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $referrals = Referral::with('progress')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalReferrals = $referrals->count();
        $statusCounts = [
            'received' => 0,
            'in_progress' => 0,
            'Sent_to_Scheduling' => 0,
        ];

        foreach ($referrals as $referral) {
            $progressSteps = $referral->progress;

            if ($progressSteps->isEmpty()) {
                $statusCounts['received']++;
            } else {
                $step3Completed = $progressSteps->firstWhere('workflow_step_id', 3)?->status === 'completed';
                if ($step3Completed) {
                    $statusCounts['Sent_to_Scheduling']++;
                } else {
                    $statusCounts['in_progress']++;
                }
            }
        }

        $referralProgressByUser = ReferralProgress::whereBetween('completed_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get()
            ->groupBy('completed_by')
            ->map(function ($progressCollection, $userId) {
                return [
                    'user' => User::find($userId)?->name ?? 'Unknown',
                    'count' => $progressCollection->pluck('referral_id')->unique()->count()
                ];
            })
            ->values();

        $anesthesiaSubmissions = ReferralProgress::where('workflow_step_id', 2)
            ->where('status', 'completed')
            ->where('notes', 'Yes')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->count();

        $sentToScheduling = ReferralProgress::where('workflow_step_id', 3)
            ->where('status', 'completed')
            ->where('notes', 'Yes')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->count();

        $procedures = $referrals->flatMap(fn($r) => explode(', ', $r->procedure))->filter()->countBy();

        $durations = $referrals->map(function ($referral) {
            $completedAt = $referral->progress
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->sortBy('completed_at')
                ->first()?->completed_at;

            return $completedAt ? $referral->created_at->diffInDays($completedAt) : null;
        })->filter()->values();

        $averageDuration = $durations->avg();
        $medianDuration = $durations->count() ? $durations->median() : null;
        $percentUnder5Days = $durations->count()
            ? round($durations->filter(fn($d) => $d <= 5)->count() / $durations->count() * 100)
            : 0;

        $avgFirstActionDelay = $referrals->map(function ($referral) {
            $firstStep = $referral->progress->sortBy('completed_at')->first()?->completed_at;
            return $firstStep ? $referral->created_at->diffInDays($firstStep) : null;
        })->filter()->avg();

        $openReferrals = $referrals->filter(fn($r) => $r->progress->isEmpty());
        $oldestOpenDays = $openReferrals->isEmpty()
            ? null
            : $openReferrals->map(fn($r) => max(0, $r->created_at->diffInDays(now())))->max() ?? 0;

        $statuses = ['received', 'in_progress', 'Sent_to_Scheduling'];
        $weeklyStackedReferrals = [];

        $referrals->each(function ($referral) use (&$weeklyStackedReferrals, $statuses) {
            $weekLabel = $referral->created_at->startOfWeek()->format('M d');

            if (!isset($weeklyStackedReferrals[$weekLabel])) {
                $weeklyStackedReferrals[$weekLabel] = array_fill_keys($statuses, 0);
            }

            $progressSteps = $referral->progress;

            if ($progressSteps->isEmpty()) {
                $weeklyStackedReferrals[$weekLabel]['received']++;
            } else {
                $step3Completed = $progressSteps->firstWhere('workflow_step_id', 3)?->status === 'completed';
                if ($step3Completed) {
                    $weeklyStackedReferrals[$weekLabel]['Sent_to_Scheduling']++;
                } else {
                    $weeklyStackedReferrals[$weekLabel]['in_progress']++;
                }
            }
        });

        return view('reports.referrals', [
            'month' => $startDate,
            'totalReferrals' => $totalReferrals,
            'statusCounts' => $statusCounts,
            'anesthesiaSubmissions' => $anesthesiaSubmissions,
            'procedures' => $procedures,
            'averageDuration' => round($averageDuration, 1),
            'medianDuration' => round($medianDuration, 1),
            'percentUnder5Days' => $percentUnder5Days,
            'avgFirstActionDelay' => round($avgFirstActionDelay, 1),
            'oldestOpenDays' => round($oldestOpenDays, 1),
            'sentToScheduling' => $sentToScheduling,
            'referralProgressByUser' => $referralProgressByUser,
            'weeklyStackedReferrals' => $weeklyStackedReferrals,
        ]);
    }
}
