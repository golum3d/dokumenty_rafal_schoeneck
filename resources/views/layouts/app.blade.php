<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white border-b border-slate-200">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-slate-900">
                    {{ config('app.name', 'Laravel') }}
                </a>

                @auth
                    <nav class="hidden items-center gap-4 sm:flex">
                        <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Dashboard</a>
                        @if(auth()->user()->can('manage-documents'))
                            <a href="{{ route('documents.index') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('documents.*') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Dokumenty</a>
                        @elseif(auth()->user()->hasRole(\App\Models\User::ROLE_USER))
                            <a href="{{ route('documents.user_index') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('documents.user*') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Dokumenty</a>
                        @endif
                        @can('manage-users')
                            <a href="{{ route('users.index') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('users.*') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Użytkownicy</a>
                            <a href="{{ route('documents.categories.index') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('documents.categories.*') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Kategorie</a>
                            <a href="{{ route('documents.statuses.index') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('documents.statuses.*') ? 'bg-indigo-100 text-indigo-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' }}">Statusy</a>
                        @endcan
                    </nav>

                    <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-3">
                        @csrf
                        <button type="submit" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Wyloguj
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pl.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validFromElement = document.querySelector('input[name="valid_from"]');
            const validToElement = document.querySelector('input[name="valid_to"]');
            
            const flatpickrOptions = {
                locale: 'pl',
                dateFormat: 'Y-m-d',
                firstDayOfWeek: 1, // 1 = poniedziałek
                allowInput: true
            };
            
            if (validFromElement) {
                flatpickr(validFromElement, flatpickrOptions);
            }
            
            if (validToElement) {
                flatpickr(validToElement, flatpickrOptions);
            }
        });
    </script>
</body>
</html>
