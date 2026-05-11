<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Invite OTP – TripLedger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2">
            <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">T</span>
            </div>
            <span class="text-xl font-bold text-gray-900">TripLedger</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Verify your invite</h1>
            <p class="text-sm text-gray-500 mt-1">
                You've been invited to join <strong class="text-gray-700">{{ $trip->name }}</strong>.
                Enter the OTP sent to your email.
            </p>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('trips.otp.verify', $trip) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="the email you were invited with"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-300 @enderror">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">6-Digit OTP</label>
                <input type="text" name="otp" value="{{ old('otp') }}" required
                       maxlength="6" pattern="\d{6}" inputmode="numeric"
                       placeholder="e.g. 482910"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-center font-mono text-xl tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('otp') border-red-300 @enderror">
                <p class="text-xs text-gray-400 mt-1">Check your email inbox for the 6-digit code</p>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                Verify &amp; Join Trip
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-gray-100 text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-600 font-medium hover:underline">Sign in</a>
            to see all your trips automatically.
        </div>

        <div class="mt-3 text-center text-xs text-gray-400">
            OTP valid for 7 days &nbsp;·&nbsp; Contact the trip admin to resend
        </div>
    </div>
</div>

</body>
</html>
