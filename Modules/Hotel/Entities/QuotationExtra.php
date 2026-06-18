<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationExtra extends BaseModel
{
    protected $table = 'hotel_quotation_extras';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function extraService(): BelongsTo
    {
        return $this->belongsTo(ExtraService::class, 'extra_service_id');
    }
}

