<?php

namespace Modules\Inventory\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryItemCategory;
use Modules\Inventory\Entities\Supplier;
use Modules\Inventory\Entities\Unit;

class InventoryItemsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $branchId;

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** @var array<string, bool>|null */
    private static ?array $inventoryItemColumns = null;

    /** @var array<string,int> */
    private array $categoryCache = [];

    /** @var array<string,int> */
    private array $unitCache = [];

    /** @var array<string,int> */
    private array $supplierCache = [];

    public function __construct(int $branchId)
    {
        $this->branchId = $branchId;
    }

    public function createdCount(): int
    {
        return $this->created;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    public function skippedCount(): int
    {
        return $this->skipped;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $name = $this->cleanString($row['name'] ?? null);
            if (!$name) {
                $this->skipped++;
                continue;
            }

            $data = [
                'branch_id' => $this->branchId,
                'name' => $name,
            ];

            $description = $this->cleanString($row['description'] ?? null);
            $this->setIfColumnExists($data, 'description', $description);

            $categoryName = $this->cleanString($row['category'] ?? null);
            if ($categoryName) {
                $this->setIfColumnExists($data, 'inventory_item_category_id', $this->getOrCreateCategoryId($categoryName));
            }

            $unitName = $this->cleanString($row['unit'] ?? null);
            $unitSymbol = $this->cleanString($row['unit_symbol'] ?? null);
            if ($unitName || $unitSymbol) {
                $this->setIfColumnExists($data, 'unit_id', $this->getOrCreateUnitId($unitName, $unitSymbol));
            }

            $threshold = $this->toNumber($row['threshold_quantity'] ?? null);
            $this->setIfColumnExists($data, 'threshold_quantity', $threshold);

            $reorderQty = $this->toNumber($row['reorder_quantity'] ?? null);
            $this->setIfColumnExists($data, 'reorder_quantity', $reorderQty);

            $purchasePrice = $this->toNumber($row['unit_purchase_price'] ?? null);
            $this->setIfColumnExists($data, 'unit_purchase_price', $purchasePrice);

            $preferredSupplier = $this->cleanString($row['preferred_supplier'] ?? null);
            if ($preferredSupplier) {
                $this->setIfColumnExists($data, 'preferred_supplier_id', $this->findSupplierId($preferredSupplier));
            }

            $existing = InventoryItem::withoutGlobalScopes()
                ->where('branch_id', $this->branchId)
                ->where('name', $name)
                ->first();

            if ($existing) {
                $existing->fill($data);
                $existing->save();
                $this->updated++;
            } else {
                InventoryItem::create($data);
                $this->created++;
            }
        }
    }

    private function setIfColumnExists(array &$data, string $column, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!$this->inventoryItemHasColumn($column)) {
            return;
        }

        $data[$column] = $value;
    }

    private function inventoryItemHasColumn(string $column): bool
    {
        if (self::$inventoryItemColumns === null) {
            $columns = Schema::getColumnListing((new InventoryItem())->getTable());
            self::$inventoryItemColumns = array_fill_keys($columns, true);
        }

        return isset(self::$inventoryItemColumns[$column]);
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }

        return $v;
    }

    private function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $v = is_numeric($value) ? (float) $value : null;
        if ($v === null) {
            // Try to normalize "1,234.56"
            $normalized = str_replace([',', ' '], ['', ''], (string) $value);
            if (is_numeric($normalized)) {
                $v = (float) $normalized;
            }
        }

        return $v;
    }

    private function getOrCreateCategoryId(string $name): int
    {
        $key = Str::lower($name);
        if (isset($this->categoryCache[$key])) {
            return $this->categoryCache[$key];
        }

        $category = InventoryItemCategory::withoutGlobalScopes()
            ->where('branch_id', $this->branchId)
            ->where('name', $name)
            ->first();

        if (!$category) {
            $category = InventoryItemCategory::create([
                'branch_id' => $this->branchId,
                'name' => $name,
            ]);
        }

        return $this->categoryCache[$key] = (int) $category->id;
    }

    private function getOrCreateUnitId(?string $unitName, ?string $unitSymbol): int
    {
        $cacheKey = Str::lower(trim(($unitName ?? '') . '|' . ($unitSymbol ?? '')));
        if (isset($this->unitCache[$cacheKey])) {
            return $this->unitCache[$cacheKey];
        }

        $query = Unit::withoutGlobalScopes()->where('branch_id', $this->branchId);

        if ($unitName) {
            $query->where('name', $unitName);
        } elseif ($unitSymbol) {
            $query->where('symbol', $unitSymbol);
        }

        $unit = $query->first();

        if (!$unit) {
            $unit = Unit::create([
                'branch_id' => $this->branchId,
                'name' => $unitName ?: $unitSymbol,
                'symbol' => $unitSymbol ?: Str::lower(substr((string) $unitName, 0, 3)),
            ]);
        }

        return $this->unitCache[$cacheKey] = (int) $unit->id;
    }

    private function findSupplierId(string $preferredSupplier): ?int
    {
        $key = Str::lower($preferredSupplier);
        if (isset($this->supplierCache[$key])) {
            return $this->supplierCache[$key];
        }

        // Supplier model is scoped by restaurant, not branch
        $supplier = Supplier::query()
            ->where('name', $preferredSupplier)
            ->first();

        return $this->supplierCache[$key] = $supplier ? (int) $supplier->id : null;
    }
}

