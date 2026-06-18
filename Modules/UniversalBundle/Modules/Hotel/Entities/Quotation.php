<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Models\User;
use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\Hotel\Enums\QuotationStatus;

class Quotation extends BaseModel
{
    use HasBranch;

    protected $table = 'hotel_quotations';

    protected $guarded = ['id'];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'status' => QuotationStatus::class,
        'rooms_count' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
        'total_amount' => 'decimal:2',
        'advance_paid' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'subtotal_before_tax' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'extras_amount' => 'decimal:2',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function primaryGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'primary_guest_id');
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class, 'rate_plan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotationGuests(): HasMany
    {
        return $this->hasMany(QuotationGuest::class, 'quotation_id')->orderBy('sort_order');
    }

    public function quotationRooms(): HasMany
    {
        return $this->hasMany(QuotationRoom::class, 'quotation_id');
    }

    public function quotationExtras(): HasMany
    {
        return $this->hasMany(QuotationExtra::class, 'quotation_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'hotel_quotation_tax', 'quotation_id', 'tax_id')
            ->withTimestamps();
    }

    /**
     * Taxes to print on invoices/receipts. Amounts split by rate weight so they sum to {@see $tax_amount}.
     *
     * @return Collection<int, array{tax: Tax, amount: float}>
     */
    public function invoiceTaxes(): Collection
    {
        $totalTax = round((float) ($this->tax_amount ?? 0), 2);

        $taxModels = $this->taxes->isNotEmpty()
            ? $this->taxes
            : ($this->tax ? collect([$this->tax]) : collect());

        if ($taxModels->isEmpty()) {
            return collect();
        }

        if ($totalTax <= 0) {
            return $taxModels->map(fn (Tax $t) => ['tax' => $t, 'amount' => 0.0])->values();
        }

        $rateSum = (float) $taxModels->sum(fn (Tax $t) => (float) $t->rate);

        if ($rateSum <= 0) {
            return $taxModels->values()->map(function (Tax $t, int $i) use ($totalTax): array {
                return [
                    'tax' => $t,
                    'amount' => $i === 0 ? $totalTax : 0.0,
                ];
            });
        }

        $allocated = 0.0;
        $indexed = $taxModels->values();

        return $indexed->map(function (Tax $t, int $i) use ($indexed, $totalTax, $rateSum, &$allocated): array {
            if ($i === $indexed->count() - 1) {
                $amount = round($totalTax - $allocated, 2);
            } else {
                $amount = round($totalTax * ((float) $t->rate / $rateSum), 2);
                $allocated += $amount;
            }

            return ['tax' => $t, 'amount' => $amount];
        });
    }

    public static function generateQuotationNumber(?int $branchId): string
    {
        $prefix = 'QUO';
        $year = date('Y');
        $month = date('m');

        $last = self::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('quotation_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last?->quotation_number) {
            $lastNumber = (int) substr($last->quotation_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }
}

