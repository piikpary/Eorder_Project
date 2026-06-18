<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Traits\HasBranch;
use Modules\Hotel\Enums\ReservationStatus;
use Modules\Hotel\Enums\PricingType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Reservation extends BaseModel
{
    use HasBranch;

    protected $table = 'hotel_reservations';

    protected $guarded = ['id'];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'status' => ReservationStatus::class,
        'rooms_count' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
        'total_amount' => 'decimal:2',
        'advance_paid' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'discount_value' => 'decimal:2',
        'subtotal_before_tax' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'extras_amount' => 'decimal:2',
        'pricing_type'  => PricingType::class,
    ];

    public function setChildrenAttribute($value): void
    {
        if ($value === '' || $value === null) {
            $this->attributes['children'] = 0;

            return;
        }
        $this->attributes['children'] = max(0, (int) $value);
    }

    public function setAdvancePaidAttribute($value): void
    {
        if ($value === '' || $value === null) {
            $this->attributes['advance_paid'] = '0.00';

            return;
        }
        $this->attributes['advance_paid'] = number_format(max(0, (float) $value), 2, '.', '');
    }

    public function setSecurityDepositAttribute($value): void
    {
        if ($value === '' || $value === null) {
            $this->attributes['security_deposit'] = '0.00';

            return;
        }
        $this->attributes['security_deposit'] = number_format(max(0, (float) $value), 2, '.', '');
    }

    public function setDiscountValueAttribute($value): void
    {
        if ($value === '' || $value === null) {
            $this->attributes['discount_value'] = '0.00';

            return;
        }
        $this->attributes['discount_value'] = number_format((float) $value, 2, '.', '');
    }

    public function setRatePlanIdAttribute($value): void
    {
        $this->attributes['rate_plan_id'] = ($value === '' || $value === null) ? null : (int) $value;
    }

    public function setTaxIdAttribute($value): void
    {
        $this->attributes['tax_id'] = ($value === '' || $value === null) ? null : (int) $value;
    }

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

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function reservationRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class, 'reservation_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'hotel_reservation_tax', 'reservation_id', 'tax_id')
            ->withTimestamps();
    }

    public function reservationGuests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class, 'reservation_id')->orderBy('sort_order');
    }

    public function reservationExtras(): HasMany
    {
        return $this->hasMany(ReservationExtra::class, 'reservation_id');
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class, 'reservation_id');
    }

    /**
     * Generate a unique reservation number
     */
    public static function generateReservationNumber($branchId): string
    {
        $prefix = 'RES';
        $year = date('Y');
        $month = date('m');

        $lastReservation = self::where('branch_id', $branchId)
            ->where('reservation_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReservation) {
            $lastNumber = (int) substr($lastReservation->reservation_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
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
}
