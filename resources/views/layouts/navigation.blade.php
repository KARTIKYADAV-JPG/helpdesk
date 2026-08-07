{{--
    Sidebar Navigation — Concept C "Sidebar Pro"
    Indigo-to-violet gradient, white icon nav items, active pill state
--}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:relative inset-y-0 left-0 z-30 flex w-64 h-full flex-shrink-0 flex-col sidebar-gradient shadow-xl shadow-indigo-900/30 transition-transform duration-300 ease-in-out"
>
    {{-- ── Logo ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10 flex-shrink-0">
        {{-- Icon bubble --}}
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 ring-1 ring-white/30 shadow-inner">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        {{-- Wordmark --}}
        <div>
            <p class="text-lg font-black tracking-tight text-white leading-none">
                Help<span class="font-black opacity-80">Desk</span>
            </p>
            <p class="text-[10px] font-medium text-white/50 uppercase tracking-widest mt-0.5">
                Support Platform
            </p>
        </div>
    </div>

    {{-- ── Navigation ──────────────────────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-0.5">

        {{-- Section label --}}
        <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-white/40">
            Main Menu
        </p>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('dashboard')
                     ? 'bg-white text-indigo-700 shadow-md shadow-indigo-900/20'
                     : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg
                         {{ request()->routeIs('dashboard') ? 'bg-indigo-50' : 'bg-white/10 group-hover:bg-white/20' }}
                         transition-colors">
                <svg class="h-4 w-4 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
            <span>Dashboard</span>
            @if(request()->routeIs('dashboard'))
                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
            @endif
        </a>

        {{-- Tickets --}}
        <a href="{{ route('tickets.index') }}"
           class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('tickets.*')
                     ? 'bg-white text-indigo-700 shadow-md shadow-indigo-900/20'
                     : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg
                         {{ request()->routeIs('tickets.*') ? 'bg-indigo-50' : 'bg-white/10 group-hover:bg-white/20' }}
                         transition-colors">
                <svg class="h-4 w-4 {{ request()->routeIs('tickets.*') ? 'text-indigo-600' : 'text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </span>
            <span>Tickets</span>
            @if(request()->routeIs('tickets.*'))
                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
            @endif
        </a>

        @if(Auth::user()->isAdmin())
        {{-- Users (Admin only) --}}
        <a href="{{ route('users.index') }}"
           class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('users.*')
                     ? 'bg-white text-indigo-700 shadow-md shadow-indigo-900/20'
                     : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg
                         {{ request()->routeIs('users.*') ? 'bg-indigo-50' : 'bg-white/10 group-hover:bg-white/20' }}
                         transition-colors">
                <svg class="h-4 w-4 {{ request()->routeIs('users.*') ? 'text-indigo-600' : 'text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
            <span>Users</span>
            @if(request()->routeIs('users.*'))
                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
            @endif
        </a>
        @endif

    </nav>

    {{-- ── User section ─────────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-white/10 p-4 space-y-3">
        {{-- User info --}}
        <div class="flex items-center gap-3">
            {{-- Avatar --}}
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 ring-1 ring-white/30">
                <span class="text-sm font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white leading-tight">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-white/50 leading-tight">{{ Auth::user()->email }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1.5">
            <button
                type="button"
                @click="
                    isDarkMode = !isDarkMode;
                    if (isDarkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                "
                class="flex items-center justify-center p-1.5 rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition-colors"
                title="Toggle Dark/Light Theme"
            >
                <svg x-show="isDarkMode" class="h-4 w-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="!isDarkMode" class="h-4 w-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <a href="{{ route('profile.edit') }}"
               class="flex flex-1 items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-white/70 hover:bg-white/10 hover:text-white transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-white/70 hover:bg-white/10 hover:text-white transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>

</aside>
