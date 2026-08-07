<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ __('Tickets') }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Browse, filter and manage support tickets') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-5 px-6 max-w-7xl mx-auto w-full">
        <div class="space-y-5">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="flex items-center p-4 text-emerald-800 dark:text-emerald-300 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 shadow-sm" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                    </svg>
                    <span class="sr-only">Success</span>
                    <div class="ms-3 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center p-4 text-rose-800 dark:text-rose-300 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                    </svg>
                    <span class="sr-only">Error</span>
                    <div class="ms-3 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('tickets.index') }}" class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-4 transition-colors">
                <!-- Search and Filter Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3.5">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Search') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="block w-full ps-9 pr-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500" placeholder="Search ticket number or subject...">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Status') }}</label>
                        <select id="status" name="status" class="block w-full py-1.5 px-3 border border-slate-300 dark:border-slate-700 rounded-xl text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('All Statuses') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Category') }}</label>
                        <select id="category" name="category" class="block w-full py-1.5 px-3 border border-slate-300 dark:border-slate-700 rounded-xl text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Priority') }}</label>
                        <select id="priority" name="priority" class="block w-full py-1.5 px-3 border border-slate-300 dark:border-slate-700 rounded-xl text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('All Priorities') }}</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" {{ ($filters['priority'] ?? '') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Second Line: Agent and Buttons -->
                <div class="flex flex-col md:flex-row items-stretch md:items-end justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <!-- Assigned Agent -->
                    <div class="w-full md:max-w-xs">
                        <label for="assigned_to" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Assigned Agent') }}</label>
                        <select id="assigned_to" name="assigned_to" class="block w-full py-1.5 px-3 border border-slate-300 dark:border-slate-700 rounded-xl text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                            <option value="">{{ __('All Agents') }}</option>
                            <option value="unassigned" {{ ($filters['assigned_to'] ?? '') === 'unassigned' ? 'selected' : '' }}>{{ __('Unassigned') }}</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" {{ ($filters['assigned_to'] ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit and Reset Buttons -->
                    <div class="flex items-center space-x-2.5">
                        @if(isset($sortField))
                            <input type="hidden" name="sort" value="{{ $sortField }}">
                        @endif
                        @if(isset($sortDirection))
                            <input type="hidden" name="direction" value="{{ $sortDirection }}">
                        @endif

                        <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-3.5 py-1.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition-colors duration-150">
                            {{ __('Reset Filters') }}
                        </a>
                        
                        <button type="submit" class="inline-flex items-center px-4 py-1.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                            <svg class="w-3.5 h-3.5 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            {{ __('Apply Filters') }}
                        </button>
                    </div>
                </div>

                <!-- Active Filters Display -->
                @php
                    $activeFilters = array_filter($filters, fn($val) => !empty($val));
                @endphp
                @if (!empty($activeFilters))
                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400">
                        <span class="font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1">{{ __('Active Filters:') }}</span>
                        @foreach ($activeFilters as $key => $value)
                            @php
                                $displayValue = $value;
                                if ($key === 'assigned_to') {
                                    if ($value === 'unassigned') {
                                        $displayValue = __('Unassigned');
                                    } else {
                                        $agentObj = $agents->firstWhere('id', $value);
                                        $displayValue = $agentObj ? $agentObj->name : $value;
                                    }
                                }
                                $removeUrlParams = array_merge($filters, [$key => null], ['sort' => $sortField, 'direction' => $sortDirection]);
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-700/10">
                                <span class="capitalize">{{ $key === 'assigned_to' ? 'Agent' : $key }}:</span>
                                <span class="ms-1 font-medium text-indigo-900 dark:text-indigo-200">{{ $displayValue }}</span>
                                <a href="{{ route('tickets.index', $removeUrlParams) }}" class="ms-1.5 inline-flex items-center justify-center w-3 h-3 text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-200 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-full transition-colors">
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>

            <!-- Ticket List Card -->
            <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-sm rounded-2xl border border-slate-200/80 dark:border-slate-800 transition-colors">
                <div class="p-0">
                    @if ($tickets->isEmpty())
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-16 text-center p-6">
                            <div class="p-4 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 dark:text-slate-500 mb-4">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">{{ __('No Tickets Found') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm">
                                {{ __('There are currently no tickets in the database. New support requests will appear here once submitted.') }}
                            </p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="w-full">
                            <table class="w-full text-left border-collapse table-auto">
                                <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <tr>
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            {{ __('Ticket Number') }}
                                        </th>
                                        <!-- Subject -->
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'subject', 'direction' => ($sortField === 'subject' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                                                <span>{{ __('Subject') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'subject')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <!-- Status -->
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'status', 'direction' => ($sortField === 'status' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                                                <span>{{ __('Status') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'status')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <!-- Priority -->
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'priority', 'direction' => ($sortField === 'priority' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                                                <span>{{ __('Priority') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'priority')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            {{ __('Created By') }}
                                        </th>
                                        <th scope="col" class="px-3.5 py-3 text-left">
                                            {{ __('Assigned To') }}
                                        </th>
                                        <!-- Created At -->
                                        <th scope="col" class="px-3.5 py-3 text-left whitespace-nowrap">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'created_at', 'direction' => ($sortField === 'created_at' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                                                <span>{{ __('Created At') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'created_at')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    @foreach ($tickets as $ticket)
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors duration-150">
                                            <!-- Ticket Number -->
                                            <td class="px-3.5 py-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                                {{ $ticket->ticket_number }}
                                            </td>

                                            <!-- Subject -->
                                            <td class="px-3.5 py-3 text-xs">
                                                <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors block max-w-xs truncate" title="{{ $ticket->subject }}">
                                                    {{ $ticket->subject }}
                                                </a>
                                                <span class="mt-0.5 inline-flex items-center px-1.5 py-0.2 text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded border border-slate-200/60 dark:border-slate-700">
                                                    {{ $ticket->category->value }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="px-3.5 py-3 text-xs whitespace-nowrap">
                                                @php
                                                    $statusVal = $ticket->status instanceof \App\Enums\TicketStatus ? $ticket->status->value : $ticket->status;
                                                    $statusLabel = \App\Enums\TicketStatus::labels()[$statusVal] ?? ucfirst($statusVal);
                                                @endphp
                                                @if ($statusVal === \App\Enums\TicketStatus::NEW->value)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::PROCESSING->value)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-50 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300 ring-1 ring-inset ring-cyan-600/15">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::OPEN->value)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::IN_PROGRESS->value)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 ring-1 ring-inset ring-amber-600/15">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::RESOLVED->value)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-500/10">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Priority -->
                                            <td class="px-3.5 py-3 text-xs whitespace-nowrap">
                                                @if ($ticket->priority === \App\Enums\TicketPriority::URGENT)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-950/80 text-red-800 dark:text-red-300 ring-1 ring-inset ring-red-600/20">
                                                        {{ __('Urgent') }}
                                                    </span>
                                                @elseif ($ticket->priority === \App\Enums\TicketPriority::HIGH)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 ring-1 ring-inset ring-rose-600/10">
                                                        {{ __('High') }}
                                                    </span>
                                                @elseif ($ticket->priority === \App\Enums\TicketPriority::MEDIUM)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 ring-1 ring-inset ring-amber-600/10">
                                                        {{ __('Medium') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-600/10">
                                                        {{ __('Low') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Created By -->
                                            <td class="px-3.5 py-3 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                        {{ strtoupper(substr($ticket->creator->name ?? '?', 0, 2)) }}
                                                    </div>
                                                    <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[120px]">
                                                        {{ $ticket->creator->name ?? __('Unknown') }}
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Assigned To -->
                                            <td class="px-3.5 py-3 text-xs">
                                                @if ($ticket->assignedAgent)
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-[10px] font-bold text-indigo-650 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                                                            {{ strtoupper(substr($ticket->assignedAgent->name, 0, 2)) }}
                                                        </div>
                                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[120px]">
                                                            {{ $ticket->assignedAgent->name }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 dark:text-slate-500 italic">
                                                        {{ __('Unassigned') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Created At -->
                                            <td class="px-3.5 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                                {{ $ticket->created_at->format('M d, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Footer -->
                        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
