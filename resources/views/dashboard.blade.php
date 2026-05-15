@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Panel użytkownika</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">Witaj ponownie!</h1>
                    <p class="mt-2 text-sm text-slate-600">Jesteś zalogowany jako <span class="font-medium text-slate-900">{{ auth()->user()->email }}</span>.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 sm:grid-cols-2">
            <article class="rounded-[2rem] bg-white p-6 shadow-xl ring-1 ring-slate-200">
                <h2 class="text-xl font-semibold text-slate-950">Dostępne funkcje</h2>
                <p class="mt-3 text-sm text-slate-600">Aplikacja jest teraz chroniona i dostępna tylko dla zalogowanych użytkowników.</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li class="flex items-center gap-3">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                        Bezpieczne logowanie
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                        Prosty dostęp do panelu
                    </li>
                </ul>
            </article>

            <article class="rounded-[2rem] bg-slate-900 p-6 text-white shadow-xl ring-1 ring-slate-800">
                <h2 class="text-xl font-semibold">Gotowe do pracy</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">Jeżeli chcesz, mogę dodać kolejne strony, menu administracyjne lub integrację z bazą dokumentów.</p>
            </article>
        </section>
    </div>
@endsection
