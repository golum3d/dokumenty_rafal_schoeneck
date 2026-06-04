@extends('layouts.app')

@section('title', __('auth.login_title'))

@section('content')
    <div class="mx-auto max-w-md rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">{{ __('auth.login_title') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('auth.login_subtitle') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate id="loginForm">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('auth.email') }}</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <div class="mt-2 hidden text-sm text-rose-600" id="emailError"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('auth.password') }}</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
                <div class="mt-2 hidden text-sm text-rose-600" id="passwordError"></div>
            </div>

            <div class="flex items-center justify-between text-sm text-slate-600">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    {{ __('auth.remember_me') }}
                </label>
            </div>

            <button type="submit" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                {{ __('auth.login_button') }}
            </button>
        </form>
    </div>

    <script>
        const validationMessages = {
            email: "{{ __('validation.email') }}",
            email_format: "{{ __('validation.email_format') }}",
            email_required: "{{ __('validation.email_required') }}",
            password_required: "{{ __('validation.password_required') }}"
        };

        const form = document.getElementById('loginForm');
        const emailInput = form.querySelector('input[name="email"]');
        const passwordInput = form.querySelector('input[name="password"]');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        function validateEmail() {
            emailError.classList.add('hidden');
            
            if (!emailInput.value) {
                emailError.textContent = validationMessages.email_required;
                emailError.classList.remove('hidden');
                return false;
            }
            
            if (!emailInput.value.includes('@')) {
                emailError.textContent = validationMessages.email;
                emailError.classList.remove('hidden');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                emailError.textContent = validationMessages.email_format;
                emailError.classList.remove('hidden');
                return false;
            }
            
            return true;
        }

        function validatePassword() {
            passwordError.classList.add('hidden');
            
            if (!passwordInput.value) {
                passwordError.textContent = validationMessages.password_required;
                passwordError.classList.remove('hidden');
                return false;
            }
            
            return true;
        }

        emailInput.addEventListener('blur', validateEmail);
        emailInput.addEventListener('change', validateEmail);
        passwordInput.addEventListener('blur', validatePassword);

        form.addEventListener('submit', (e) => {
            const isEmailValid = validateEmail();
            const isPasswordValid = validatePassword();
            
            if (!isEmailValid || !isPasswordValid) {
                e.preventDefault();
            }
        });
    </script>
@endsection
