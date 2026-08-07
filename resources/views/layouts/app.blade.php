<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HelpDesk') }}</title>

        <!-- Theme Initialization Script (Prevents FOUC) -->
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Inter Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200">

        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, isDarkMode: document.documentElement.classList.contains('dark') }">

            {{-- Mobile sidebar backdrop --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            @include('layouts.navigation')

            {{-- Main area --}}
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

                {{-- Top header bar --}}
                <header class="bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 flex-shrink-0 z-10 transition-colors duration-200">
                    <div class="flex items-center h-16 px-6 gap-4 justify-between">

                        <div class="flex items-center gap-4 flex-1">
                            {{-- Mobile hamburger --}}
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 -ml-1 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                                aria-label="Open sidebar"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            {{-- Page header slot --}}
                            @isset($header)
                                <div class="flex-1">
                                    {{ $header }}
                                </div>
                            @endisset
                        </div>

                        {{-- Header Controls (Dark Mode Switcher & Actions) --}}
                        <div class="flex items-center gap-3">
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
                                class="relative inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm group"
                                title="Toggle Dark / Light Mode"
                            >
                                <!-- Sun Icon (shown in dark mode) -->
                                <svg x-show="isDarkMode" class="w-5 h-5 text-amber-400 transform transition-transform group-hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <!-- Moon Icon (shown in light mode) -->
                                <svg x-show="!isDarkMode" class="w-5 h-5 text-indigo-600 transform transition-transform group-hover:-rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </header>

                {{-- Page content --}}
                <main class="flex-1 overflow-y-auto scrollbar-thin">
                    {{ $slot }}
                </main>

            </div>
        </div>

    </body>
</html>
