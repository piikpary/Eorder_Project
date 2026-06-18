<?php

namespace Modules\Inventory\Livewire\InventoryItem;

use Livewire\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Inventory\Imports\InventoryItemsImport;

class ImportInventoryItems extends Component
{
    use WithFileUploads;
    use LivewireAlert;

    public $file;

    protected function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:csv,txt,xlsx,xls',
        ];
    }

    public function import(): void
    {
        abort_if(!user_can('Create Inventory Item'), 403);

        $this->validate();

        try {
            $import = new InventoryItemsImport(branch()->id);
            Excel::import($import, $this->file);

            $this->dispatch('inventoryItemImported');
            $this->dispatch('hideImportInventoryItemModal');

            $this->reset('file');

            $this->alert('success', __('inventory::modules.inventoryItem.importSuccess', [
                'created' => $import->createdCount(),
                'updated' => $import->updatedCount(),
                'skipped' => $import->skippedCount(),
            ]));
        } catch (\Throwable $e) {
            report($e);
            $message = __('inventory::modules.inventoryItem.importFailed');
            if (config('app.debug')) {
                $message .= ' (' . $e->getMessage() . ')';
            }

            $this->alert('error', $message);
        }
    }

    public function render()
    {
        return view('inventory::livewire.inventory-item.import-inventory-items');
    }
}

