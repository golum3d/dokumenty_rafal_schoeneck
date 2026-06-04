@extends('layouts.app')

@section('title', __('documents.module_title'))

@section('content')
    <div class="space-y-8">
        @if (session('success'))
            <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">{{ __('documents.module_title') }}</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('documents.module_title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('documents.module_description') }}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="openFolderModal({})" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">{{ __('documents.buttons.create_folder') }}</button>
                <a href="{{ route('documents.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">{{ __('documents.buttons.create') }}</a>
            </div>
        </div>

        <div id="rootDropZone" class="rounded-[2rem] bg-white shadow-xl ring-1 ring-slate-200 drop-target"
             ondragover="onDragOver(event)"
             ondragleave="onDragLeave(event)"
             ondrop="onDrop(event)"
             data-folder-id="">
            <div class="divide-y divide-slate-200">
                {{-- Render folders and documents recursively --}}
                @forelse($folders as $folder)
                    @include('documents.partials._folder-item', ['folder' => $folder, 'level' => 0])
                @empty
                @endforelse

                {{-- Documents without folder --}}
                @forelse($documents as $document)
                    @include('documents.partials._document-item', ['document' => $document, 'level' => 0])
                @empty
                    @if($folders->isEmpty())
                        <div class="px-6 py-10 text-center text-slate-600">
                            {{ __('documents.empty') }}
                        </div>
                    @endif
                @endforelse
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="folderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h2 class="text-xl font-semibold text-slate-950" id="folderModalTitle">{{ __('documents.new_folder') }}</h2>
            <form id="folderForm" class="mt-4 space-y-4">
                <input type="hidden" id="folderId" />
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.name') }}</label>
                    <input
                        type="text"
                        id="folderName"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('documents.fields.parent') }}</label>
                    <select
                        id="folderParent"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                    >
                        <option value="">{{ __('documents.no_folder') }}</option>
                        @foreach($allFolders as $parentFolder)
                            <option value="{{ $parentFolder->id }}">{{ $parentFolder->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeFolderModal()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-900 hover:bg-slate-100">
                        {{ __('documents.buttons.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-700" id="folderSubmitButton">
                        {{ __('documents.buttons.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openFolderModal(options = {}) {
            document.getElementById('folderModal').classList.remove('hidden');
            document.getElementById('folderName').value = options.name || '';
            document.getElementById('folderParent').value = options.parent_id || '';
            document.getElementById('folderId').value = options.id || '';
            document.getElementById('folderModalTitle').textContent = options.isEdit ? '{{ __('documents.edit_folder') }}' : '{{ __('documents.new_folder') }}';
            document.getElementById('folderSubmitButton').textContent = options.isEdit ? '{{ __('documents.buttons.save') }}' : '{{ __('documents.buttons.create') }}';
            document.getElementById('folderName').focus();
        }

        function closeFolderModal() {
            document.getElementById('folderModal').classList.add('hidden');
            document.getElementById('folderName').value = '';
            document.getElementById('folderId').value = '';
            document.getElementById('folderParent').value = '';
            document.getElementById('folderModalTitle').textContent = '{{ __('documents.new_folder') }}';
            document.getElementById('folderSubmitButton').textContent = '{{ __('documents.buttons.create') }}';
        }

        document.getElementById('folderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const name = document.getElementById('folderName').value;
            const parentId = document.getElementById('folderParent').value || null;
            const folderId = document.getElementById('folderId').value;

            const payload = { name, parent_id: parentId };
            const url = folderId ? `/folders/${folderId}` : '{{ route("folders.store") }}';
            const method = folderId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.error || 'Wystąpił błąd.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        function onDragStart(event) {
            const target = event.currentTarget;
            const dragType = target.dataset.dragType;
            const dragId = target.dataset.dragId;

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', `${dragType}:${dragId}`);
            target.classList.add('opacity-60');
        }

        function onDragEnd(event) {
            event.currentTarget.classList.remove('opacity-60');
        }

        function onDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            event.currentTarget.classList.add('bg-slate-100');
        }

        function onDragLeave(event) {
            event.currentTarget.classList.remove('bg-slate-100');
        }

        function onDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('bg-slate-100');

            const payload = event.dataTransfer.getData('text/plain');
            if (!payload) {
                return;
            }

            const [type, id] = payload.split(':');
            const targetFolderId = event.currentTarget.dataset.folderId || '';

            if (type === 'folder') {
                moveFolder(id, targetFolderId);
            } else if (type === 'document') {
                moveDocument(id, targetFolderId);
            }
        }

        async function moveFolder(folderId, targetFolderId) {
            const response = await fetch(`/folders/${folderId}/move`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ parent_id: targetFolderId || null }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.error || 'Wystąpił błąd.');
            }
        }

        async function moveDocument(documentId, targetFolderId) {
            const response = await fetch(`/documents/${documentId}/move`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ folder_id: targetFolderId || null }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.error || 'Wystąpił błąd.');
            }
        }

        function deleteFolder(folderId) {
            if (confirm('{{ __("documents.confirm_delete_folder") }}')) {
                fetch(`/folders/${folderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('{{ __("documents.folder_not_empty") }}');
                    }
                });
            }
        }

        function toggleFolder(folderId) {
            const content = document.getElementById(`folder-content-${folderId}`);
            const toggle = document.getElementById(`folder-toggle-${folderId}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                toggle.innerHTML = '▼';
            } else {
                content.classList.add('hidden');
                toggle.innerHTML = '▶';
            }
        }
    </script>
@endsection
