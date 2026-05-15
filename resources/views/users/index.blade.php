@extends('layouts.app')

@php
    use App\Models\User;
@endphp

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
                        <article class="flex h-full flex-col justify-between rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ $user->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        @foreach($user->role_labels as $roleLabel)
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-700">{{ $roleLabel }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="whitespace-nowrap rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white">ID {{ $user->id }}</span>
                            </div>

                            <div class="mt-6 flex items-center justify-between gap-3 mt-auto">
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

                        <div class="relative" x-data="">
                            <label for="roles" class="block text-sm font-medium text-slate-700">Role</label>

                            @php
                                $selectedRoles = old('roles', $editUser?->roles ?? [User::ROLE_USER]);
                                if (!is_array($selectedRoles)) {
                                    $selectedRoles = explode(',', $selectedRoles);
                                }
                                $roleOptions = [
                                    User::ROLE_USER => 'Użytkownik',
                                    User::ROLE_DOCUMENT_STAFF => 'Pracownik merytoryczny',
                                    User::ROLE_ADMIN => 'Administrator',
                                ];
                            @endphp

                            <button
                                type="button"
                                id="rolesToggle"
                                class="mt-2 flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm text-slate-900 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <span id="selectedRolesPlaceholder" class="flex flex-wrap gap-2">
                                    @if(empty($selectedRoles))
                                        <span class="text-slate-400">Wybierz role...</span>
                                    @else
                                        @foreach($selectedRoles as $selectedRole)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-700">{{ $roleOptions[$selectedRole] ?? $selectedRole }}</span>
                                        @endforeach
                                    @endif
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-slate-500">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                id="rolesDropdown"
                                class="invisible absolute left-0 right-0 z-20 mt-2 max-h-52 overflow-auto rounded-2xl border border-slate-200 bg-white shadow-lg ring-1 ring-slate-200 transition duration-150"
                                style="opacity: 0; transform: translateY(-6px);"
                            >
                                @foreach($roleOptions as $roleValue => $roleLabel)
                                    <button
                                        type="button"
                                        data-role="{{ $roleValue }}"
                                        class="group flex w-full items-center justify-between border-b border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <span>{{ $roleLabel }}</span>
                                        <span class="h-5 w-5 rounded-full border border-slate-300 bg-white text-slate-700 transition group-hover:border-indigo-500 group-hover:bg-indigo-50" data-role-icon>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="hidden h-4 w-4" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 5.29a.75.75 0 010 1.06l-7.25 7.25a.75.75 0 01-1.06 0l-3.25-3.25a.75.75 0 111.06-1.06l2.72 2.72 6.72-6.72a.75.75 0 011.06 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <select id="rolesSelect" name="roles[]" multiple class="hidden" required>
                                @foreach($roleOptions as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}" {{ in_array($roleValue, $selectedRoles, true) ? 'selected' : '' }}>{{ $roleLabel }}</option>
                                @endforeach
                            </select>

                            <p class="mt-2 text-sm text-slate-500">Kliknij, aby wybrać lub odznaczyć rolę.</p>
                            @error('roles')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                            @error('roles.*')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const toggle = document.getElementById('rolesToggle');
                                const dropdown = document.getElementById('rolesDropdown');
                                const select = document.getElementById('rolesSelect');
                                const selectedPlaceholder = document.getElementById('selectedRolesPlaceholder');
                                const items = Array.from(dropdown.querySelectorAll('button[data-role]'));

                                const closeDropdown = () => {
                                    dropdown.style.opacity = '0';
                                    dropdown.style.transform = 'translateY(-6px)';
                                    dropdown.classList.add('invisible');
                                };

                                const openDropdown = () => {
                                    dropdown.classList.remove('invisible');
                                    dropdown.style.opacity = '1';
                                    dropdown.style.transform = 'translateY(0)';
                                };

                                const updateSelected = () => {
                                    const selected = Array.from(select.selectedOptions).map(option => option.value);
                                    selectedPlaceholder.innerHTML = '';

                                    if (selected.length === 0) {
                                        selectedPlaceholder.innerHTML = '<span class="text-slate-400">Wybierz role...</span>';
                                    } else {
                                        selected.forEach(value => {
                                            const label = select.querySelector(`option[value="${value}"]`).textContent;
                                            const badge = document.createElement('span');
                                            badge.className = 'inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-700';
                                            badge.textContent = label;
                                            selectedPlaceholder.appendChild(badge);
                                        });
                                    }

                                    items.forEach(item => {
                                        const icon = item.querySelector('[data-role-icon] svg');
                                        const isSelected = selected.includes(item.dataset.role);
                                        icon.classList.toggle('hidden', !isSelected);
                                    });
                                };

                                toggle.addEventListener('click', function (event) {
                                    event.preventDefault();
                                    if (dropdown.classList.contains('invisible')) {
                                        openDropdown();
                                    } else {
                                        closeDropdown();
                                    }
                                });

                                items.forEach(item => {
                                    item.addEventListener('click', function () {
                                        const role = this.dataset.role;
                                        const option = select.querySelector(`option[value="${role}"]`);
                                        option.selected = !option.selected;
                                        updateSelected();
                                    });
                                });

                                document.addEventListener('click', function (event) {
                                    if (!event.target.closest('#rolesToggle') && !event.target.closest('#rolesDropdown')) {
                                        closeDropdown();
                                    }
                                });

                                updateSelected();
                            });
                        </script>

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
