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
     * AI Resolved Tickets count (status: resolved AND assigned_to: AI agent ID).
     */
    public function getAiResolvedTickets(): int
    {
        $aiAgent = User::where('email', 'ai@helpdesk.com')->first();
        if (!$aiAgent) {
            return 0;
        }

        return Ticket::where('status', 'resolved')
            ->where('assigned_to', $aiAgent->id)
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
     * Calculate average resolution time between created_at and resolved_at.
     */
    public function getAverageResolutionSeconds(): ?float
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $avgSeconds = Ticket::whereNotNull('resolved_at')
                ->selectRaw("AVG(STRFTIME('%s', resolved_at) - STRFTIME('%s', created_at)) as avg_sec")
                ->value('avg_sec');
        } else {
            $avgSeconds = Ticket::whereNotNull('resolved_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))) as avg_sec')
                ->value('avg_sec');
        }

        return $avgSeconds !== null ? (float) $avgSeconds : null;
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
