<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationRoom extends BaseModel
{
    protected $table = 'hotel_quotation_rooms';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}

