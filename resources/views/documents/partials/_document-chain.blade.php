@php
    $chain = $document->connectedDocuments();
@endphp

@if($chain->count() > 1)
    <section class="{{ !empty($sidebar) ? '' : 'my-6' }} {{ !empty($fitHeight) ? 'lg:absolute lg:inset-0 lg:flex lg:min-h-0 lg:flex-col' : '' }} min-w-0 rounded-2xl border border-slate-200 bg-slate-50 p-4" aria-label="{{ __('documents.related_documents') }}">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('documents.related_documents') }}</h2>
        <p class="mt-1 text-xs font-semibold text-slate-700">Stan zestawu: {{ __('documents.relation_states.' . $document->relationState()) }}</p>
        <p class="mt-1 text-xs text-slate-500">Cały zestaw w kolejności dodania — od najstarszego dokumentu.</p>
        @if(!empty($sidebar))
            <div class="mt-3 shrink-0" data-chain-control hidden>
                <button type="button" data-chain-up aria-label="Przewiń dokumenty w górę" aria-controls="document-chain-{{ $document->id }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-emerald-600 disabled:cursor-default disabled:opacity-40">
                    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                </button>
            </div>
        @endif
        <ol @if(!empty($sidebar)) id="document-chain-{{ $document->id }}" data-document-chain-scroll tabindex="0" aria-label="Lista powiązanych dokumentów" @endif class="mt-4 flex flex-col gap-3 p-1 {{ !empty($sidebar) ? 'max-h-96 overflow-y-auto overscroll-contain' : 'md:flex-row md:items-stretch md:overflow-x-auto md:pb-3' }} {{ !empty($fitHeight) ? 'lg:min-h-0 lg:max-h-none lg:flex-1' : '' }}">
            @foreach($chain as $member)
                @php
                    $current = $member->id === $document->id;
                    [$background, $border] = match ($member->type) {
                        \App\Models\Document::TYPE_CHANGE => ['bg-amber-50/70', 'border-amber-200'],
                        \App\Models\Document::TYPE_REPEAL => ['bg-rose-50/70', 'border-rose-200'],
                        default => ['bg-white', 'border-slate-200'],
                    };
                    $link = !empty($publicView)
                        ? route('documents.preview_public', $member)
                        : (!empty($editLinks)
                            ? route('documents.edit', ['document' => $member, 'return_url' => $returnUrl ?? route('documents.index')])
                            : route('documents.preview', $member));
                @endphp
                <li class="flex shrink-0 flex-col gap-3 {{ !empty($sidebar) ? '' : 'md:flex-row md:items-center' }}">
                    <a href="{{ $link }}" @if($current) aria-current="page" @endif class="block rounded-xl border {{ $border }} {{ $background }} p-4 transition hover:shadow-md focus-visible:outline-2 focus-visible:outline-emerald-600 {{ !empty($sidebar) ? '' : 'md:w-60' }} {{ $current ? 'ring-2 ring-emerald-600' : '' }}">
                        <span class="text-xs font-semibold text-slate-600">{{ __('documents.types.' . $member->type) }}</span>
                        @if($current)
                            <span class="ml-1 text-xs font-semibold text-emerald-700">Otwarty dokument</span>
                        @endif
                        <p class="mt-2 break-words text-sm font-semibold text-slate-900">{{ $member->title }}</p>
                        <p class="mt-1 break-words text-xs text-slate-600">{{ $member->document_number }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $member->created_at?->format('d.m.Y H:i') }}</p>
                    </a>
                    @unless($loop->last)
                        <span aria-hidden="true" class="self-center text-xl text-slate-400">@if(!empty($sidebar))↓@else<span class="md:hidden">↓</span><span class="hidden md:inline">→</span>@endif</span>
                    @endunless
                </li>
            @endforeach
        </ol>
        @if(!empty($sidebar))
            <div class="mt-3 shrink-0" data-chain-control hidden>
                <button type="button" data-chain-down aria-label="Przewiń dokumenty w dół" aria-controls="document-chain-{{ $document->id }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 text-emerald-700 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-emerald-600 disabled:cursor-default disabled:opacity-40">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
            </div>
        @endif
    </section>

    @if(!empty($sidebar))
        @once
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('[data-document-chain-scroll]').forEach((list) => {
                        const section = list.closest('section');
                        const up = section.querySelector('[data-chain-up]');
                        const down = section.querySelector('[data-chain-down]');
                        const controls = section.querySelectorAll('[data-chain-control]');
                        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

                        const updateControls = () => {
                            const limit = list.scrollHeight - list.clientHeight;
                            controls.forEach((control) => { control.hidden = limit <= 1; });
                            up.disabled = list.scrollTop <= 1;
                            down.disabled = list.scrollTop >= limit - 1;
                        };

                        const centerCurrent = () => {
                            const current = list.querySelector('[aria-current="page"]');
                            if (current) {
                                // Scroll only the list; keep the page and form in place.
                                list.scrollTop += current.getBoundingClientRect().top
                                    - list.getBoundingClientRect().top
                                    - (list.clientHeight - current.offsetHeight) / 2;
                            }
                            updateControls();
                        };

                        const move = (direction) => list.scrollBy({
                            top: direction * Math.max(120, list.clientHeight * 0.7),
                            behavior: reducedMotion.matches ? 'auto' : 'smooth',
                        });

                        up.addEventListener('click', () => move(-1));
                        down.addEventListener('click', () => move(1));
                        list.addEventListener('scroll', updateControls, { passive: true });
                        new ResizeObserver(updateControls).observe(list);
                        requestAnimationFrame(centerCurrent);
                        document.fonts.ready.then(centerCurrent);
                    });
                });
            </script>
        @endonce
    @endif
@endif
