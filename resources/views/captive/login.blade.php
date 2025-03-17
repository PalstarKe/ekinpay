<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- laravel CRUD token -->
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
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
        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-5 shadow-lg">
            <h2 class="text-lg font-semibold text-cyan-300 mb-3 text-center">Choose Package</h2>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($packages as $package)
                    <button class="bg-cyan-700 p-3 rounded-lg text-center shadow-md package-btn"
                        data-package-id="{{ $package->id }}"
                        data-package-name="{{ $package->name_plan }}"
                        data-package-price="{{ $package->price }}"
                        data-nas-ip="{{ $nas_ip }}">
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
    <div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-5 rounded-lg text-black w-80">
            <h2 class="text-lg font-bold text-center mb-3">Enter Your Phone Number</h2>
            <form id="paymentForm">
                <input type="hidden" id="selectedPackageId">
                <input type="hidden" id="nasIp">
                <input type="hidden" id="macAddress" value="00:11:22:33:44:55"> <!-- Replace dynamically -->

                <div class="mb-4">
                    <input type="text" id="phoneNumber" placeholder="Enter M-Pesa Number"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded-lg">Proceed</button>
                <button type="button" id="closeModal" class="w-full mt-2 py-2 rounded-lg bg-gray-300">Cancel</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let checkoutRequestID = null;
            let pollingInterval = null;
            
            $('.package-btn').click(function() {
                let packageID = $(this).data('package-id');
                let nasIp = $(this).data('nas-ip');
                let macAddress = "00:11:22:33:44:55";

                Swal.fire({
                    title: "Enter M-Pesa Number",
                    input: "text",
                    inputPlaceholder: "07XXXXXXXX",
                    showCancelButton: true,
                    confirmButtonText: "Pay Now",
                    preConfirm: (phoneNumber) => {
                        if (!phoneNumber.match(/^07\d{8}$/)) {
                            Swal.showValidationMessage("Enter a valid M-Pesa number (07XXXXXXXX)");
                        }
                        return phoneNumber;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        processCustomer(nasIp, packageID, result.value, macAddress);
                    }
                });
            });

            function processCustomer(nasIp, packageID, phoneNumber, macAddress) {
                Swal.fire({
                    title: "Processing Payment...",
                    html: "Please wait while we initiate STK Push...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('processCustomer') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nas_ip: nasIp,
                        package_id: packageID,
                        phone_number: phoneNumber,
                        mac_address: macAddress
                    },
                    success: function(response) {
                        if (response.success) {
                            checkoutRequestID = response.checkoutRequestID;
                            Swal.fire("STK Push Sent!", "Enter M-Pesa PIN on your phone.", "info");
                            startPolling(nasIp, packageID, phoneNumber, macAddress);
                        } else {
                            Swal.fire("Error", response.message || "Failed to initiate payment", "error");
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Something went wrong. Try again.", "error");
                    }
                });
            }

            function startPolling(nasIp, packageID, phoneNumber, macAddress) {
                pollingInterval = setInterval(function() {
                    checkPaymentStatus(nasIp, packageID, phoneNumber, macAddress);
                }, 5000);
            }

            function checkPaymentStatus(nasIp, packageID, phoneNumber, macAddress) {
                $.ajax({
                    url: "{{ route('processQueryMpesa') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ref: checkoutRequestID
                    },
                    success: function(response) {
                        if (response.success && response.status === "COMPLETED") {
                            clearInterval(pollingInterval);
                            Swal.fire("Payment Successful!", "You are now connected.", "success").then(() => {
                                window.location.href = "{{ route('captive.showLogin', ['nas_ip' => '']) }}" + nasIp;
                            });
                        } else if (response.status === "FAILED") {
                            clearInterval(pollingInterval);
                            Swal.fire("Payment Failed", "Please try again.", "error");
                        }
                    },
                    error: function() {
                        clearInterval(pollingInterval);
                        Swal.fire("Error", "Could not verify payment. Try again.", "error");
                    }
                });
            }
        });
    </script>

</body>

</html>
