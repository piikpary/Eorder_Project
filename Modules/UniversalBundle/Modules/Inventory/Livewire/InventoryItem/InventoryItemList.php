<?php

namespace Modules\Inventory\Livewire\InventoryItem;

use Livewire\Component;
use Livewire\Attributes\On;

class InventoryItemList extends Component
{
    public $search = '';
    public $showAddInventoryItem = false;
    public $showEditInventoryItemModal = false;
    public $showImportInventoryItemModal = false;

    #[On('hideAddInventoryItem')]
    public function hideAddInventoryItem()
    {
        $this->showAddInventoryItem = false;
    }

    #[On('hideEditInventoryItemModal')]
    public function hideEditInventoryItemModal()
    {
        $this->showEditInventoryItemModal = false;
    }

    #[On('hideImportInventoryItemModal')]
    public function hideImportInventoryItemModal()
    {
        $this->showImportInventoryItemModal = false;
    }

    public function closeImportInventoryItemModal(): void
    {
        $this->showImportInventoryItemModal = false;
    }

    public function render()
    {
        return view('inventory::livewire.inventory-item.inventory-item-list');
    }
}
