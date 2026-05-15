@extends('layouts.app')

@section('title', 'Zaloguj się')

@section('content')
    <div class="mx-auto max-w-md rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Zaloguj się</h1>
            <p class="mt-2 text-sm text-slate-500">Wprowadź dane konta, aby kontynuować.</p>
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

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Hasło</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
            </div>

            <div class="flex items-center justify-between text-sm text-slate-600">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    Zapamiętaj mnie
                </label>
            </div>

            <button type="submit" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                Zaloguj
            </button>
        </form>
    </div>
@endsection
