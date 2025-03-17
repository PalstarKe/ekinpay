<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi Hotspot Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 to-indigo-800 text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <!-- Steps & Customer Care Card -->
        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-5 shadow-lg text-center">
            <h2 class="text-lg font-semibold text-cyan-300 mb-3">How to Connect</h2>
            <ol class="list-decimal pl-5 space-y-2 text-left">
                <li>Choose a package</li>
                <li>Make payment via M-Pesa</li>
                <li>Verify payment and connect</li>
            </ol>
            <p class="mt-4 text-sm">For assistance, call: <span class="font-semibold">+254 700 123 456</span></p>
        </div>

        <!-- Packages Card -->
        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-5 shadow-lg text-center">
            <h2 class="text-lg font-semibold text-cyan-300 mb-3">Choose Package</h2>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($packages as $package)
                    <button class="bg-cyan-700 p-3 rounded-lg text-center shadow-md">
                        <span class="block font-semibold">{{ $package->name_plan }}</span>
                        <span class="text-sm">Ksh {{ $package->price }}</span><br>
                        <span class="text-xs">{{ $package->validity }} {{ $package->validity_unit }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Verify & Redeem Card -->
        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-5 shadow-lg text-center">
            <h2 class="text-lg font-semibold text-cyan-300 mb-3">Verify Payment</h2>
            <div class="flex space-x-2">
                <input type="text" class="w-full p-2 bg-transparent border border-cyan-400 rounded-lg text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-300" placeholder="Enter Mpesa Code">
                <button class="bg-cyan-600 px-4 py-2 rounded-lg">Verify</button>
            </div>
            <h2 class="text-lg font-semibold text-cyan-300 mt-4 mb-3">Redeem Voucher</h2>
            <div class="flex space-x-2">
                <input type="text" class="w-full p-2 bg-transparent border border-cyan-400 rounded-lg text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-300" placeholder="Enter Voucher Code">
                <button class="bg-cyan-600 px-4 py-2 rounded-lg">Redeem</button>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-5 shadow-lg text-center">
            <h2 class="text-lg font-semibold text-cyan-300 mb-3">Login Credentials</h2>
            <div class="space-y-3">
                <input type="text" class="w-full p-2 bg-transparent border border-cyan-400 rounded-lg text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-300" placeholder="Username">
                <input type="password" class="w-full p-2 bg-transparent border border-cyan-400 rounded-lg text-white placeholder-gray-300 focus:ring-2 focus:ring-cyan-300" placeholder="Password">
                <button class="bg-cyan-600 w-full py-2 rounded-lg">Connect</button>
            </div>
        </div>
    </div>
</body>
</html>
