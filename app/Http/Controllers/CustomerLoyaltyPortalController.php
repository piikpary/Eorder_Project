<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerLoyaltyPortalController extends Controller
{
    public function login(Request $request)
    {
        $this->resolveRestaurantFromDomain($request);

        return view('loyalty.login');
    }

    public function find(Request $request)
    {
        $restaurant = $this->resolveRestaurantFromDomain($request);

        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $phone = $this->normalizePhone($request->phone);
        $restaurantId = (int) $restaurant->id;

        $customer = $this->findCustomerByPhone(
            $phone,
            $restaurantId
        );

        if (!$customer) {
            return redirect()
                ->to(
                    '/loyalty/register?phone=' .
                    urlencode($phone)
                )
                ->with(
                    'info',
                    'Please register your phone number first.'
                );
        }

        if (empty($customer->loyalty_token)) {
            $token = Str::random(48);

            DB::table('customers')
                ->where('id', $customer->id)
                ->where(
                    'restaurant_id',
                    $restaurantId
                )
                ->update([
                    'loyalty_token' => $token,
                    'updated_at' => now(),
                ]);

            $customer->loyalty_token = $token;
        }

        $this->rememberLoyaltyCustomer(
            $restaurantId,
            $customer->loyalty_token
        );

        return redirect()->to(
            '/loyalty/card/' .
            $customer->loyalty_token
        );
    }

    public function register(Request $request)
    {
        $this->resolveRestaurantFromDomain($request);

        return view('loyalty.register', [
            'phone' => $request->query('phone'),
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = $this->resolveRestaurantFromDomain(
            $request
        );

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $restaurantId = (int) $restaurant->id;
        $phone = $this->normalizePhone(
            $request->phone
        );

        $existingCustomer = $this->findCustomerByPhone(
            $phone,
            $restaurantId
        );

        if ($existingCustomer) {
            if (empty($existingCustomer->loyalty_token)) {
                $token = Str::random(48);

                DB::table('customers')
                    ->where(
                        'id',
                        $existingCustomer->id
                    )
                    ->where(
                        'restaurant_id',
                        $restaurantId
                    )
                    ->update([
                        'loyalty_token' => $token,
                        'updated_at' => now(),
                    ]);

                $this->rememberLoyaltyCustomer(
                    $restaurantId,
                    $token
                );

                return redirect()->to(
                    '/loyalty/card/' . $token
                );
            }

            $this->rememberLoyaltyCustomer(
                $restaurantId,
                $existingCustomer->loyalty_token
            );

            return redirect()->to(
                '/loyalty/card/' .
                $existingCustomer->loyalty_token
            );
        }

        $token = Str::random(48);

        $data = [
            'restaurant_id' => $restaurantId,
            'name' => trim($request->name),
            'phone' => $phone,
            'loyalty_token' => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (
            Schema::hasColumn(
                'customers',
                'email'
            )
        ) {
            $data['email'] = null;
        }

        DB::table('customers')->insert($data);

        $this->rememberLoyaltyCustomer(
            $restaurantId,
            $token
        );

        return redirect()->to(
            '/loyalty/card/' . $token
        );
    }

    public function card(
        Request $request,
        string $token
    ) {
        $restaurant = $this->resolveRestaurantFromDomain(
            $request
        );

        $customer = DB::table('customers')
            ->where(
                'restaurant_id',
                $restaurant->id
            )
            ->where(
                'loyalty_token',
                $token
            )
            ->firstOrFail();

        $this->rememberLoyaltyCustomer(
            (int) $restaurant->id,
            $customer->loyalty_token
        );

        $progress = $this->getLoyaltyProgress(
            $customer
        );

        $qrUrl = $request->getSchemeAndHttpHost()
            . '/loyalty/scan/'
            . $customer->loyalty_token;

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrUrl)
            ->size(220)
            ->margin(10)
            ->build();

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
            ->where(
                'loyalty_token',
                $token
            )
            ->first();

        if (!$customer) {
            return response()->view(
                'loyalty.scan-result',
                [
                    'valid' => false,
                    'message' =>
                        'Invalid loyalty QR code. Customer not found.',
                    'customer' => null,
                    'restaurant' => null,
                    'restaurantName' => '-',
                    'progress' => [],
                ],
                404
            );
        }

        $restaurant = null;
        $restaurantName = '-';

        if (
            !empty($customer->restaurant_id)
            && Schema::hasTable('restaurants')
        ) {
            $restaurant = DB::table(
                'restaurants'
            )
                ->where(
                    'id',
                    $customer->restaurant_id
                )
                ->first();

            $restaurantName =
                $restaurant->name ?? '-';
        }

        $progress = $this->getLoyaltyProgress(
            $customer
        );

        return view('loyalty.scan-result', [
            'valid' => true,
            'message' =>
                'Verified customer loyalty card.',
            'customer' => $customer,
            'restaurant' => $restaurant,
            'restaurantName' => $restaurantName,
            'progress' => $progress,
        ]);
    }

    private function resolveRestaurantFromDomain(
        Request $request
    ): Restaurant {
        $host = strtolower(
            str_replace(
                'www.',
                '',
                $request->getHost()
            )
        );

        $restaurant = Restaurant::where(
            'sub_domain',
            $host
        )->first();

        if (!$restaurant) {
            abort(
                404,
                'Store not found for this domain.'
            );
        }

        session([
            'restaurant' => $restaurant,
        ]);

        return $restaurant;
    }

    private function getLoyaltyProgress(
        object $customer
    ): array {
        if (
            !Schema::hasTable(
                'loyalty_stamp_rules'
            )
            || !Schema::hasTable(
                'customer_stamps'
            )
        ) {
            return [];
        }

        $customerId = (int) $customer->id;

        $restaurantId =
            $customer->restaurant_id ?? null;

        $rulesQuery = DB::table(
            'loyalty_stamp_rules'
        )->where(
            'is_active',
            1
        );

        if (
            $restaurantId
            && Schema::hasColumn(
                'loyalty_stamp_rules',
                'restaurant_id'
            )
        ) {
            $rulesQuery->where(
                'restaurant_id',
                $restaurantId
            );
        }

        $rules = $rulesQuery->get();

        $items = [];

        foreach ($rules as $rule) {
            $ruleId = (int) $rule->id;

            $customerStampQuery = DB::table(
                'customer_stamps'
            )
                ->where(
                    'customer_id',
                    $customerId
                )
                ->where(
                    'stamp_rule_id',
                    $ruleId
                );

            if (
                $restaurantId
                && Schema::hasColumn(
                    'customer_stamps',
                    'restaurant_id'
                )
            ) {
                $customerStampQuery->where(
                    'restaurant_id',
                    $restaurantId
                );
            }

            $customerStamp =
                $customerStampQuery->first();

            $earned = (int) (
                $customerStamp->stamps_earned ?? 0
            );

            $redeemed = (int) (
                $customerStamp->stamps_redeemed ?? 0
            );

            $current = max(
                0,
                $earned - $redeemed
            );

            $required = max(
                1,
                (int) (
                    $rule->stamps_required ?? 10
                )
            );

            $rewardName = 'Reward';

            if (!empty($rule->reward_type)) {
                $rewardName = str_replace(
                    '_',
                    ' ',
                    ucfirst($rule->reward_type)
                );
            }

            if (!empty($rule->reward_value)) {
                $rewardName .=
                    ' - ' .
                    $rule->reward_value;
            }

            $items[] = [
                'name' =>
                    $rule->name
                    ?? $rule->title
                    ?? 'Loyalty Reward',

                'current' => $current,
                'required' => $required,

                'remaining' => max(
                    0,
                    $required - $current
                ),

                'reward' => $rewardName,

                'completed' =>
                    $current >= $required,
            ];
        }

        return $items;
    }

    private function normalizePhone(
        ?string $phone
    ): string {
        return preg_replace(
            '/\D+/',
            '',
            (string) $phone
        );
    }

    private function findCustomerByPhone(
        string $phone,
        ?int $restaurantId = null
    ): ?object {
        $phoneWithoutZero = ltrim(
            $phone,
            '0'
        );

        $phoneWithZero =
            '0' . $phoneWithoutZero;

        $phoneWith855 =
            '855' . $phoneWithoutZero;

        $query = DB::table('customers')
            ->where(function ($query) use (
                $phone,
                $phoneWithZero,
                $phoneWith855
            ) {
                $cleanPhoneSql =
                    "REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '')";

                $query
                    ->whereRaw(
                        "{$cleanPhoneSql} = ?",
                        [$phone]
                    )
                    ->orWhereRaw(
                        "{$cleanPhoneSql} = ?",
                        [$phoneWithZero]
                    )
                    ->orWhereRaw(
                        "{$cleanPhoneSql} = ?",
                        [$phoneWith855]
                    );
            });

        if (
            $restaurantId
            && Schema::hasColumn(
                'customers',
                'restaurant_id'
            )
        ) {
            $query->where(
                'restaurant_id',
                $restaurantId
            );
        }

        return $query->first();
    }

    private function attachRestaurantIfMissing(
        object $customer,
        ?int $restaurantId
    ): void {
        if (
            !$restaurantId
            || !Schema::hasColumn(
                'customers',
                'restaurant_id'
            )
        ) {
            return;
        }

        if (!empty($customer->restaurant_id)) {
            return;
        }

        DB::table('customers')
            ->where(
                'id',
                $customer->id
            )
            ->update([
                'restaurant_id' => $restaurantId,
                'updated_at' => now(),
            ]);

        $customer->restaurant_id =
            $restaurantId;
    }

    private function findTable(
        array $tables
    ): ?string {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function rememberLoyaltyCustomer(
        int $restaurantId,
        string $token
    ): void {
        session([
            'loyalty_customer_token_' .
            $restaurantId => $token,
        ]);
    }
}