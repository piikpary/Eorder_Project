<?php

namespace Modules\Hotel\Entities;

use App\Models\BaseModel;
use Modules\Hotel\Enums\AgreementType;

class AgreementTemplate extends BaseModel
{
    protected $table = 'hotel_agreement_templates';

    protected $guarded = ['id'];

    protected $casts = [
        'type'       => AgreementType::class,
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function agreements()
    {
        return $this->hasMany(Agreement::class, 'template_id');
    }

    /**
     * Get the default template for a given type.
     */
    public static function getDefault(AgreementType $type): ?self
    {
        return static::where('type', $type->value)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? static::where('type', $type->value)
                ->where('is_active', true)
                ->first();
    }
}
