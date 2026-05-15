@extends('layouts.app')

@section('title', 'Użytkownicy')

@section('content')
    <div class="space-y-8">
        @if (session('success'))
            <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-8 xl:grid-cols-[1.3fr_0.9fr]">
            <section class="space-y-6">
                <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.32em] text-slate-500">Lista użytkowników</p>
                            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Zarządzanie użytkownikami</h1>
                        </div>
                        <div class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Łącznie: {{ $users->count() }}</div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @forelse($users as $user)
                        <article class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ $user->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                                </div>
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">ID {{ $user->id }}</span>
                            </div>

                            <div class="mt-6 flex items-center justify-between gap-3">
                                <p class="text-sm text-slate-600">Utworzono: {{ $user->created_at?->format('Y-m-d') ?? 'brak' }}</p>
                                <a href="{{ route('users.edit', $user) }}" class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:border-slate-400 hover:bg-slate-100">Edytuj</a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-8 text-center text-slate-600">
                            Brak użytkowników do wyświetlenia.
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-[2rem] bg-slate-900 p-8 text-white shadow-xl ring-1 ring-slate-800">
                    <h2 class="text-2xl font-semibold">Szybkie dodawanie</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">Utwórz nowy użytkownik lub edytuj istniejący wpis bezpośrednio z karty.</p>
                </div>

                <div class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200">
                    <div class="space-y-3">
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ $editUser ? 'Edytuj użytkownika' : 'Dodaj użytkownika' }}</p>
                        <h2 class="text-2xl font-semibold text-slate-950">{{ $editUser ? $editUser->name : 'Nowy użytkownik' }}</h2>
                    </div>

                    <form
                        method="POST"
                        action="{{ $editUser ? route('users.update', $editUser) : route('users.store') }}"
                        class="mt-8 space-y-5"
                    >
                        @csrf
                        @if($editUser)
                            @method('PUT')
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nazwa</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $editUser?->name) }}"
                                required
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />
                            @error('name')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Email</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $editUser?->email) }}"
                                required
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />
                            @error('email')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hasło</label>
                            <input
                                type="password"
                                name="password"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                {{ $editUser ? '' : 'required' }}
                            />
                            <p class="mt-2 text-sm text-slate-500">{{ $editUser ? 'Pozostaw puste, jeśli nie chcesz zmieniać hasła.' : 'Minimum 8 znaków.' }}</p>
                            @error('password')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <button type="submit" class="inline-flex justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                                {{ $editUser ? 'Zapisz zmiany' : 'Dodaj użytkownika' }}
                            </button>

                            @if($editUser)
                                <a href="{{ route('dashboard') }}" class="inline-flex justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                                    Anuluj edycję
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </aside>
        </div>
    </div>
@endsection
