<div>
    <div class="px-5 pt-5 pb-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl tracking-tight">
                    {{ __('hotel::modules.quotation.quotations') }}
                </h1>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                @if(user_can('Create Hotel Quotation'))
                    <a href="{{ route('hotel.quotations.create') }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-lg bg-skin-base text-white hover:opacity-90 transition shadow-sm w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('hotel::modules.quotation.newQuotation') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1 min-w-0 sm:max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                    </svg>
                </div>
                <x-input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('hotel::modules.quotation.searchPlaceholder') }}"
                    class="block w-full pl-9" />
            </div>

            <x-input type="date" wire:model.live="filterDate" class="block w-full sm:w-40" />

            <x-select wire:model.live="filterStatus" class="block w-full sm:w-44">
                <option value="">{{ __('hotel::modules.quotation.allStatuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.quotationNumber') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.guest') }}</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.date') }}</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.amount') }}</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.status') }}</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('hotel::modules.quotation.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @php
                    $statusCfg = [
                        'draft' => ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600'],
                        'sent' => ['dot' => 'bg-blue-500', 'badge' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-700'],
                        'accepted' => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-700'],
                        'rejected' => ['dot' => 'bg-rose-500', 'badge' => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-700'],
                    ];
                @endphp

                @forelse($quotations as $quotation)
                    @php
                        $sc = $statusCfg[$quotation->status->value ?? 'draft'] ?? ['dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-600 ring-gray-200'];
                        $initials = collect(explode(' ', $quotation->primaryGuest?->full_name ?? ''))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                        $canEdit = user_can('Update Hotel Quotation');
                    @endphp
                    <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors duration-100">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $quotation->quotation_number }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold shrink-0">
                                    {{ $initials }}
                                </span>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate max-w-[140px]">
                                    {{ $quotation->primaryGuest?->full_name ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ optional($quotation->check_in_date)->format('M d, Y') }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ currency_format($quotation->total_amount) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $sc['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} shrink-0"></span>
                                {{ $quotation->status?->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2 flex-wrap">
                                <a target="_blank" href="{{ route('hotel.quotations.confirmation', $quotation) }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                    {{ __('hotel::modules.quotation.quotaConPdf') }}
                                </a>

                                @if(user_can('Update Hotel Quotation'))
                                    <button type="button"
                                        wire:click="emailQuotationConfirmation({{ $quotation->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                        {{ __('hotel::modules.quotation.emailQuotaCon') }}
                                    </button>
                                @endif

                                @if(user_can('Create Hotel Reservation'))
                                    <a href="{{ route('hotel.reservations.create', ['fromQuotation' => $quotation->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-md bg-skin-base text-white hover:opacity-90 transition shadow-sm">
                                        {{ __('hotel::modules.quotation.makeReservation') }}
                                    </a>
                                @endif

                                @if($canEdit)
                                    <a href="{{ route('hotel.quotations.edit', $quotation) }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                        {{ __('hotel::modules.quotation.edit') }}
                                    </a>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('hotel::modules.quotation.noQuotationsFound') }}
                                </p>
                                @if(user_can('Create Hotel Quotation'))
                                    <a href="{{ route('hotel.quotations.create') }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                                        {{ __('hotel::modules.quotation.createFirstQuotation') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $quotations->links() }}
    </div>
</div>

