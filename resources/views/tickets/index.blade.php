<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800 leading-tight">{{ __('Tickets') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Browse, filter and manage support tickets') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-6">
        <div class="space-y-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="flex items-center p-4 mb-4 text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 shadow-sm" role="alert">
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
                <div class="flex items-center p-4 mb-4 text-rose-800 rounded-lg bg-rose-50 border border-rose-200 shadow-sm" role="alert">
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
            <form method="GET" action="{{ route('tickets.index') }}" class="bg-white p-6 rounded-lg shadow-sm border border-gray-150 space-y-4">
                <!-- Search and Filter Grid -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">{{ __('Search') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="block w-full ps-10 pr-4 py-2 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search ticket number or subject...">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">{{ __('Status') }}</label>
                        <select id="status" name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('All Statuses') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">{{ __('Category') }}</label>
                        <select id="category" name="category" class="block w-full py-2 px-3 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">{{ __('Priority') }}</label>
                        <select id="priority" name="priority" class="block w-full py-2 px-3 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('All Priorities') }}</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" {{ ($filters['priority'] ?? '') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Second Line: Agent and Buttons -->
                <div class="flex flex-col md:flex-row items-stretch md:items-end justify-between gap-4 pt-2 border-t border-gray-100">
                    <!-- Assigned Agent -->
                    <div class="w-full md:max-w-xs">
                        <label for="assigned_to" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">{{ __('Assigned Agent') }}</label>
                        <select id="assigned_to" name="assigned_to" class="block w-full py-2 px-3 border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('All Agents') }}</option>
                            <option value="unassigned" {{ ($filters['assigned_to'] ?? '') === 'unassigned' ? 'selected' : '' }}>{{ __('Unassigned') }}</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" {{ ($filters['assigned_to'] ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit and Reset Buttons -->
                    <div class="flex items-center space-x-3">
                        @if(isset($sortField))
                            <input type="hidden" name="sort" value="{{ $sortField }}">
                        @endif
                        @if(isset($sortDirection))
                            <input type="hidden" name="direction" value="{{ $sortDirection }}">
                        @endif

                        <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition-colors duration-150">
                            {{ __('Reset Filters') }}
                        </a>
                        
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-50 text-sm text-gray-600">
                        <span class="font-medium text-xs uppercase tracking-wider text-gray-400 mr-1">{{ __('Active Filters:') }}</span>
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                <span class="capitalize">{{ $key === 'assigned_to' ? 'Agent' : $key }}:</span>
                                <span class="ms-1 font-medium text-indigo-900">{{ $displayValue }}</span>
                                <a href="{{ route('tickets.index', $removeUrlParams) }}" class="ms-1.5 inline-flex items-center justify-center w-3 h-3 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-100 rounded-full transition-colors">
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>

            <!-- Ticket List Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">
                    @if ($tickets->isEmpty())
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="p-4 rounded-full bg-slate-50 border border-slate-100 text-slate-400 mb-4">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ __('No Tickets Found') }}</h3>
                            <p class="text-sm text-gray-500 max-w-sm">
                                {{ __('There are currently no tickets in the database. New support requests will appear here once submitted.') }}
                            </p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-55">
                                    <tr>
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Ticket Number') }}
                                        </th>
                                        <!-- Subject -->
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'subject', 'direction' => ($sortField === 'subject' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-gray-900 transition-colors">
                                                <span>{{ __('Subject') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'subject')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <!-- Status -->
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'status', 'direction' => ($sortField === 'status' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-gray-900 transition-colors">
                                                <span>{{ __('Status') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'status')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <!-- Priority -->
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'priority', 'direction' => ($sortField === 'priority' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-gray-900 transition-colors">
                                                <span>{{ __('Priority') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'priority')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Created By') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Assigned To') }}
                                        </th>
                                        <!-- Created At -->
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <a href="{{ route('tickets.index', array_merge($filters, ['sort' => 'created_at', 'direction' => ($sortField === 'created_at' && $sortDirection === 'asc') ? 'desc' : 'asc'])) }}" class="group inline-flex items-center space-x-1 hover:text-gray-900 transition-colors">
                                                <span>{{ __('Created At') }}</span>
                                                <span class="flex-shrink-0">
                                                    @if ($sortField === 'created_at')
                                                        @if ($sortDirection === 'asc')
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        @endif
                                                    @else
                                                        <svg class="w-3 h-3 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-150">
                                    @foreach ($tickets as $ticket)
                                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                                            <!-- Ticket Number -->
                                            <td class="px-4 py-2.5 whitespace-nowrap text-sm font-semibold text-indigo-650">
                                                {{ $ticket->ticket_number }}
                                            </td>

                                            <!-- Subject -->
                                            <td class="px-4 py-2.5 whitespace-nowrap text-sm text-gray-900 max-w-xs font-medium">
                                                <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-indigo-650 hover:underline transition-colors block truncate font-semibold text-gray-900">
                                                    {{ $ticket->subject }}
                                                </a>
                                                <span class="mt-0.5 inline-flex items-center px-1.5 py-0.2 text-[10px] font-medium bg-slate-100 text-slate-600 rounded">
                                                    {{ $ticket->category->value }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                @php
                                                    $statusVal = $ticket->status instanceof \App\Enums\TicketStatus ? $ticket->status->value : $ticket->status;
                                                    $statusLabel = \App\Enums\TicketStatus::labels()[$statusVal] ?? ucfirst($statusVal);
                                                @endphp
                                                @if ($statusVal === \App\Enums\TicketStatus::NEW->value)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::PROCESSING->value)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-800 ring-1 ring-inset ring-cyan-600/15">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::OPEN->value)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::IN_PROGRESS->value)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/15">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @elseif ($statusVal === \App\Enums\TicketStatus::RESOLVED->value)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-500/10">
                                                        {{ $statusLabel }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Priority -->
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                @if ($ticket->priority === \App\Enums\TicketPriority::URGENT)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 ring-1 ring-inset ring-red-650/20">
                                                        {{ __('Urgent') }}
                                                    </span>
                                                @elseif ($ticket->priority === \App\Enums\TicketPriority::HIGH)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10">
                                                        {{ __('High') }}
                                                    </span>
                                                @elseif ($ticket->priority === \App\Enums\TicketPriority::MEDIUM)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/10">
                                                        {{ __('Medium') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-600/10">
                                                        {{ __('Low') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Created By -->
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-7 w-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-semibold text-slate-600 border border-slate-200">
                                                        {{ strtoupper(substr($ticket->creator->name ?? '?', 0, 2)) }}
                                                    </div>
                                                    <div class="ms-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $ticket->creator->name ?? __('Unknown') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Assigned To -->
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                @if ($ticket->assignedAgent)
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-50 flex items-center justify-center text-xs font-semibold text-indigo-650 border border-indigo-100">
                                                            {{ strtoupper(substr($ticket->assignedAgent->name, 0, 2)) }}
                                                        </div>
                                                        <div class="ms-3">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $ticket->assignedAgent->name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400 italic">
                                                        {{ __('Unassigned') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Created At -->
                                            <td class="px-4 py-2.5 whitespace-nowrap text-sm text-gray-500">
                                                {{ $ticket->created_at->format('M d, Y h:i A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Footer -->
                        <div class="mt-0 px-4 py-3 border-t border-slate-100 bg-slate-50/30">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
