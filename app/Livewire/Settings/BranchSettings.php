<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use App\Services\BranchMenuCloneService;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

class BranchSettings extends Component
{
    use LivewireAlert;

    // Form fields
    public $branchName;
    public $branchAddress;
    public string $branchCrNumber = '';
    public string $branchVatNumber = '';
    public $branchLat = '26.9125';
    public $branchLng = '75.7875';
    public $isEditing = false;
    public $confirmDeleteBranchModal = false;
    public $activeBranch = null;
    public $activeBranchId = null;
    public $formMode = 'add';
    public $cloneData;
    public $cloneMenu = false;
    public $clonecategories = false;
    public $cloneMenuItems = false;
    public $cloneItemModifires = false;
    public $cloneModifiersGroups = false;
    public $cloneReservationSettings = false;
    public $cloneDeliverySettings = false;
    public $cloneKotSettings = false;
    public bool $showCloneDependencyNote = false;
    public $menus;
    public $menu;

    // Branch-specific loyalty card settings
    public bool $loyaltyCardEnabled = true;
    public ?string $loyaltyCardTitle = '';
    public ?string $loyaltyCardSubtitle = 'Loyalty Member';

    public string $loyaltyPrimaryColor = '#d4a017';
    public string $loyaltySecondaryColor = '#9a6b00';
    public string $loyaltyBackgroundColor = '#fffaf0';
    public string $loyaltyTextColor = '#2f2100';
    public string $loyaltyMutedTextColor = '#786b55';
    public string $loyaltyBorderColor = '#d4a017';
    public string $loyaltyButtonColor = '#d4a017';
    public string $loyaltyButtonTextColor = '#ffffff';

    public bool $loyaltyShowCustomerName = true;
    public bool $loyaltyShowPhone = true;
    public bool $loyaltyShowMemberType = true;
    public bool $loyaltyShowPoints = true;
    public bool $loyaltyShowPointsValue = true;

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->branchName = '';
        $this->branchAddress = '';
        $this->branchCrNumber = '';
        $this->branchVatNumber = '';
        $this->branchLat = '26.9125';
        $this->branchLng = '75.7875';
        $this->activeBranchId = null;
        $this->formMode = 'add';
        $this->isEditing = false;
        $this->cloneData = null;
        $this->cloneMenu = false;
        $this->clonecategories = false;
        $this->cloneMenuItems = false;
        $this->cloneItemModifires = false;
        $this->cloneModifiersGroups = false;
        $this->cloneReservationSettings = false;
        $this->cloneDeliverySettings = false;
        $this->cloneKotSettings = false;
        $this->showCloneDependencyNote = false;

        $this->loyaltyCardEnabled = true;
        $this->loyaltyCardTitle = '';
        $this->loyaltyCardSubtitle = 'Loyalty Member';

        $this->loyaltyPrimaryColor = '#d4a017';
        $this->loyaltySecondaryColor = '#9a6b00';
        $this->loyaltyBackgroundColor = '#fffaf0';
        $this->loyaltyTextColor = '#2f2100';
        $this->loyaltyMutedTextColor = '#786b55';
        $this->loyaltyBorderColor = '#d4a017';
        $this->loyaltyButtonColor = '#d4a017';
        $this->loyaltyButtonTextColor = '#ffffff';

        $this->loyaltyShowCustomerName = true;
        $this->loyaltyShowPhone = true;
        $this->loyaltyShowMemberType = true;
        $this->loyaltyShowPoints = true;
        $this->loyaltyShowPointsValue = true;
    }

    private function checkBranchLimit(): bool
    {
        if (!in_array('Change Branch', restaurant_modules(), true)) {
            abort(403, __('messages.invalidRequest'));
        }

        $restaurant = restaurant();
        $branchLimit = $restaurant->package->branch_limit;

        if (is_null($branchLimit) || $branchLimit === -1) {
            return true;
        }

        if (
            $branchLimit === 0
            || $restaurant->branches()->count() >= $branchLimit
        ) {
            $this->alert('error', __('messages.branchLimitReached'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close'),
            ]);

            return false;
        }

        return true;
    }

    public function createMode()
    {
        if (!$this->checkBranchLimit()) {
            return;
        }

        $this->dispatch('initAddressMap');

        $this->resetForm();
        $this->formMode = 'add';
        $this->isEditing = true;
    }

    public function showEditBranch($id)
    {
        $this->editMode($id);
    }

    public function editMode($id)
    {
        $branch = Branch::findOrFail($id);

        $this->activeBranchId = $branch->id;
        $this->activeBranch = $branch;
        $this->branchName = $branch->name;
        $this->branchAddress = $branch->address;
        $this->branchCrNumber = (string) ($branch->cr_number ?? '');
        $this->branchVatNumber = (string) ($branch->vat_number ?? '');
        $this->branchLat = $branch->lat ?? '26.9125';
        $this->branchLng = $branch->lng ?? '75.7875';

        $loyaltySettings = $branch->loyalty_card_settings;

        if (is_string($loyaltySettings)) {
            $loyaltySettings = json_decode(
                $loyaltySettings,
                true
            );
        }

        if (!is_array($loyaltySettings)) {
            $loyaltySettings = [];
        }

        $this->loyaltyCardEnabled =
            (bool) ($loyaltySettings['enabled'] ?? true);

        $this->loyaltyCardTitle =
            (string) ($loyaltySettings['title'] ?? $branch->name);

        $this->loyaltyCardSubtitle =
            (string) (
                $loyaltySettings['subtitle']
                ?? 'Loyalty Member'
            );

        $this->loyaltyPrimaryColor =
            (string) (
                $loyaltySettings['primary_color']
                ?? '#d4a017'
            );

        $this->loyaltySecondaryColor =
            (string) (
                $loyaltySettings['secondary_color']
                ?? '#9a6b00'
            );

        $this->loyaltyBackgroundColor =
            (string) (
                $loyaltySettings['background_color']
                ?? '#fffaf0'
            );

        $this->loyaltyTextColor =
            (string) (
                $loyaltySettings['text_color']
                ?? '#2f2100'
            );

        $this->loyaltyMutedTextColor =
            (string) (
                $loyaltySettings['muted_text_color']
                ?? '#786b55'
            );

        $this->loyaltyBorderColor =
            (string) (
                $loyaltySettings['border_color']
                ?? '#d4a017'
            );

        $this->loyaltyButtonColor =
            (string) (
                $loyaltySettings['button_color']
                ?? '#d4a017'
            );

        $this->loyaltyButtonTextColor =
            (string) (
                $loyaltySettings['button_text_color']
                ?? '#ffffff'
            );

        $this->loyaltyShowCustomerName =
            (bool) (
                $loyaltySettings['show_customer_name']
                ?? true
            );

        $this->loyaltyShowPhone =
            (bool) (
                $loyaltySettings['show_phone']
                ?? true
            );

        $this->loyaltyShowMemberType =
            (bool) (
                $loyaltySettings['show_member_type']
                ?? true
            );

        $this->loyaltyShowPoints =
            (bool) (
                $loyaltySettings['show_points']
                ?? true
            );

        $this->loyaltyShowPointsValue =
            (bool) (
                $loyaltySettings['show_points_value']
                ?? true
            );

        $this->formMode = 'edit';
        $this->isEditing = true;

        $this->dispatch('initAddressMap');
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function showDeleteBranch($id)
    {
        $this->activeBranch = Branch::findOrFail($id);
        $this->activeBranchId = $id;
        $this->confirmDeleteBranchModal = true;
    }

    public function deleteBranch()
    {
        Branch::destroy($this->activeBranchId);

        $this->activeBranch = null;
        $this->activeBranchId = null;
        $this->confirmDeleteBranchModal = false;

        session(['branches' => Branch::get()]);

        $this->alert('success', __('messages.branchDeleted'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close'),
        ]);
    }

    #[On('updateLivewireMapProperties')]
    public function updateLivewireMapProperties($lat, $lng)
    {
        $this->branchLat = $lat;
        $this->branchLng = $lng;
    }

    public function saveBranch()
    {
        if (
            $this->formMode === 'add'
            && !$this->checkBranchLimit()
        ) {
            $this->alert('error', __('messages.invalidRequest'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close'),
            ]);

            return;
        }

        if ($this->formMode === 'add') {
            $rules = [
                'branchName' =>
                    'required|unique:branches,name,null,id,restaurant_id,'
                    . restaurant()->id,

                'branchAddress' => 'required',
                'branchCrNumber' => 'nullable|string|max:50',
                'branchVatNumber' => 'nullable|string|max:50',
                'branchLat' => 'required|numeric|between:-90,90',
                'branchLng' => 'required|numeric|between:-180,180',

                'loyaltyCardEnabled' => 'boolean',
                'loyaltyCardTitle' => 'nullable|string|max:100',
                'loyaltyCardSubtitle' => 'nullable|string|max:100',

                'loyaltyPrimaryColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltySecondaryColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyBackgroundColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyMutedTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyBorderColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyButtonColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyButtonTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyShowCustomerName' => 'boolean',
                'loyaltyShowPhone' => 'boolean',
                'loyaltyShowMemberType' => 'boolean',
                'loyaltyShowPoints' => 'boolean',
                'loyaltyShowPointsValue' => 'boolean',
            ];

            if ($this->cloneMenuItems) {
                $rules['clonecategories'] = 'accepted';
                $rules['cloneMenu'] = 'accepted';
            }

            if ($this->cloneItemModifires) {
                $rules['cloneMenuItems'] = 'accepted';
            }

            $this->validate($rules, [
                'clonecategories.accepted' =>
                    __('messages.cloneCategoriesRequired'),

                'cloneMenu.accepted' =>
                    __('messages.cloneMenuRequired'),

                'cloneMenuItems.accepted' =>
                    __('messages.cloneMenuItemRequired'),
            ]);

            $newBranch = Branch::create([
                'name' => $this->branchName,
                'restaurant_id' => restaurant()->id,
                'address' => $this->branchAddress,

                'cr_number' =>
                    $this->branchCrNumber !== ''
                        ? $this->branchCrNumber
                        : null,

                'vat_number' =>
                    $this->branchVatNumber !== ''
                        ? $this->branchVatNumber
                        : null,

                'lat' => $this->branchLat,
                'lng' => $this->branchLng,

                'loyalty_card_settings' =>
                    $this->loyaltyCardSettingsPayload(),

                'cloned_branch_name' =>
                    $this->cloneData
                        ? Branch::find($this->cloneData)->name
                        : null,

                'cloned_branch_id' => $this->cloneData,
                'is_menu_clone' => $this->cloneMenu,
                'is_item_categories_clone' => $this->clonecategories,
                'is_menu_items_clone' => $this->cloneMenuItems,
                'is_item_modifiers_clone' => $this->cloneItemModifires,
                'is_modifiers_groups_clone' =>
                    $this->cloneModifiersGroups,

                'is_clone_reservation_settings' =>
                    $this->cloneReservationSettings,

                'is_clone_delivery_settings' =>
                    $this->cloneDeliverySettings,

                'is_clone_kot_setting' =>
                    $this->cloneKotSettings,
            ]);

            if ($this->cloneData) {
                $this->cloneBranchData(
                    $this->cloneData,
                    $newBranch
                );
            }

            $this->alert('success', __('messages.branchAdded'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close'),
            ]);
        } else {
            $rules = [
                'branchName' =>
                    'required|unique:branches,name,'
                    . $this->activeBranchId
                    . ',id,restaurant_id,'
                    . restaurant()->id,

                'branchAddress' => 'required',
                'branchCrNumber' => 'nullable|string|max:50',
                'branchVatNumber' => 'nullable|string|max:50',
                'branchLat' => 'required|numeric|between:-90,90',
                'branchLng' => 'required|numeric|between:-180,180',

                'loyaltyCardEnabled' => 'boolean',
                'loyaltyCardTitle' => 'nullable|string|max:100',
                'loyaltyCardSubtitle' => 'nullable|string|max:100',

                'loyaltyPrimaryColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltySecondaryColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyBackgroundColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyMutedTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyBorderColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyButtonColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyButtonTextColor' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'loyaltyShowCustomerName' => 'boolean',
                'loyaltyShowPhone' => 'boolean',
                'loyaltyShowMemberType' => 'boolean',
                'loyaltyShowPoints' => 'boolean',
                'loyaltyShowPointsValue' => 'boolean',
            ];

            if ($this->cloneMenuItems) {
                $rules['clonecategories'] = 'accepted';
                $rules['cloneMenu'] = 'accepted';
            }

            if ($this->cloneItemModifires) {
                $rules['cloneMenuItems'] = 'accepted';
            }

            $this->validate($rules, [
                'clonecategories.accepted' =>
                    __('messages.cloneCategoriesRequired'),

                'cloneMenu.accepted' =>
                    __('messages.cloneMenuRequired'),

                'cloneMenuItems.accepted' =>
                    __('messages.cloneMenuItemRequired'),
            ]);

            Branch::where(
                'id',
                $this->activeBranchId
            )->update([
                'name' => $this->branchName,
                'restaurant_id' => restaurant()->id,
                'address' => $this->branchAddress,

                'cr_number' =>
                    $this->branchCrNumber !== ''
                        ? $this->branchCrNumber
                        : null,

                'vat_number' =>
                    $this->branchVatNumber !== ''
                        ? $this->branchVatNumber
                        : null,

                'lat' => $this->branchLat,
                'lng' => $this->branchLng,

                'loyalty_card_settings' => json_encode(
                    $this->loyaltyCardSettingsPayload(),
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                ),

                'cloned_branch_name' =>
                    $this->cloneData
                        ? Branch::find($this->cloneData)->name
                        : null,

                'cloned_branch_id' => $this->cloneData,
                'is_menu_clone' => $this->cloneMenu,
                'is_item_categories_clone' => $this->clonecategories,
                'is_menu_items_clone' => $this->cloneMenuItems,
                'is_item_modifiers_clone' => $this->cloneItemModifires,
                'is_modifiers_groups_clone' =>
                    $this->cloneModifiersGroups,

                'is_clone_reservation_settings' =>
                    $this->cloneReservationSettings,

                'is_clone_delivery_settings' =>
                    $this->cloneDeliverySettings,

                'is_clone_kot_setting' =>
                    $this->cloneKotSettings,
            ]);

            $this->activeBranch = Branch::find(
                $this->activeBranchId
            );

            if ($this->cloneData) {
                $this->cloneBranchData(
                    $this->cloneData,
                    $this->activeBranch
                );
            }

            $this->alert('success', __('messages.branchUpdated'), [
                'toast' => true,
                'position' => 'top-end',
                'showCancelButton' => false,
                'cancelButtonText' => __('app.close'),
            ]);
        }

        session(['branches' => Branch::get()]);

        $this->resetForm();
    }

    private function loyaltyCardSettingsPayload(): array
    {
        return [
            'enabled' => $this->loyaltyCardEnabled,
            'title' => trim(
                (string) $this->loyaltyCardTitle
            ),
            'subtitle' => trim(
                (string) $this->loyaltyCardSubtitle
            ),

            'primary_color' =>
                $this->loyaltyPrimaryColor,

            'secondary_color' =>
                $this->loyaltySecondaryColor,

            'background_color' =>
                $this->loyaltyBackgroundColor,

            'text_color' =>
                $this->loyaltyTextColor,

            'muted_text_color' =>
                $this->loyaltyMutedTextColor,

            'border_color' =>
                $this->loyaltyBorderColor,

            'button_color' =>
                $this->loyaltyButtonColor,

            'button_text_color' =>
                $this->loyaltyButtonTextColor,

            'show_customer_name' =>
                $this->loyaltyShowCustomerName,

            'show_phone' =>
                $this->loyaltyShowPhone,

            'show_member_type' =>
                $this->loyaltyShowMemberType,

            'show_points' =>
                $this->loyaltyShowPoints,

            'show_points_value' =>
                $this->loyaltyShowPointsValue,
        ];
    }

    protected function cloneBranchData(
        $sourceBranchId,
        Branch $newBranch
    ): void {
        if (!$sourceBranchId) {
            return;
        }

        app(BranchMenuCloneService::class)->clone(
            (int) $sourceBranchId,
            $newBranch,
            [
                'clone_menu' => $this->cloneMenu,
                'clone_categories' => $this->clonecategories,
                'clone_menu_items' => $this->cloneMenuItems,
                'clone_item_modifiers' => $this->cloneItemModifires,
                'clone_modifier_groups' =>
                    $this->cloneModifiersGroups,
            ]
        );
    }

    public function handleCloneMenuItemsChange(): void
    {
        if ($this->cloneMenuItems) {
            $autoSelected = false;

            if (!$this->cloneMenu) {
                $this->cloneMenu = true;
                $autoSelected = true;
            }

            if (!$this->clonecategories) {
                $this->clonecategories = true;
                $autoSelected = true;
            }

            $this->showCloneDependencyNote =
                $autoSelected;
        } else {
            $this->cloneItemModifires = false;
            $this->showCloneDependencyNote = false;
        }
    }

    public function handleCloneItemModifiersChange(): void
    {
        if ($this->cloneItemModifires) {
            $autoSelected = false;

            foreach ([
                'cloneMenuItems' => true,
                'cloneMenu' => true,
                'clonecategories' => true,
                'cloneModifiersGroups' => true,
            ] as $property => $value) {
                if (!$this->{$property}) {
                    $this->{$property} = $value;
                    $autoSelected = true;
                }
            }

            $this->showCloneDependencyNote =
                $autoSelected;
        } else {
            $this->showCloneDependencyNote = false;
        }
    }

    public function dismissCloneDependencyNote(): void
    {
        $this->showCloneDependencyNote = false;
    }

    public function render()
    {
        $branches = Branch::where(
            'restaurant_id',
            restaurant()->id
        )->get();

        $mapApiKey =
            global_setting()->google_map_api_key
            ?? null;

        $mapProvider =
            global_setting()->map_provider
            ?? 'google';

        return view(
            'livewire.settings.branch-settings',
            [
                'branches' => $branches,
                'mapApiKey' => $mapApiKey,
                'mapProvider' => $mapProvider,
            ]
        );
    }
}
