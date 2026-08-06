<x-guest-layout>

    {{-- Page title --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Create your account</h2>
        <p class="mt-1 text-sm text-slate-500">Get started with HelpDesk today — it's free.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('Full name') }}
            </label>
            <x-text-input
                id="name"
                class="block w-full"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="John Doe"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

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
                autocomplete="username"
                placeholder="you@company.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('Password') }}
            </label>
            <x-text-input
                id="password"
                class="block w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Minimum 8 characters"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                {{ __('Confirm password') }}
            </label>
            <x-text-input
                id="password_confirmation"
                class="block w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Re-enter your password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit -->
        <div class="pt-1">
            <x-primary-button class="w-full py-3 text-sm font-semibold">
                {{ __('Create account') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-slate-500">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}"
               class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                {{ __('Sign in') }}
            </a>
        </p>
    </form>

</x-guest-layout>
