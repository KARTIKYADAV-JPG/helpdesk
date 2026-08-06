<x-guest-layout>

    {{-- Page title --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome back</h2>
        <p class="mt-1 text-sm text-slate-500">Sign in to your HelpDesk account to continue.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('Email address') }}
            </label>
            <x-text-input
                id="email"
                class="block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="you@company.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-700">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
                       href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <x-text-input
                id="password"
                class="block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox"
                   class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   name="remember">
            <label for="remember_me" class="ml-2 text-sm text-slate-600">
                {{ __('Keep me signed in') }}
            </label>
        </div>

        <!-- Submit -->
        <div class="pt-1">
            <x-primary-button class="w-full py-3 text-sm font-semibold">
                {{ __('Sign in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="text-center text-sm text-slate-500">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}"
                   class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                    {{ __('Create one') }}
                </a>
            </p>
        @endif
    </form>

</x-guest-layout>
