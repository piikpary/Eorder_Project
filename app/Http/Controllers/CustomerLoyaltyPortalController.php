<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class CustomerLoyaltyPortalController extends Controller
{
    public function login(Request $request)
    {
        return view('loyalty.login', [
            'restaurantId' => $request->query('restaurant_id'),
        ]);
    }

    public function find(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'restaurant_id' => ['nullable', 'integer'],
        ]);

        $phone = $this->normalizePhone($request->phone);
        $restaurantId = $request->restaurant_id;

        $customer = $this->findCustomerByPhone($phone, $restaurantId);

        if (!$customer) {
            return redirect()
                ->route('loyalty.register', [
                    'phone' => $phone,
                    'restaurant_id' => $restaurantId,
                ])
                ->with('info', 'Please register your phone number first.');
        }

        $this->attachRestaurantIfMissing($customer, $restaurantId);

        if (empty($customer->loyalty_token)) {
            $token = Str::random(48);

            DB::table('customers')
                ->where('id', $customer->id)
                ->update([
                    'loyalty_token' => $token,
                    'updated_at' => now(),
                ]);

            $customer->loyalty_token = $token;
        }

        return redirect()->route('loyalty.card', $customer->loyalty_token);
    }

    public function register(Request $request)
    {
        return view('loyalty.register', [
            'phone' => $request->query('phone'),
            'restaurantId' => $request->query('restaurant_id'),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'restaurant_id' => ['nullable', 'integer'],
        ];

        if (Schema::hasColumn('customers', 'restaurant_id')) {
            $rules['restaurant_id'] = ['required', 'integer'];
        }

        $request->validate($rules);

        $phone = $this->normalizePhone($request->phone);
        $restaurantId = $request->restaurant_id;

        $existingCustomer = $this->findCustomerByPhone($phone, $restaurantId);

        if ($existingCustomer) {
            $this->attachRestaurantIfMissing($existingCustomer, $restaurantId);

            if (empty($existingCustomer->loyalty_token)) {
                $token = Str::random(48);

                DB::table('customers')
                    ->where('id', $existingCustomer->id)
                    ->update([
                        'loyalty_token' => $token,
                        'updated_at' => now(),
                    ]);

                return redirect()->route('loyalty.card', $token);
            }

            return redirect()->route('loyalty.card', $existingCustomer->loyalty_token);
        }

        $token = Str::random(48);

        $data = [
            'name' => trim($request->name),
            'phone' => $phone,
            'loyalty_token' => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('customers', 'restaurant_id')) {
            $data['restaurant_id'] = $restaurantId;
        }

        if (Schema::hasColumn('customers', 'email')) {
            $data['email'] = null;
        }

        DB::table('customers')->insert($data);

        return redirect()->route('loyalty.card', $token);
    }

    public function card(string $token)
    {
        $customer = DB::table('customers')
            ->where('loyalty_token', $token)
            ->firstOrFail();

        $progress = $this->getLoyaltyProgress($customer->id);
        $qrUrl = route('loyalty.scan', $customer->loyalty_token);

        $result = (new Builder(
            writer: new PngWriter(),
            data: $qrUrl,
            size: 220,
            margin: 10
        ))->build();

        return view('loyalty.card', [
            'customer' => $customer,
            'progress' => $progress,
            'qrUrl' => $qrUrl,
            'qrImage' => $result->getDataUri(),
        ]);
    }

    public function scan(string $token)
    {
        $customer = DB::table('customers')
            ->where('loyalty_token', $token)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name ?? $customer->customer_name ?? '-',
                'phone' => $customer->phone ?? '-',
                'loyalty_token' => $customer->loyalty_token,
            ],
        ]);
    }

    private function getLoyaltyProgress(int $customerId): array
    {
        $stampRuleTable = $this->findTable([
            'loyalty_stamp_rules',
            'stamp_rules',
        ]);

        $customerStampTable = $this->findTable([
            'loyalty_customer_stamps',
            'customer_loyalty_stamps',
            'loyalty_stamps',
            'customer_stamps',
        ]);

        if (!$stampRuleTable || !$customerStampTable) {
            return [];
        }

        $rules = DB::table($stampRuleTable)->get();
        $items = [];

        foreach ($rules as $rule) {
            $ruleId = $rule->id;
            $required = (int) ($rule->stamps_required ?? $rule->required_stamps ?? 10);

            $customerStamp = DB::table($customerStampTable)
                ->where('customer_id', $customerId)
                ->where(function ($query) use ($ruleId) {
                    $query->where('stamp_rule_id', $ruleId)
                        ->orWhere('loyalty_stamp_rule_id', $ruleId);
                })
                ->first();

            $current = (int) (
                $customerStamp->stamps
                ?? $customerStamp->stamp_count
                ?? $customerStamp->current_stamps
                ?? $customerStamp->total_stamps
                ?? 0
            );

            $items[] = [
                'name' => $rule->name ?? $rule->title ?? $rule->menu_item_name ?? 'Loyalty Reward',
                'current' => $current,
                'required' => $required,
                'remaining' => max(0, $required - $current),
                'reward' => trim(($rule->reward_type ?? 'Reward') . (!empty($rule->reward_value) ? ' - ' . $rule->reward_value : '')),
                'completed' => $current >= $required,
            ];
        }

        return $items;
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    private function findCustomerByPhone(string $phone, ?int $restaurantId = null): ?object
    {
        $phoneWithoutZero = ltrim($phone, '0');
        $phoneWithZero = '0' . $phoneWithoutZero;
        $phoneWith855 = '855' . $phoneWithoutZero;

        $query = DB::table('customers')
            ->where(function ($query) use ($phone, $phoneWithZero, $phoneWith855) {
                $query->whereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') = ?", [$phone])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') = ?", [$phoneWithZero])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') = ?", [$phoneWith855]);
            });

        if ($restaurantId && Schema::hasColumn('customers', 'restaurant_id')) {
            $query->where(function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId)
                    ->orWhereNull('restaurant_id');
            });
        }

        return $query->first();
    }

    private function attachRestaurantIfMissing(object $customer, ?int $restaurantId): void
    {
        if (!$restaurantId || !Schema::hasColumn('customers', 'restaurant_id')) {
            return;
        }

        if (!empty($customer->restaurant_id)) {
            return;
        }

        DB::table('customers')
            ->where('id', $customer->id)
            ->update([
                'restaurant_id' => $restaurantId,
                'updated_at' => now(),
            ]);

        $customer->restaurant_id = $restaurantId;
    }

    private function findTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }
}