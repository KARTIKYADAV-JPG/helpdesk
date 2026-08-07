<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all key dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        $totalTickets = $this->getTotalTickets();
        $openTickets = $this->getOpenTickets();
        $aiResolvedTickets = $this->getAiResolvedTickets();
        $aiResolutionPercentage = $this->getAiResolutionPercentage($totalTickets, $aiResolvedTickets);
        $avgResolutionTime = $this->getAverageResolutionTimeFormatted();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'ai_resolved_tickets' => $aiResolvedTickets,
            'ai_resolution_percentage' => $aiResolutionPercentage,
            'average_resolution_time' => $avgResolutionTime,
        ];
    }

    /**
     * Total Tickets count.
     */
    public function getTotalTickets(): int
    {
        return Ticket::count();
    }

    /**
     * Open Tickets count (status: new, open, processing).
     */
    public function getOpenTickets(): int
    {
        return Ticket::whereIn('status', ['new', 'open', 'processing'])->count();
    }

    /**
     * AI Resolved Tickets count (status: resolved/closed AND assigned to AI agent or containing AI summary/resolution).
     */
    public function getAiResolvedTickets(): int
    {
        $aiAgent = User::where('email', 'ai@helpdesk.com')->orWhere('name', 'AI')->first();
        $aiAgentId = $aiAgent ? $aiAgent->id : null;

        return Ticket::whereIn('status', ['resolved', 'closed'])
            ->where(function ($query) use ($aiAgentId) {
                if ($aiAgentId) {
                    $query->where('assigned_to', $aiAgentId)
                          ->orWhereNotNull('summary');
                } else {
                    $query->whereNotNull('summary');
                }
            })
            ->count();
    }

    /**
     * Calculate AI resolution percentage: (ai_resolved / total) * 100.
     */
    public function getAiResolutionPercentage(int $totalTickets, int $aiResolvedTickets): float
    {
        if ($totalTickets === 0) {
            return 0.0;
        }

        return round(($aiResolvedTickets / $totalTickets) * 100, 1);
    }

    /**
     * Calculate average resolution time between created_at and resolved_at (or updated_at fallback).
     */
    public function getAverageResolutionSeconds(): ?float
    {
        $resolvedTickets = Ticket::whereIn('status', ['resolved', 'closed'])
            ->orWhereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at', 'updated_at']);

        if ($resolvedTickets->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        $count = 0;

        foreach ($resolvedTickets as $ticket) {
            $endTime = $ticket->resolved_at ?? $ticket->updated_at;
            if ($ticket->created_at && $endTime) {
                $diff = abs($endTime->diffInSeconds($ticket->created_at));
                $totalSeconds += $diff;
                $count++;
            }
        }

        return $count > 0 ? (float) ($totalSeconds / $count) : null;
    }

    /**
     * Format average resolution time into a human-readable string.
     */
    public function getAverageResolutionTimeFormatted(): string
    {
        $seconds = $this->getAverageResolutionSeconds();

        if ($seconds === null || $seconds < 0) {
            return 'N/A';
        }

        $seconds = (int) round($seconds);

        if ($seconds < 60) {
            return "{$seconds} secs";
        }

        $minutes = (int) floor($seconds / 60);
        if ($minutes < 60) {
            $remSecs = $seconds % 60;
            return $remSecs > 0 ? "{$minutes}m {$remSecs}s" : "{$minutes} mins";
        }

        $hours = (int) floor($minutes / 60);
        $remMins = $minutes % 60;

        if ($hours < 24) {
            return $remMins > 0 ? "{$hours}h {$remMins}m" : "{$hours} hours";
        }

        $days = (int) floor($hours / 24);
        $remHours = $hours % 24;

        return $remHours > 0 ? "{$days}d {$remHours}h" : "{$days} days";
    }

    /**
     * Get daily ticket trends for the last 30 days using database aggregation.
     *
     * @param int $days
     * @return array{labels: array<string>, data: array<int>}
     */
    public function getDailyTicketTrend(int $days = 30): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        // Database aggregation for tickets created per day
        $rawCounts = Ticket::where('created_at', '>=', $startDate)
            ->selectRaw("DATE(created_at) as date_key, COUNT(*) as total")
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date_key', 'asc')
            ->pluck('total', 'date_key')
            ->toArray();

        $labels = [];
        $data = [];

        // Build continuous 30-day timeline with formatted dates (e.g. Jul 1)
        for ($i = $days - 1; $i >= 0; $i--) {
            $carbonDate = now()->subDays($i);
            $key = $carbonDate->format('Y-m-d');
            $label = $carbonDate->format('M j');

            $labels[] = $label;
            $data[] = (int) ($rawCounts[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
