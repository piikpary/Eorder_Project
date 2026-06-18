<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Hotel\Enums\AgreementType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(AgreementType::cases(), 'value'));
            $table->string('name');
            $table->longText('content');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
        });

        // Seed default templates
        $now = now();
        DB::table('hotel_agreement_templates')->insert([
            [
                'type'       => 'sale',
                'name'       => 'Default Sale Agreement',
                'content'    => $this->saleTemplate(),
                'is_default' => true,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type'       => 'lease',
                'name'       => 'Default Lease Agreement',
                'content'    => $this->leaseTemplate(),
                'is_default' => true,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type'       => 'rent',
                'name'       => 'Default Rent Agreement',
                'content'    => $this->rentTemplate(),
                'is_default' => true,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_agreement_templates');
    }

    private function saleTemplate(): string
    {
        return <<<'TPL'
Dear {{tenant_name}},

This letter serves as the official Sale Agreement between {{owner_name}} (the "Property Owner") and {{tenant_name}} (the "Tenant") for the property located at {{property_address}}.

Agreement Reference
Agreement No: {{agreement_id}}
Booking ID: {{booking_id}}
Agreement Date: {{agreement_date}}

Terms of Agreement

1. Sale Date: The sale transaction is agreed on {{agreement_date}}.
2. Purchase Price: The Tenant agrees to pay a total purchase price of {{agreement_amount}} for the property.
3. Advance / Down Payment: An advance payment of {{advance_amount}} is required and will be handled as per the agreed policy, subject to the terms of this agreement.
4. Property Condition: The property is being sold in its current condition, and the Tenant has inspected the property and accepts it as is.
5. Termination: Either party may terminate this agreement by providing 30 days written notice, subject to applicable laws.

The Parties hereby agree to the terms and conditions outlined in this Agreement and such agreement is demonstrated by their signatures below.

___________________________
{{tenant_name}}
Tenant
Date: {{tenant_signed_date}}

___________________________
{{owner_name}}
Property Owner
Date: {{owner_signed_date}}
TPL;
    }

    private function leaseTemplate(): string
    {
        return <<<'TPL'
Dear {{tenant_name}},

This letter serves as the official Lease Agreement between {{owner_name}} (the "Lessor") and {{tenant_name}} (the "Lessee") for the property located at {{property_address}}.

Agreement Reference
Agreement No: {{agreement_id}}
Booking ID: {{booking_id}}
Agreement Date: {{agreement_date}}
Lease Start: {{check_in_date}}
Lease End: {{check_out_date}}

Terms of Agreement

1. Rent Amount: The Lessee agrees to pay {{agreement_amount}} as per the agreed pricing rate.
2. Advance / Security Deposit: An advance deposit of {{advance_amount}} has been paid.
3. Utilities: All utility bills are the responsibility of the Lessee unless otherwise stated.
4. Property Condition: The Lessee shall maintain the property in good condition throughout the lease period.
5. Termination: Either party may terminate this agreement by providing 30 days written notice, subject to applicable laws.

The Parties hereby agree to the terms and conditions outlined in this Agreement.

___________________________
{{tenant_name}}
Lessee
Date: {{tenant_signed_date}}

___________________________
{{owner_name}}
Lessor
Date: {{owner_signed_date}}
TPL;
    }

    private function rentTemplate(): string
    {
        return <<<'TPL'
Dear {{tenant_name}},

This letter serves as the official Rent Agreement between {{owner_name}} (the "Landlord") and {{tenant_name}} (the "Tenant") for the property located at {{property_address}}.

Agreement Reference
Agreement No: {{agreement_id}}
Booking ID: {{booking_id}}
Agreement Date: {{agreement_date}}
Stay Period: {{check_in_date}} to {{check_out_date}}
Pricing Type: {{pricing_type}}

Terms of Agreement

1. Rent Amount: The Tenant agrees to pay {{agreement_amount}} as per the {{pricing_type}} rate.
2. Advance Paid: {{advance_amount}} has been paid in advance.
3. Check-in Date: {{check_in_date}}
4. Check-out Date: {{check_out_date}}
5. Special Requests: {{special_requests}}
6. Termination: Either party may terminate this agreement by providing 30 days written notice.

The Parties hereby agree to the terms and conditions outlined in this Agreement.

___________________________
{{tenant_name}}
Tenant
Date: {{tenant_signed_date}}

___________________________
{{owner_name}}
Landlord
Date: {{owner_signed_date}}
TPL;
    }
};
