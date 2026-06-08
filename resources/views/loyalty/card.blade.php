<!DOCTYPE html>
<html>
<head>
    <title>My Loyalty Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(168, 85, 247, .12), transparent 34%),
                radial-gradient(circle at bottom right, rgba(251, 191, 36, .18), transparent 32%),
                linear-gradient(180deg, #fff7ed 0%, #f8f4ff 100%);
            min-height: 100vh;
        }

        .phone-shell {
            max-width: 390px;
            margin: 0 auto;
        }

        .loyalty-card {
            background:
                radial-gradient(circle at 4% 22%, rgba(168, 85, 247, .20) 0 48px, transparent 50px),
                radial-gradient(circle at 98% 82%, rgba(168, 85, 247, .15) 0 58px, transparent 60px),
                linear-gradient(135deg, #fff 0%, #fff7df 100%);
            border: 1px solid rgba(250, 204, 21, .9);
        }

        .stamp-box {
            width: 43px;
            height: 43px;
            border: 3px solid #b45309;
            border-radius: 10px;
            background: linear-gradient(135deg, #fff2a8, #fde68a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #92400e;
            box-shadow: inset 0 2px 5px rgba(255,255,255,.7), 0 3px 7px rgba(120,53,15,.16);
        }

        .stamp-filled {
            background: linear-gradient(135deg, #facc15, #f97316);
            color: white;
        }

        .coin {
            position: absolute;
            width: 27px;
            height: 27px;
            border-radius: 999px;
            background: radial-gradient(circle at 35% 30%, #fff7a8, #f59e0b);
            box-shadow: 0 5px 14px rgba(245, 158, 11, .35);
        }

        .qr-modal {
            display: none;
        }

        .qr-modal.active {
            display: flex;
        }

        @media (max-width: 360px) {
            .stamp-box {
                width: 38px;
                height: 38px;
            }
        }
    </style>
</head>

<body class="text-slate-900">
    <div class="phone-shell px-4 py-5 pb-8">

        <div class="rounded-[28px] bg-white/80 p-4 shadow-xl backdrop-blur border border-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-purple-600">
                        Customer Loyalty
                    </p>

                    <h1 class="mt-1 text-2xl font-black text-slate-900 leading-tight">
                        {{ $customer->name ?? 'Customer' }}
                    </h1>

                    <p class="text-sm font-medium text-slate-500">
                        {{ $customer->phone ?? '-' }}
                    </p>
                </div>

                <button
                    type="button"
                    onclick="openQrModal()"
                    class="flex items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-300 active:scale-95"
                    style="height:52px;width:52px;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4h6v6H3V4zm12 0h6v6h-6V4zM3 14h6v6H3v-6zm12 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4z" />
                    </svg>
                </button>
            </div>
        </div>

        @forelse($progress as $item)
            <div class="loyalty-card relative mt-5 overflow-hidden rounded-[30px] p-5 shadow-2xl">
                <span class="coin right-5 top-5"></span>
                <span class="coin bottom-6 right-8"></span>

                <div class="relative z-10">
                    <div class="mb-5 flex justify-center">
                        <div class="rounded-2xl border border-yellow-300 bg-white/85 px-4 py-2 shadow-sm">
                            <h2 class="text-base font-black text-yellow-600">
                                កាតសន្សំពិន្ទុ ចាប់រង្វាន់
                            </h2>
                        </div>
                    </div>

                    <div class="mb-4 flex items-center justify-between rounded-2xl bg-white/70 p-3 border border-yellow-100">
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400">Reward</p>
                            <p class="text-sm font-black text-slate-800">
                                {{ $item['reward'] }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-purple-600 px-3 py-2 text-right text-white shadow-md">
                            <p class="text-xl font-black leading-none">
                                {{ $item['current'] }}/{{ $item['required'] }}
                            </p>
                            <p class="mt-1 text-[10px] font-semibold text-purple-100">stamps</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-3">
                        @for($i = 1; $i <= $item['required']; $i++)
                            <div class="stamp-box {{ $i <= $item['current'] ? 'stamp-filled' : '' }}">
                                @if($i <= $item['current'])
                                    ✓
                                @endif
                            </div>
                        @endfor
                    </div>

                    @if($item['completed'])
                        <div class="mt-5 rounded-2xl border border-green-300 bg-green-100 p-3 text-center">
                            <p class="text-sm font-black text-green-700">🎉 Reward Available</p>
                            <p class="mt-1 text-xs text-green-600">Please show this card to cashier.</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl border border-purple-100 bg-white/85 p-3 text-center shadow-sm">
                            <p class="text-sm font-black text-purple-700">
                                Need {{ $item['remaining'] }} more stamp{{ $item['remaining'] > 1 ? 's' : '' }} to get reward.
                            </p>
                        </div>
                    @endif

                    <button
                        type="button"
                        onclick="openQrModal()"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-purple-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-purple-200 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h6v6H3V4zm12 0h6v6h-6V4zM3 14h6v6H3v-6zm12 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4z" />
                        </svg>
                        Show My QR Code
                    </button>
                </div>
            </div>
        @empty
            <div class="loyalty-card relative mt-5 overflow-hidden rounded-[30px] p-5 shadow-2xl">
                <span class="coin right-5 top-5"></span>
                <span class="coin bottom-6 right-8"></span>

                <div class="relative z-10">
                    <div class="mb-5 flex justify-center">
                        <div class="rounded-2xl border border-yellow-300 bg-white/85 px-4 py-2 shadow-sm">
                            <h2 class="text-base font-black text-yellow-600">
                                កាតសន្សំពិន្ទុ ចាប់រង្វាន់
                            </h2>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-3">
                        @for($i = 1; $i <= 10; $i++)
                            <div class="stamp-box"></div>
                        @endfor
                    </div>

                    <div class="mt-5 rounded-2xl border border-purple-100 bg-white/90 p-4 text-center shadow-sm">
                        <p class="text-base font-black text-purple-700">
                            No loyalty progress yet
                        </p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Buy item again and show your QR to cashier.
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="openQrModal()"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-purple-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-purple-200 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h6v6H3V4zm12 0h6v6h-6V4zM3 14h6v6H3v-6zm12 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4z" />
                        </svg>
                        Show My QR Code
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <div id="qrModal" class="qr-modal fixed inset-0 z-50 items-center justify-center bg-black/70 px-5">
        <div class="w-full max-w-sm rounded-[30px] bg-white p-5 text-center shadow-2xl">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h6v6H3V4zm12 0h6v6h-6V4zM3 14h6v6H3v-6zm12 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4z" />
                </svg>
            </div>

            <h3 class="text-xl font-black text-slate-900">My Loyalty QR</h3>
            <p class="mt-1 text-sm text-slate-500">
                Show this QR to cashier every time you buy.
            </p>

            <div class="mt-5 flex justify-center">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <img src="{{ $qrImage }}" alt="Customer Loyalty QR" class="h-56 w-56 object-contain">
                </div>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-3">
                <p class="text-sm font-black text-slate-800">
                    {{ $customer->name ?? 'Customer' }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ $customer->phone ?? '-' }}
                </p>
            </div>

            <button
                type="button"
                onclick="closeQrModal()"
                class="mt-5 w-full rounded-2xl bg-slate-900 py-3 font-bold text-white">
                Close
            </button>
        </div>
    </div>

    <script>
        function openQrModal() {
            document.getElementById('qrModal').classList.add('active');
        }

        function closeQrModal() {
            document.getElementById('qrModal').classList.remove('active');
        }

        document.getElementById('qrModal').addEventListener('click', function (event) {
            if (event.target.id === 'qrModal') {
                closeQrModal();
            }
        });
    </script>
</body>
</html>