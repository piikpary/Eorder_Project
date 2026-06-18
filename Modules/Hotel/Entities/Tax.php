<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tax extends BaseModel
{
    use HasBranch;

    protected $table = 'hotel_taxes';

    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'hotel_reservation_tax', 'tax_id', 'reservation_id')
            ->withTimestamps();
    }
}
