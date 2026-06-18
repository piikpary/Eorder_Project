<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasBranch;
use Modules\Hotel\Enums\AgreementType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agreement extends BaseModel
{
    use HasBranch;

    protected $table = 'hotel_agreements';

    protected $guarded = ['id'];

    protected $casts = [
        'type'           => AgreementType::class,
        'agreement_date' => 'date',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AgreementTemplate::class, 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate the next sequential agreement number.
     */
    public static function generateAgreementNumber(?int $branchId = null): string
    {
        $year  = now()->format('Y');
        $prefix = 'AGR' . $year . '/';

        $last = static::where('agreement_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = $last
            ? ((int) substr($last->agreement_number, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Replace all placeholders in the content with reservation data.
     */
    public static function renderContent(string $template, Reservation $reservation): string
    {
        $guest       = $reservation->primaryGuest;
        $restaurant  = $reservation->restaurant;
        $branch      = $reservation->branch;
        $address     = $branch?->address ?? $restaurant?->address ?? '';

        // Get pricing_type label directly from the reservation
        $pricingTypeLabel = 'Daily';
        if ($reservation->pricing_type) {
            $pricingTypeLabel = $reservation->pricing_type instanceof \Modules\Hotel\Enums\PricingType
                ? $reservation->pricing_type->label()
                : ucfirst($reservation->pricing_type ?? 'Daily');
        }

        $placeholders = [
            '{{tenant_name}}'             => ($guest?->first_name . ' ' . $guest?->last_name) ?? '',
            '{{owner_name}}'              => $restaurant?->restaurant_name ?? $restaurant?->name ?? '',
            '{{property_address}}'        => $address,
            '{{agreement_id}}'            => '{{agreement_id}}', // replaced after save
            '{{booking_id}}'              => $reservation->reservation_number ?? '',
            '{{agreement_date}}'          => now()->format('d M Y'),
            '{{check_in_date}}'           => $reservation->check_in_date?->format('d M Y') ?? '',
            '{{check_out_date}}'          => $reservation->check_out_date?->format('d M Y') ?? '',
            '{{agreement_amount}}'        => currency_format($reservation->total_amount ?? 0),
            '{{advance_amount}}'          => currency_format($reservation->advance_paid ?? 0),
            '{{pricing_type}}'            => $pricingTypeLabel,
            '{{special_requests}}'        => $reservation->special_requests ?? 'None',
            '{{tenant_signed_date}}'      => now()->format('d M Y'),
            '{{owner_signed_date}}'       => now()->format('d M Y'),
            '{{tenant_signature}}'        => '',
            '{{owner_signature}}'         => '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
}
