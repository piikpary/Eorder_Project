<div>
    <form wire:submit="submitForm">

        {{-- Reservation Info Banner --}}
        @if($reservation)
        <div class="mb-4 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">
                        {{ $reservation->reservation_number }}
                    </p>
                    <p class="text-xs text-indigo-600 dark:text-indigo-300">
                        {{ $reservation->primaryGuest?->full_name }} &nbsp;·&nbsp;
                        {{ $reservation->check_in_date?->format('d M Y') }} → {{ $reservation->check_out_date?->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="space-y-4">
            {{-- Agreement Type --}}
            <div>
                <x-label for="type" value="{{ __('hotel::modules.agreement.agreementType') }}" required />
                <x-select id="type" wire:model.live="type" class="block mt-1 w-full">
                    @foreach($agreementTypes as $agreementType)
                        <option value="{{ $agreementType->value }}">{{ $agreementType->label() }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="type" class="mt-2" />
            </div>

            {{-- Agreement Date --}}
            <div>
                <x-label for="agreement_date" value="{{ __('hotel::modules.agreement.agreementDate') }}" required />
                <x-input id="agreement_date" type="date" wire:model="agreement_date" class="block mt-1 w-full" />
                <x-input-error for="agreement_date" class="mt-2" />
            </div>

            {{-- Notes --}}
            <div>
                <x-label for="notes" value="{{ __('hotel::modules.agreement.notes') }}" />
                <textarea id="notes" wire:model="notes" rows="2"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-skin-base focus:ring focus:ring-skin-base focus:ring-opacity-50 dark:bg-gray-800 dark:text-white dark:border-gray-600"></textarea>
            </div>

            {{-- Content (editable) --}}
            <div>
                <x-label for="content" value="{{ __('hotel::modules.agreement.content') }}" required />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">
                    {{ __('hotel::modules.agreement.contentHint') }}
                </p>
                <textarea id="content" wire:model="content" rows="22"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-skin-base focus:ring focus:ring-skin-base focus:ring-opacity-50 font-mono text-xs dark:bg-gray-800 dark:text-white dark:border-gray-600"
                    style="min-height: 420px;"></textarea>
                <x-input-error for="content" class="mt-2" />
            </div>
        </div>

        <div class="flex gap-3 mt-6 pb-4">
            <x-button wire:loading.attr="disabled">
                {{ __('hotel::modules.agreement.generate') }}
            </x-button>
            <x-button-cancel type="button" wire:click="cancel" wire:loading.attr="disabled">
                {{ __('app.cancel') }}
            </x-button-cancel>
        </div>
    </form>
</div>
