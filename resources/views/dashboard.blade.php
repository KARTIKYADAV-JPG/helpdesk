<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight">Admin Dashboard</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Real-time analytics & AI performance') }}</p>
            </div>
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                Live
            </span>
        </div>
    </x-slot>

    <div class="py-6 px-6 space-y-6">

        {{-- ── Metric Cards ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">

            {{-- Total Tickets --}}
            <div class="card-hover bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-5 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                            {{ __('Total Tickets') }}
                        </p>
                        <p class="text-3xl font-black text-slate-800 dark:text-slate-100">
                            {{ number_format($metrics['total_tickets']) }}
                        </p>
                        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">{{ __('All time created') }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-inner">
                        <svg class="h-6 w-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Open Tickets --}}
            <div class="card-hover bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-5 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-3">
                            {{ __('Open Tickets') }}
                        </p>
                        <p class="text-3xl font-black text-amber-700 dark:text-amber-300">
                            {{ number_format($metrics['open_tickets']) }}
                        </p>
                        <p class="mt-1.5 text-xs text-amber-500/80 dark:text-amber-400/80">{{ __('New, open & processing') }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-950/80 shadow-inner">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- AI Resolved --}}
            <div class="card-hover bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-5 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3">
                            {{ __('AI Resolved') }}
                        </p>
                        <p class="text-3xl font-black text-emerald-700 dark:text-emerald-300">
                            {{ number_format($metrics['ai_resolved_tickets']) }}
                        </p>
                        <p class="mt-1.5 text-xs text-emerald-500/80 dark:text-emerald-400/80">{{ __('Auto-resolved via KB') }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 shadow-inner">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- AI Resolution % --}}
            <div class="card-hover bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-5 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400 mb-3">
                            {{ __('AI Resolution %') }}
                        </p>
                        <p class="text-3xl font-black text-violet-700 dark:text-violet-300">
                            {{ $metrics['ai_resolution_percentage'] }}<span class="text-xl">%</span>
                        </p>
                        <p class="mt-1.5 text-xs text-violet-500/80 dark:text-violet-400/80">{{ __('Solved by AI') }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-violet-100 dark:bg-violet-950/80 shadow-inner">
                        <svg class="h-6 w-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Avg Resolution Time --}}
            <div class="card-hover bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-5 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-3">
                            {{ __('Avg Resolution') }}
                        </p>
                        <p class="text-2xl font-black text-blue-700 dark:text-blue-300 truncate leading-tight">
                            {{ $metrics['average_resolution_time'] }}
                        </p>
                        <p class="mt-1.5 text-xs text-blue-500/80 dark:text-blue-400/80">{{ __('Time to resolution') }}</p>
                    </div>
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 dark:bg-blue-950/80 shadow-inner">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Daily Ticket Trend Chart ─────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-6 transition-colors">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-950/80">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                        </span>
                        {{ __('Daily Ticket Volume') }}
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 ml-9">{{ __('Last 30 days — aggregated count of new tickets per day') }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-600/20">
                    {{ __('30-day view') }}
                </span>
            </div>

            <div class="relative h-72 w-full">
                <canvas id="ticketsTrendChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('ticketsTrendChart');
            if (!ctx) return;

            const labels = @json($dailyTrend['labels']);
            const data   = @json($dailyTrend['data']);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Tickets Created',
                        data,
                        backgroundColor: 'rgba(99, 102, 241, 0.80)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1.5,
                        borderRadius: 7,
                        borderSkipped: false,
                        hoverBackgroundColor: 'rgba(79, 70, 229, 1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleFont: { size: 12, weight: 'bold', family: 'Inter' },
                            bodyFont: { size: 12, family: 'Inter' },
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                label: ctx => ctx.parsed.y + ' ticket' + (ctx.parsed.y === 1 ? '' : 's'),
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', maxRotation: 45 }
                        },
                        y: {
                            beginAtZero: true,
                            border: { display: false, dash: [4, 4] },
                            grid: { color: 'rgba(226, 232, 240, 0.7)' },
                            ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', precision: 0 }
                        }
                    }
                }
            });
        });
    </script>

</x-app-layout>
