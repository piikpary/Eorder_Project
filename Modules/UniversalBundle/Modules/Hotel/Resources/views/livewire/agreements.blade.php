<div>

    {{-- ══════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════ --}}
    <div class="px-5 pt-5 pb-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl tracking-tight">
                {{ __('hotel::modules.agreement.agreements') }}
            </h1>
            @if(user_can('Create Hotel Reservation'))
            <button type="button" wire:click="$set('showAddModal', true)"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-lg bg-skin-base text-white hover:opacity-90 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('hotel::modules.agreement.generate') }}
            </button>
            @endif
        </div>

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1 min-w-0 sm:max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                    </svg>
                </div>
                <x-input type="text" wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('hotel::modules.agreement.searchPlaceholder') }}"
                    class="block w-full pl-9" />
            </div>

            <x-select wire:model.live="filterType" class="block w-full sm:w-44">
                <option value="">{{ __('hotel::modules.agreement.allTypes') }}</option>
                @foreach($agreementTypes as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         TABLE
    ══════════════════════════════════════════ --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.agreementNumber') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.reservation') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.tenant') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.type') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.date') }}</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($agreements as $agreement)
                @php
                    $typeCfg = [
                        'sale'  => 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:ring-purple-700',
                        'lease' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-700',
                        'rent'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-700',
                    ];
                    $badge = $typeCfg[$agreement->type->value] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $agreement->agreement_number }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $agreement->reservation?->reservation_number ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $agreement->reservation?->primaryGuest?->full_name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $badge }}">
                            {{ $agreement->type->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $agreement->agreement_date->format('d M Y') }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            <a href="{{ route('hotel.agreements.print', $agreement) }}"
                                target="_blank"
                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                {{ __('hotel::modules.agreement.print') }}
                            </a>

                            @if(user_can('Delete Hotel Reservation'))
                            <button type="button" wire:click="showDeleteAgreement({{ $agreement->id }})"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:text-rose-400 dark:hover:bg-rose-900/30 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('hotel::modules.agreement.noAgreements') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($agreements->hasPages())
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $agreements->links() }}
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         GENERATE AGREEMENT MODAL
    ══════════════════════════════════════════ --}}
    <x-right-modal wire:model.live="showAddModal" maxWidth="2xl">
        <x-slot name="title">{{ __('hotel::modules.agreement.generateAgreement') }}</x-slot>
        <x-slot name="content">
            @if($showAddModal)
                @if($selectedReservationId)
                    <livewire:hotel::forms.add-agreement
                        :reservationId="(int) $selectedReservationId"
                        :key="'add-agreement-' . $selectedReservationId" />
                @else
                    {{-- No reservation selected: show reservation selector --}}
                    <div class="p-4 space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('hotel::modules.agreement.selectReservationFirst') }}</p>
                        @php
                            $reservations = \Modules\Hotel\Entities\Reservation::with('primaryGuest')
                                ->whereIn('status', ['tentative','confirmed','checked_in','checked_out'])
                                ->latest()->take(50)->get();
                        @endphp
                        <x-select wire:model.live="selectedReservationId" class="block w-full">
                            <option value="">{{ __('hotel::modules.agreement.selectReservation') }}</option>
                            @foreach($reservations as $res)
                                <option value="{{ $res->id }}">
                                    {{ $res->reservation_number }} — {{ $res->primaryGuest?->full_name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                @endif
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddModal', false)">{{ __('app.close') }}</x-secondary-button>
        </x-slot>
    </x-right-modal>

    <x-confirmation-modal wire:model="confirmDeleteAgreementModal">
        <x-slot name="title">{{ __('app.delete') }}</x-slot>
        <x-slot name="content">{{ __('hotel::modules.agreement.deleteConfirm') }}</x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmDeleteAgreementModal')">{{ __('app.cancel') }}</x-secondary-button>
            <x-danger-button class="ml-3" wire:click="deleteAgreement">{{ __('app.delete') }}</x-danger-button>
        </x-slot>
    </x-confirmation-modal>

</div>
