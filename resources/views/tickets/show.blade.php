<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center p-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-600/20 uppercase tracking-wider">
                            {{ $ticket->ticket_number }}
                        </span>
                        <span id="category-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            🤖 {{ $categoryLabels[$ticket->category->value] ?? $ticket->category->value }}
                        </span>
                    </div>
                    <h1 class="mt-1 text-base font-bold text-slate-800 dark:text-slate-100 leading-snug">
                        {{ $ticket->subject }}
                    </h1>
                </div>
            </div>

            <!-- Header Action: Gemini AI Auto-Resolution -->
            <div class="flex items-center gap-2">
                <button type="button" id="auto-resolve-btn" onclick="autoResolveTicket()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    <svg id="auto-resolve-btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span id="auto-resolve-btn-text">{{ __('🤖 Run Gemini Auto-Resolution') }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 px-6 overflow-x-hidden">
        <div class="space-y-4">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="flex items-center p-4 mb-4 text-emerald-800 dark:text-emerald-300 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 shadow-sm" role="alert">
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
                <div class="flex items-center p-4 mb-4 text-rose-800 dark:text-rose-300 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                    </svg>
                    <span class="sr-only">Error</span>
                    <div class="ms-3 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Gemini Auto-Resolution Status Banner (shown if ticket is resolved by AI) -->
            @if ($ticket->status->value === \App\Enums\TicketStatus::RESOLVED->value)
                <div id="ai-resolved-banner" class="flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-indigo-500/10 border border-teal-500/30 text-teal-900 dark:text-teal-200 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white font-bold text-sm shadow">
                            🤖
                        </div>
                        <div>
                            <h4 class="text-sm font-bold flex items-center gap-2">
                                {{ __('Google Gemini AI Auto-Resolved') }}
                                <span class="px-2 py-0.5 text-[10px] uppercase font-extrabold bg-teal-600 text-white rounded-full">Active</span>
                            </h4>
                            <p class="text-xs opacity-90">
                                {{ __('This ticket was automatically categorized, matched against the Knowledge Base, and resolved by Gemini AI.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <!-- Left Column (2/3 width) -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Ticket Main Content Card -->
                    <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-sm rounded-2xl border border-slate-200/80 dark:border-slate-800 transition-colors">
                        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('Ticket Description') }}
                            </h3>
                            <span class="text-xs text-slate-400 dark:text-slate-500">
                                {{ __('Created') }} {{ $ticket->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="px-5 py-4 text-slate-700 dark:text-slate-300 leading-relaxed text-sm break-words">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>

                    <!-- AI Action Bar -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" id="summarize-btn" onclick="summarizeTicket()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 focus:outline-none focus:ring-2 focus:ring-indigo-400 shadow-sm transition-colors">
                            <svg id="summarize-btn-icon" class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span id="summarize-btn-text">{{ __('Summarize with Gemini AI') }}</span>
                        </button>

                        <button type="button" id="classify-btn" onclick="classifyTicket()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/60 focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-sm transition-colors">
                            <svg id="classify-btn-icon" class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h7M13 11h7M13 15h7"/>
                            </svg>
                            <span id="classify-btn-text">{{ __('Classify with Gemini') }}</span>
                        </button>
                    </div>

                    <!-- AI Generated Summary Container Box -->
                    <div id="summary-card-container" class="{{ !empty($ticket->summary) ? '' : 'hidden' }} bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-blue-500/10 overflow-hidden shadow-sm sm:rounded-2xl border border-indigo-500/20 p-5 space-y-3">
                        <div class="flex items-center justify-between border-b border-indigo-500/20 pb-3">
                            <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('AI Generated Ticket Summary') }}
                            </h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/80 px-2 py-0.5 rounded-full">
                                {{ __('Google Gemini AI') }}
                            </span>
                        </div>
                        <div id="summary-content" class="text-sm text-indigo-950 dark:text-indigo-100 leading-relaxed font-medium whitespace-pre-line">
                            {{ $ticket->summary ?? '' }}
                        </div>
                    </div>

                    <!-- AI Error Notification Container -->
                    <div id="summary-error-container" class="hidden flex items-center p-4 text-rose-800 dark:text-rose-300 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                        </svg>
                        <div id="summary-error-text" class="ms-3 text-sm font-medium">
                            {{ __('Unable to generate AI summary at this time. Please try again.') }}
                        </div>
                    </div>

                    <!-- Reply Thread Section -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            {{ __('Discussion & Replies') }}
                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300">{{ $ticket->replies->count() }}</span>
                        </h3>

                        @if ($ticket->replies->isEmpty())
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 text-center text-sm text-slate-500 dark:text-slate-400 italic">
                                {{ __('No replies posted yet. Be the first to start the conversation.') }}
                            </div>
                        @else
                            @foreach ($ticket->replies as $reply)
                                @php
                                    $isAgent = ($reply->senderType === 'agent' || $reply->sender_type === 'agent');
                                @endphp
                                <div class="rounded-2xl px-5 py-4 border shadow-sm transition-all duration-150 {{ $isAgent ? 'bg-indigo-50/40 dark:bg-indigo-950/30 border-indigo-200/70 dark:border-indigo-900/60' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800' }}">
                                    <div class="flex items-center justify-between mb-2.5 pb-2 border-b {{ $isAgent ? 'border-indigo-100 dark:border-indigo-900/40' : 'border-slate-100 dark:border-slate-800' }}">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold {{ $isAgent ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-200' }}">
                                                {{ strtoupper(substr($reply->user->name ?? '?', 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $reply->user->name ?? __('Unknown') }}</span>
                                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $isAgent ? 'bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                                                    {{ $isAgent ? __('Agent Response') : __('Customer Response') }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                                        {!! nl2br(e($reply->body)) !!}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Post Reply Form -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm px-5 py-4 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                {{ __('Post a Reply') }}
                            </h3>

                            <!-- ✨ Generate Instant AI Reply Button -->
                            <button type="button" id="generate-ai-reply-btn" onclick="generateAiReply()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-sm transition-colors">
                                <svg id="generate-ai-reply-btn-icon" class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span id="generate-ai-reply-btn-text">{{ __('✨ Auto-Generate AI Reply') }}</span>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('tickets.replies.store', $ticket) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="body" class="sr-only">{{ __('Reply Content') }}</label>
                                <textarea id="body" name="body" rows="4"
                                          class="block w-full py-2.5 px-3.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100 resize-none shadow-sm"
                                          placeholder="{{ __('Type your reply message here or click ✨ Auto-Generate AI Reply...') }}">{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                <div id="polish-error-message" class="hidden text-xs font-semibold text-rose-600 dark:text-rose-400 mr-auto"></div>

                                {{-- Polish button --}}
                                <button type="button" id="polish-btn" onclick="polishReply()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/80 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 shadow-sm transition-colors">
                                    <svg id="polish-btn-icon" class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span id="polish-btn-text">{{ __('Polish with AI') }}</span>
                                </button>

                                {{-- Submit Reply button --}}
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-500/25 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    {{ __('Submit Reply') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4 lg:sticky lg:top-4">

                    <!-- Update Ticket Form -->
                    @can('update', $ticket)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm px-4 py-4 space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                                {{ __('Update Attributes') }}
                            </h3>
                            <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Status') }}</label>
                                    <select id="status" name="status" class="block w-full py-1.5 px-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100">
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}" {{ $ticket->status->value === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Category') }}</label>
                                    <select id="category" name="category" class="block w-full py-1.5 px-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100">
                                        @foreach ($categoryLabels as $value => $label)
                                            <option value="{{ $value }}" {{ $ticket->category->value === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>

                                <!-- Assigned Agent -->
                                <div>
                                    <label for="assignedToId" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">{{ __('Assigned Agent') }}</label>
                                    <select id="assignedToId" name="assignedToId" class="block w-full py-1.5 px-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100">
                                        <option value="">{{ __('Unassigned') }}</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assignedToId')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                                </div>

                                <div class="pt-1">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-md shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                        {{ __('Save Changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endcan

                    <!-- Ticket Overview Card -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm px-4 py-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                            {{ __('Ticket Details') }}
                        </h3>

                        <!-- Current Status -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Status') }}</span>
                            <div id="status-badge-container">
                                @php
                                    $stVal = $ticket->status instanceof \App\Enums\TicketStatus ? $ticket->status->value : $ticket->status;
                                    $stLabel = $statusLabels[$stVal] ?? ucfirst($stVal);
                                @endphp
                                @if ($stVal === \App\Enums\TicketStatus::NEW->value)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @elseif ($stVal === \App\Enums\TicketStatus::PROCESSING->value)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-cyan-50 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300 ring-1 ring-inset ring-cyan-600/15">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @elseif ($stVal === \App\Enums\TicketStatus::OPEN->value)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 ring-1 ring-inset ring-emerald-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @elseif ($stVal === \App\Enums\TicketStatus::IN_PROGRESS->value)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 ring-1 ring-inset ring-amber-600/15">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @elseif ($stVal === \App\Enums\TicketStatus::RESOLVED->value)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 ring-1 ring-inset ring-slate-500/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 me-1.5"></span>
                                        {{ $stLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Current Priority -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Priority') }}</span>
                            <div>
                                @if ($ticket->priority === \App\Enums\TicketPriority::URGENT)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-950/80 text-red-800 dark:text-red-300 ring-1 ring-inset ring-red-600/20">
                                        {{ __('Urgent') }}
                                    </span>
                                @elseif ($ticket->priority === \App\Enums\TicketPriority::HIGH)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 ring-1 ring-inset ring-rose-600/10">
                                        {{ __('High') }}
                                    </span>
                                @elseif ($ticket->priority === \App\Enums\TicketPriority::MEDIUM)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 ring-1 ring-inset ring-amber-600/10">
                                        {{ __('Medium') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-600/10">
                                        {{ __('Low') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Current Category -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Category') }}</span>
                            <span id="overview-category-label" class="text-sm font-semibold text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                                {{ $categoryLabels[$ticket->category->value] ?? $ticket->category->value }}
                            </span>
                        </div>

                        <!-- Created By -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">{{ __('Created By') }}</span>
                            <div class="flex items-center gap-2.5">
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ strtoupper(substr($ticket->creator->name ?? '?', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $ticket->creator->name ?? __('Unknown') }}</div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500">{{ $ticket->creator->email ?? '' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Agent -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">{{ __('Assigned Agent') }}</span>
                            @if ($ticket->assignedAgent)
                                <div class="flex items-center gap-2.5">
                                    <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-xs font-bold text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900">
                                        {{ strtoupper(substr($ticket->assignedAgent->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $ticket->assignedAgent->name }}</div>
                                        <div class="text-xs text-indigo-500 dark:text-indigo-400 font-medium">{{ $ticket->assignedAgent->email }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center text-sm text-slate-400 dark:text-slate-500 italic bg-slate-50 dark:bg-slate-800/60 px-2.5 py-2 rounded-xl border border-slate-200/60 dark:border-slate-800">
                                    <svg class="w-4 h-4 mr-1.5 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ __('Unassigned') }}
                                </div>
                            @endif
                        </div>

                        <!-- Created Date -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">{{ __('Created Date') }}</span>
                            <div class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $ticket->created_at->format('d M Y, h:i A') }}
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        async function summarizeTicket() {
            const btn = document.getElementById('summarize-btn');
            const btnText = document.getElementById('summarize-btn-text');
            const btnIcon = document.getElementById('summarize-btn-icon');
            const summaryCard = document.getElementById('summary-card-container');
            const summaryContent = document.getElementById('summary-content');
            const errorCard = document.getElementById('summary-error-container');
            const errorText = document.getElementById('summary-error-text');

            if (!btn) return;

            btn.disabled = true;
            btnText.innerText = '{{ __("Summarizing with Gemini...") }}';
            btnIcon.classList.add('animate-spin');
            errorCard.classList.add('hidden');

            try {
                const response = await fetch("{{ route('tickets.summarize', $ticket) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    summaryContent.innerText = data.summary;
                    summaryCard.classList.remove('hidden');
                } else {
                    errorText.innerText = data.message || '{{ __("Unable to generate AI summary at this time. Please try again.") }}';
                    errorCard.classList.remove('hidden');
                    if (data.summary) {
                        summaryContent.innerText = data.summary;
                        summaryCard.classList.remove('hidden');
                    }
                }
            } catch (err) {
                errorText.innerText = '{{ __("Network error while generating AI summary.") }}';
                errorCard.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btnText.innerText = '{{ __("Summarize with Gemini AI") }}';
                btnIcon.classList.remove('animate-spin');
            }
        }

        async function polishReply() {
            const bodyArea = document.getElementById('body');
            const btn = document.getElementById('polish-btn');
            const btnText = document.getElementById('polish-btn-text');
            const btnIcon = document.getElementById('polish-btn-icon');
            const errorMsg = document.getElementById('polish-error-message');

            if (!bodyArea || !btn) return;

            const draft = bodyArea.value.trim();
            if (!draft || draft.length < 2) {
                alert('{{ __("Please enter a draft reply to polish.") }}');
                return;
            }

            btn.disabled = true;
            btnText.innerText = '{{ __("Polishing with Gemini...") }}';
            btnIcon.classList.add('animate-spin');
            if (errorMsg) errorMsg.classList.add('hidden');

            try {
                const response = await fetch("{{ route('tickets.replies.polish') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ body: draft })
                });

                const data = await response.json();

                if (response.ok && data.success && data.polished_reply) {
                    bodyArea.value = data.polished_reply;
                } else {
                    const msg = data.message || '{{ __("Unable to polish reply at this time. Please try again.") }}';
                    if (errorMsg) {
                        errorMsg.innerText = msg;
                        errorMsg.classList.remove('hidden');
                    } else {
                        alert(msg);
                    }
                }
            } catch (err) {
                const msg = '{{ __("Network error while communicating with AI service.") }}';
                if (errorMsg) {
                    errorMsg.innerText = msg;
                    errorMsg.classList.remove('hidden');
                } else {
                    alert(msg);
                }
            } finally {
                btn.disabled = false;
                btnText.innerText = '{{ __("Polish with AI") }}';
                btnIcon.classList.remove('animate-spin');
            }
        }

        async function generateAiReply() {
            const bodyArea = document.getElementById('body');
            const btn = document.getElementById('generate-ai-reply-btn');
            const btnText = document.getElementById('generate-ai-reply-btn-text');
            const btnIcon = document.getElementById('generate-ai-reply-btn-icon');
            const errorMsg = document.getElementById('polish-error-message');

            if (!bodyArea || !btn) return;

            btn.disabled = true;
            btnText.innerText = '{{ __("Generating with Gemini...") }}';
            btnIcon.classList.add('animate-spin');
            if (errorMsg) errorMsg.classList.add('hidden');

            try {
                const response = await fetch("{{ route('tickets.replies.generate', $ticket) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success && data.ai_reply) {
                    bodyArea.value = data.ai_reply;
                } else {
                    const msg = data.message || '{{ __("Unable to generate instant AI reply at this time. Please try again.") }}';
                    if (errorMsg) {
                        errorMsg.innerText = msg;
                        errorMsg.classList.remove('hidden');
                    } else {
                        alert(msg);
                    }
                }
            } catch (err) {
                const msg = '{{ __("Network error while generating AI reply.") }}';
                if (errorMsg) {
                    errorMsg.innerText = msg;
                    errorMsg.classList.remove('hidden');
                } else {
                    alert(msg);
                }
            } finally {
                btn.disabled = false;
                btnText.innerText = '{{ __("✨ Auto-Generate AI Reply") }}';
                btnIcon.classList.remove('animate-spin');
            }
        }

        async function autoResolveTicket() {
            const btn = document.getElementById('auto-resolve-btn');
            const btnText = document.getElementById('auto-resolve-btn-text');
            const btnIcon = document.getElementById('auto-resolve-btn-icon');

            if (!btn) return;

            btn.disabled = true;
            btnText.innerText = '{{ __("Running Gemini AI Auto-Resolution...") }}';
            btnIcon.classList.add('animate-spin');

            try {
                const response = await fetch("{{ route('tickets.auto-resolve', $ticket) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __("Failed to run Gemini AI Auto-Resolution.") }}');
                }
            } catch (err) {
                alert('{{ __("Network error while communicating with AI service.") }}');
            } finally {
                btn.disabled = false;
                btnText.innerText = '{{ __("🤖 Run Gemini Auto-Resolution") }}';
                btnIcon.classList.remove('animate-spin');
            }
        }

        async function classifyTicket() {
            const btn = document.getElementById('classify-btn');
            const btnText = document.getElementById('classify-btn-text');
            const btnIcon = document.getElementById('classify-btn-icon');

            if (!btn) return;

            btn.disabled = true;
            btnText.innerText = '{{ __("Classifying...") }}';
            btnIcon.classList.add('animate-spin');

            try {
                const response = await fetch("{{ route('tickets.classify', $ticket) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const badge = document.getElementById('category-badge');
                    const overviewLabel = document.getElementById('overview-category-label');
                    if (badge) badge.innerText = '🤖 ' + data.category_label;
                    if (overviewLabel) overviewLabel.innerText = data.category_label;
                    alert(data.message);
                } else {
                    alert(data.message || '{{ __("Failed to classify ticket with Gemini AI.") }}');
                }
            } catch (err) {
                alert('{{ __("Network error while running Gemini AI Classification.") }}');
            } finally {
                btn.disabled = false;
                btnText.innerText = '{{ __("Classify with Gemini") }}';
                btnIcon.classList.remove('animate-spin');
            }
        }
    </script>
</x-app-layout>
