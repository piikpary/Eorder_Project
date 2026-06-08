<!DOCTYPE html>
<html>
<head>
    <title>Customer Loyalty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-3xl bg-slate-900 border border-slate-700 p-6 shadow-2xl">
        <h1 class="text-2xl font-bold text-center">My Loyalty Card</h1>

        <p class="text-sm text-slate-400 text-center mt-2">
            Enter your phone number to see your points and rewards.
        </p>

        @if($errors->any())
            <div class="mt-4 rounded-xl bg-red-500/10 border border-red-500/30 p-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        @if(!$restaurantId)
            <div class="mt-4 rounded-xl bg-yellow-500/10 border border-yellow-500/30 p-3 text-sm text-yellow-300">
                Missing restaurant link. Please open loyalty from the restaurant customer site.
            </div>
        @endif

        <form method="POST" action="{{ route('loyalty.find') }}" class="mt-6 space-y-4">
            @csrf

            <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}">

            <div>
                <label class="text-sm text-slate-300">Phone Number</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                    class="mt-2 w-full rounded-xl bg-white border border-slate-300 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-purple-500 focus:ring-purple-500"
                    placeholder="Example: 012345678"
                    required>
            </div>

            <button class="w-full rounded-xl bg-purple-600 py-3 font-bold text-white hover:bg-purple-700">
                View My Loyalty Card
            </button>
        </form>
    </div>
</body>
</html>