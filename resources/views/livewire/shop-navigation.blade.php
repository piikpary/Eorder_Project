<header class="lg:hidden">
    <nav class="bg-white border-gray-200 px-4 py-2.5 dark:bg-gray-800">
        <div class="flex flex-wrap justify-between items-center gap-2 mx-auto">
            <a
                href="{{ route('shop_restaurant', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                class="flex min-w-0 flex-1 items-center app-logo"
            >
                <x-restaurant-logo
                    :restaurant="$restaurant"
                    class="h-6 flex-shrink-0 ltr:mr-2 rtl:ml-2 sm:h-9"
                />

                @if ($restaurant->show_logo_text)
                    <span class="self-center truncate text-base font-semibold dark:text-white sm:text-xl">
                        {{ $restaurant->name }}
                    </span>
                @endif
            </a>

            <div class="flex flex-shrink-0 items-center gap-1 sm:gap-2">
                @if (languages()->count() > 1)
                    @livewire('shop.languageSwitcher')
                @endif

                @if ($showLoyaltyButton)
                    <a
                        href="{{ $loyaltyUrl }}"
                        title="My Loyalty"
                        aria-label="My Loyalty"
                        class="inline-flex h-10 flex-shrink-0 items-center justify-center
                               gap-1 rounded-lg border border-purple-200 bg-purple-50
                               px-2 text-purple-700 transition hover:bg-purple-100
                               dark:border-purple-700 dark:bg-purple-900/30
                               dark:text-purple-300 dark:hover:bg-purple-900/50"
                    >
                        <svg
                            class="h-5 w-5 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M20 12v10H4V12m16 0H4m16 0h1V7h-5.5
                                   M4 12H3V7h5.5M12 22V7m0 0H8.5
                                   A2.5 2.5 0 1 1 11 4.5L12 7Zm0 0h3.5
                                   A2.5 2.5 0 1 0 13 4.5L12 7Z"
                            />
                        </svg>

                        @if ($loyaltyIdentified && $loyaltyRequired > 0)
                            <span class="whitespace-nowrap text-xs font-extrabold">
                                {{ $loyaltyCurrent }}/{{ $loyaltyRequired }}
                            </span>
                        @else
                            <span class="hidden whitespace-nowrap text-xs font-bold sm:inline">
                                Loyalty
                            </span>
                        @endif
                    </a>
                @endif

                <button
                    id="theme-toggle-mobile"
                    data-tooltip-target="tooltip-toggle-mobile"
                    type="button"
                    class="text-gray-500 dark:text-gray-400 hover:bg-gray-100
                           dark:hover:bg-gray-700 focus:outline-none focus:ring-4
                           focus:ring-gray-200 dark:focus:ring-gray-700
                           rounded-lg text-sm p-2.5"
                >
                    <svg
                        id="theme-toggle-dark-icon-mobile"
                        class="hidden w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M17.293 13.293A8 8 0 016.707 2.707a8.001
                               8.001 0 1010.586 10.586z"
                        ></path>
                    </svg>

                    <svg
                        id="theme-toggle-light-icon-mobile"
                        class="hidden w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0
                               011-1zm4 8a4 4 0 11-8 0 4 4 0 018
                               0zm-.464 4.95l.707.707a1 1 0
                               001.414-1.414l-.707-.707a1 1 0
                               00-1.414 1.414zm2.12-10.607a1 1 0
                               010 1.414l-.706.707a1 1 0
                               11-1.414-1.414l.707-.707a1 1 0
                               011.414 0zM17 11a1 1 0 100-2h-1a1 1
                               0 100 2h1zm-7 4a1 1 0 011 1v1a1 1
                               0 11-2 0v-1a1 1 0 011-1zM5.05
                               6.464A1 1 0 106.465 5.05l-.708-.707a1
                               1 0 00-1.414 1.414l.707.707zm1.414
                               8.486l-.707.707a1 1 0
                               01-1.414-1.414l.707-.707a1 1 0
                               011.414 1.414zM4 11a1 1 0
                               100-2H3a1 1 0 000 2h1z"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                </button>

                <div
                    id="tooltip-toggle-mobile"
                    role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2
                           text-sm font-medium text-white transition-opacity
                           duration-300 bg-gray-900 rounded-lg shadow-sm
                           opacity-0 tooltip"
                >
                    @lang('app.toggleDarkMode')

                    <div
                        class="tooltip-arrow"
                        data-popper-arrow
                    ></div>
                </div>

                @if (
                    $restaurant->show_wifi_icon
                    && $restaurant->wifi_name
                    && $restaurant->wifi_password
                )
                    @livewire('forms.wifi-button', [
                        'restaurant' => $restaurant,
                    ])
                @endif

                <script>
                    document.addEventListener(
                        'DOMContentLoaded',
                        function () {
                            const themeToggleDarkIconMobile =
                                document.getElementById(
                                    'theme-toggle-dark-icon-mobile'
                                );

                            const themeToggleLightIconMobile =
                                document.getElementById(
                                    'theme-toggle-light-icon-mobile'
                                );

                            const themeToggleBtnMobile =
                                document.getElementById(
                                    'theme-toggle-mobile'
                                );

                            if (
                                themeToggleDarkIconMobile
                                && themeToggleLightIconMobile
                                && themeToggleBtnMobile
                            ) {
                                if (
                                    localStorage.getItem('color-theme') === 'dark'
                                    || (
                                        !('color-theme' in localStorage)
                                        && window.matchMedia(
                                            '(prefers-color-scheme: dark)'
                                        ).matches
                                    )
                                ) {
                                    themeToggleLightIconMobile.classList.remove(
                                        'hidden'
                                    );
                                } else {
                                    themeToggleDarkIconMobile.classList.remove(
                                        'hidden'
                                    );
                                }

                                themeToggleBtnMobile.addEventListener(
                                    'click',
                                    function () {
                                        themeToggleDarkIconMobile.classList.toggle(
                                            'hidden'
                                        );

                                        themeToggleLightIconMobile.classList.toggle(
                                            'hidden'
                                        );

                                        if (
                                            localStorage.getItem(
                                                'color-theme'
                                            )
                                        ) {
                                            if (
                                                localStorage.getItem(
                                                    'color-theme'
                                                ) === 'light'
                                            ) {
                                                document.documentElement.classList.add(
                                                    'dark'
                                                );

                                                localStorage.setItem(
                                                    'color-theme',
                                                    'dark'
                                                );
                                            } else {
                                                document.documentElement.classList.remove(
                                                    'dark'
                                                );

                                                localStorage.setItem(
                                                    'color-theme',
                                                    'light'
                                                );
                                            }
                                        } else {
                                            if (
                                                document.documentElement.classList.contains(
                                                    'dark'
                                                )
                                            ) {
                                                document.documentElement.classList.remove(
                                                    'dark'
                                                );

                                                localStorage.setItem(
                                                    'color-theme',
                                                    'light'
                                                );
                                            } else {
                                                document.documentElement.classList.add(
                                                    'dark'
                                                );

                                                localStorage.setItem(
                                                    'color-theme',
                                                    'dark'
                                                );
                                            }
                                        }

                                        document.dispatchEvent(
                                            new Event('dark-mode')
                                        );
                                    }
                                );
                            }
                        }
                    );
                </script>

                <button
                    data-collapse-toggle="mobile-menu-2"
                    type="button"
                    class="inline-flex items-center p-2 ms-1 text-sm
                           text-gray-500 rounded-lg hover:bg-gray-100
                           focus:outline-none focus:ring-2 focus:ring-gray-200
                           dark:text-gray-400 dark:hover:bg-gray-700
                           dark:focus:ring-gray-600"
                    aria-controls="mobile-menu-2"
                    aria-expanded="false"
                >
                    <span class="sr-only">
                        @lang('menu.openMainMenu')
                    </span>

                    <svg
                        class="w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0
                               110 2H4a1 1 0 01-1-1zM3 10a1
                               1 0 011-1h12a1 1 0 110 2H4a1
                               1 0 01-1-1zM3 15a1 1 0
                               011-1h12a1 1 0 110 2H4a1 1
                               0 01-1-1z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>

                    <svg
                        class="hidden w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0
                               011.414 0L10 8.586l4.293-4.293a1
                               1 0 111.414 1.414L11.414
                               10l4.293 4.293a1 1 0
                               01-1.414 1.414L10 11.414l-4.293
                               4.293a1 1 0
                               01-1.414-1.414L8.586 10
                               4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                </button>
            </div>

            <div
                class="hidden justify-between items-center w-full
                       bg-gray-50 mt-4 rounded-md dark:bg-gray-800"
                id="mobile-menu-2"
            >
                <div
                    class="p-4 border-b border-gray-100
                           dark:border-gray-700"
                >
                    @livewire('forms.shopSelectBranchMobile', [
                        'restaurant' => $restaurant,
                        'shopBranch' => $shopBranch,
                    ])
                </div>

                <ul class="flex flex-col font-medium">
                    @if ($restaurant->allow_customer_orders)
                        <li>
                            <a
                                href="{{ route('shop_restaurant', [
                                    'hash' => $restaurant->hash,
                                    'new_order' => 1,
                                ]) }}"
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       rounded dark:text-white
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:bg-gray-700"
                            >
                                @lang('menu.newOrder')
                            </a>
                        </li>
                    @endif

                    @if (in_array('Table Reservation', $modules))
                        <li>
                            <a
                                href="{{ route('book_a_table', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       rounded dark:text-gray-400
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:bg-gray-800"
                            >
                                @lang('menu.bookTable')
                            </a>
                        </li>
                    @endif

                    @if (!is_null(customer()))
                        <li>
                            <a
                                href="{{ route('my_addresses', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                                wire:navigate
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       border-b border-gray-100
                                       hover:bg-gray-50 dark:text-gray-400
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:border-gray-700"
                            >
                                @lang('menu.myAddresses')
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('my_orders', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                                wire:navigate
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       border-b border-gray-100
                                       hover:bg-gray-50 dark:text-gray-400
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:border-gray-700"
                            >
                                @lang('menu.myOrders')
                            </a>
                        </li>

                        @if (in_array('Table Reservation', $modules))
                            <li>
                                <a
                                    href="{{ route('my_bookings', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                                    wire:navigate
                                    class="block py-2 pr-4 pl-3 text-gray-700
                                           border-b border-gray-100
                                           hover:bg-gray-50 dark:text-gray-400
                                           dark:hover:bg-gray-700
                                           dark:hover:text-white
                                           dark:border-gray-700"
                                >
                                    @lang('menu.myBookings')
                                </a>
                            </li>
                        @endif

                        <li>
                            <a
                                href="{{ route('profile', [$restaurant->hash]) . '?branch=' . $shopBranch->id }}"
                                wire:navigate
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       border-b border-gray-100
                                       hover:bg-gray-50 dark:text-gray-400
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:border-gray-700"
                            >
                                @lang('menu.profile')
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ url('customer-logout') . '?restaurant=' . $restaurant->hash }}"
                                class="block py-2 pr-4 pl-3 text-gray-700
                                       rounded dark:text-white
                                       dark:hover:bg-gray-700
                                       dark:hover:text-white
                                       dark:bg-gray-700"
                            >
                                @lang('app.logout')
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</header>