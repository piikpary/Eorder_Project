@php
    $loyaltyModuleEnabled =
        function_exists('module_enabled')
        && module_enabled('Loyalty')
        && function_exists('restaurant_modules')
        && in_array(
            'Loyalty',
            (array) restaurant_modules(),
            true
        );

    $loyaltySettings =
        $shopBranch?->loyalty_card_settings ?? [];

    if (is_string($loyaltySettings)) {
        $loyaltySettings =
            json_decode($loyaltySettings, true) ?: [];
    }

    if (!is_array($loyaltySettings)) {
        $loyaltySettings = [];
    }

    $cardEnabled =
        (bool) ($loyaltySettings['enabled'] ?? true);

    $cardTitle =
        $loyaltySettings['title']
        ?? $shopBranch?->name
        ?? $restaurant?->name
        ?? 'Loyalty Card';

    $cardSubtitle =
        $loyaltySettings['subtitle']
        ?? 'Loyalty Member';

    $primaryColor =
        $loyaltySettings['primary_color']
        ?? '#d4a017';

    $secondaryColor =
        $loyaltySettings['secondary_color']
        ?? '#9a6b00';

    $backgroundColor =
        $loyaltySettings['background_color']
        ?? '#fffaf0';

    $textColor =
        $loyaltySettings['text_color']
        ?? '#2f2100';

    $mutedTextColor =
        $loyaltySettings['muted_text_color']
        ?? '#786b55';

    $borderColor =
        $loyaltySettings['border_color']
        ?? $primaryColor;

    $buttonColor =
        $loyaltySettings['button_color']
        ?? $primaryColor;

    $buttonTextColor =
        $loyaltySettings['button_text_color']
        ?? '#ffffff';

    $showCustomerName =
        (bool) (
            $loyaltySettings['show_customer_name']
            ?? true
        );

    $showPhone =
        (bool) (
            $loyaltySettings['show_phone']
            ?? true
        );

    $showMemberType =
        (bool) (
            $loyaltySettings['show_member_type']
            ?? true
        );

    $showPoints =
        (bool) (
            $loyaltySettings['show_points']
            ?? true
        );

    $showPointsValue =
        (bool) (
            $loyaltySettings['show_points_value']
            ?? true
        );

    $loyaltyCustomer =
        $customer
        ?? (
            function_exists('customer')
                ? customer()
                : null
        );

    $loyaltyPoints =
        (int) ($availableLoyaltyPoints ?? 0);

    $pointsValue =
        (float) ($loyaltyPointsValue ?? 0);

    $memberType =
        data_get($loyaltyCustomer, 'memberType.name')
        ?? data_get($loyaltyCustomer, 'member_type.name')
        ?? data_get($loyaltyCustomer, 'member_type')
        ?? $cardSubtitle;
@endphp

@if ($loyaltyModuleEnabled && $cardEnabled)
    <section class="px-4 mt-4">
        <div
            class="overflow-hidden rounded-2xl p-4 shadow-sm"
            style="
                background:
                    linear-gradient(
                        135deg,
                        {{ $backgroundColor }} 0%,
                        {{ $primaryColor }}22 100%
                    );
                border: 2px solid {{ $borderColor }};
                color: {{ $textColor }};
            "
        >
            @if ($loyaltyCustomer)
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold uppercase tracking-wider"
                            style="color: {{ $primaryColor }};"
                        >
                            {{ $cardSubtitle }}
                        </p>

                        <h3
                            class="mt-1 truncate text-lg font-bold"
                        >
                            {{ $cardTitle }}
                        </h3>

                        @if ($showCustomerName)
                            <p class="mt-3 text-sm font-semibold">
                                {{
                                    $loyaltyCustomer->name
                                    ?? 'Customer'
                                }}
                            </p>
                        @endif

                        @if (
                            $showPhone
                            && !empty($loyaltyCustomer->phone)
                        )
                            <p
                                class="mt-0.5 text-xs"
                                style="
                                    color:
                                        {{ $mutedTextColor }};
                                "
                            >
                                {{ $loyaltyCustomer->phone }}
                            </p>
                        @endif

                        @if ($showMemberType)
                            <p
                                class="mt-2 text-xs font-semibold"
                                style="
                                    color:
                                        {{ $primaryColor }};
                                "
                            >
                                {{ $memberType }}
                            </p>
                        @endif
                    </div>

                    @if ($showPoints)
                        <div
                            class="shrink-0 rounded-xl px-4 py-2 text-right"
                            style="
                                background:
                                    linear-gradient(
                                        135deg,
                                        {{ $buttonColor }},
                                        {{ $secondaryColor }}
                                    );
                                color:
                                    {{ $buttonTextColor }};
                            "
                        >
                            <p class="text-xs opacity-80">
                                Points
                            </p>

                            <p class="text-lg font-bold">
                                {{ number_format($loyaltyPoints) }} P
                            </p>
                        </div>
                    @endif
                </div>

                @if ($showPointsValue)
                    <div
                        class="mt-4 rounded-xl px-4 py-3 text-sm"
                        style="
                            background-color:
                                {{ $primaryColor }}12;
                            border:
                                1px solid
                                {{ $borderColor }}66;
                        "
                    >
                        <div
                            class="flex items-center justify-between gap-3"
                        >
                            <span
                                style="
                                    color:
                                        {{ $mutedTextColor }};
                                "
                            >
                                Points value
                            </span>

                            <strong>
                                {{
                                    currency_format(
                                        $pointsValue,
                                        $restaurant->currency_id
                                    )
                                }}
                            </strong>
                        </div>
                    </div>
                @endif

                @if ($loyaltyPoints > 0)
                    <button
                        type="button"
                        wire:click="$set(
                            'showLoyaltyRedemptionModal',
                            true
                        )"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold"
                        style="
                            background:
                                linear-gradient(
                                    135deg,
                                    {{ $buttonColor }},
                                    {{ $secondaryColor }}
                                );
                            color:
                                {{ $buttonTextColor }};
                        "
                    >
                        Redeem loyalty points
                    </button>
                @endif
            @else
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider"
                            style="color: {{ $primaryColor }};"
                        >
                            {{ $cardSubtitle }}
                        </p>

                        <h3 class="mt-1 text-lg font-bold">
                            {{ $cardTitle }}
                        </h3>

                        <p
                            class="mt-2 text-sm"
                            style="
                                color:
                                    {{ $mutedTextColor }};
                            "
                        >
                            Login to view your points and rewards.
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-bold"
                        style="
                            background:
                                {{ $primaryColor }};
                            color:
                                {{ $buttonTextColor }};
                        "
                    >
                        P
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="$dispatch('showSignup')"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold"
                    style="
                        background:
                            linear-gradient(
                                135deg,
                                {{ $buttonColor }},
                                {{ $secondaryColor }}
                            );
                        color:
                            {{ $buttonTextColor }};
                    "
                >
                    Login to view loyalty points
                </button>
            @endif
        </div>
    </section>
@endif
