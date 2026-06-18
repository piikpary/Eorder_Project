<div class="space-y-4">
    <div class="text-sm text-gray-600 dark:text-gray-300">
        {{ __('inventory::modules.inventoryItem.importHint') }}
    </div>

    <a href="{{ asset('sample-files/inventory-items.csv') }}" download
       class="inline-flex items-center justify-center cursor-pointer px-3 py-2 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-primary-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
        <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                  d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                  clip-rule="evenodd"></path>
        </svg>
        @lang('app.downloadSample')
    </a>

    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 space-y-3">
        <div>
            <x-label value="{{ __('inventory::modules.inventoryItem.uploadFile') }}" />
            <input type="file"
                   class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300"
                   wire:model="file"
                   accept=".csv,.txt,.xlsx,.xls" />
            <x-input-error for="file" class="mt-2" />
            <div wire:loading wire:target="file" class="text-xs text-gray-500 mt-1">
                {{ __('app.uploading') }}...
            </div>
        </div>

        <div class="flex gap-3">
            <x-button type="button" wire:click="import" wire:loading.attr="disabled">
                {{ __('inventory::modules.inventoryItem.import') }}
            </x-button>

            <x-secondary-button type="button" wire:click="$dispatch('hideImportInventoryItemModal')" wire:loading.attr="disabled">
                {{ __('app.cancel') }}
            </x-secondary-button>
        </div>
    </div>
</div>

