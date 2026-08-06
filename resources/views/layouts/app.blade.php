<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HelpDesk') }}</title>

        <!-- Inter Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50">

        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

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
                <header class="bg-white border-b border-slate-200/80 flex-shrink-0 z-10">
                    <div class="flex items-center h-16 px-6 gap-4">

                        {{-- Mobile hamburger --}}
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 -ml-1 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
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
                </header>

                {{-- Page content --}}
                <main class="flex-1 overflow-y-auto scrollbar-thin">
                    {{ $slot }}
                </main>

            </div>
        </div>

    </body>
</html>
