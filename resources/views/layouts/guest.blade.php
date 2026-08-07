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

        <div class="min-h-screen flex">

            {{-- ── Left branding panel ────────────────────────────────── --}}
            <div class="hidden lg:flex lg:w-[45%] sidebar-gradient flex-col justify-between p-12 relative overflow-hidden">

                {{-- Decorative circles --}}
                <div class="absolute -top-20 -right-20 h-80 w-80 rounded-full bg-white/5"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/5"></div>
                <div class="absolute top-1/2 right-8 h-40 w-40 rounded-full bg-white/5"></div>

                {{-- Logo --}}
                <div class="relative flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 ring-1 ring-white/30 shadow-inner">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-black tracking-tight text-white leading-none">
                            Help<span class="opacity-75">Desk</span>
                        </p>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/50 mt-0.5">
                            Support Platform
                        </p>
                    </div>
                </div>

                {{-- Hero text --}}
                <div class="relative">
                    <h1 class="text-4xl font-black text-white leading-[1.1] mb-5">
                        Support that<br>actually works.
                    </h1>
                    <p class="text-indigo-200 text-base leading-relaxed max-w-sm">
                        Manage tickets, track resolution times, and let Google Gemini AI handle the routine — all from one beautiful dashboard.
                    </p>

                    {{-- Stats row --}}
                    <div class="mt-10 grid grid-cols-3 gap-6">
                        <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm ring-1 ring-white/20">
                            <div class="text-2xl font-black text-white">Gemini</div>
                            <div class="text-indigo-200 text-xs font-medium mt-1">AI Powered</div>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm ring-1 ring-white/20">
                            <div class="text-2xl font-black text-white">24/7</div>
                            <div class="text-indigo-200 text-xs font-medium mt-1">Coverage</div>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm ring-1 ring-white/20">
                            <div class="text-2xl font-black text-white">Auto</div>
                            <div class="text-indigo-200 text-xs font-medium mt-1">Resolution</div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <p class="relative text-indigo-300/70 text-xs font-medium">
                    © {{ date('Y') }} HelpDesk Platform. All rights reserved.
                </p>
            </div>

            {{-- ── Right form panel ───────────────────────────────────── --}}
            <div class="flex flex-1 flex-col justify-center items-center bg-white dark:bg-slate-900 px-6 py-12 sm:px-10 lg:px-16 transition-colors duration-200">

                {{-- Mobile logo (only shown on small screens) --}}
                <div class="lg:hidden flex items-center gap-3 mb-10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 shadow">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-black text-slate-800 dark:text-slate-100">Help<span class="text-indigo-600">Desk</span></p>
                    </div>
                </div>

                <div class="w-full max-w-[400px]">
                    {{ $slot }}
                </div>
            </div>

        </div>

    </body>
</html>
