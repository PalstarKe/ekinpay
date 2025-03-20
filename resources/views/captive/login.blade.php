<!DOCTYPE html>
<html manifest="cache.appcache">
    <head>
        <meta charset="UTF-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="theme-color" content="#000000"/>
        <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>HOTSPOT LOGIN</title>

        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-blue-500">
        <div class="login">
            <div class="container mx-auto mt-4 px-4 mb-4">
                <div class="max-w-md mx-auto effect overflow-hidden md:max-w-lg">
                    <div class="w-full p-4 bg-clip-padding backdrop-filter backdrop-blur-md bg-opacity-10 border border-gray-100 rounded-lg shadow sm:p-6 md:p-8  ">
                        <div class="flex flex-col items-center ">
                            <h5 class="mb-1 text-xl font-medium text-white">{{ strtoupper($company?->name) }}</h5>
                        </div>
                            <span class="text-sm text-white ">How To Purchase:-.</span> </br>
                            <span class="text-sm text-white ">1.Choose your preffered package.</span> </br>
                            <span class="text-sm text-white ">2.Enter your phone number</span> </br>
                            <span class="text-sm text-white ">3.Click "PAY NOW"</span> </br>
                            <span class="text-sm text-white ">4.Enter your m-pesa pin, wait for 30sec for mpesa authentication.</span>
                        <div class="flex flex-col items-center ">
                            <div class="mt-4 flex md:mt-6">
                                <a href="#" class="inline-flex items-center rounded-lg bg-blue-700 border border-gray-200 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 ">CUSTOMER
                                    CARE: 
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container mx-auto mb-4 mt-4 px-4 effect mx-auto max-w-md overflow-hidden md:max-w-lg">
                <!-- <div class=""> -->
                    <div class="mb-3 w-full p-4 bg-clip-padding backdrop-filter backdrop-blur-md bg-opacity-10 border border-gray-100 rounded-lg shadow sm:p-6 md:p-8 ">
                        <div class="flex flex-col items-center">
                            <h5 class="mb-3 text-base font-semibold text-white md:text-xl ">Our Packages</h5>
                            <p class="text-sm font-normal text-white ">Subscribe to one of our available Packages.</p>
                        </div>
                        <form id="reconForm" method="post" action="{{ session('hotspot_login')['loginLink'] ?? '' }}" onSubmit="return doLogin();" style="display: none;">
                            <input type="hidden" name="dst" value="https://www.youtube.com" />
                            <input type="hidden" name="popup" value="true" />
                            <input type="hidden" name="reconame" id="recoName" value="{{ session('mac') }}">
                            <input type="hidden" name="reconpassword" id="recoPass" value="">
                        </form>
                        <button onclick="doLogin2()" id="submitBtn" class="group w-full flex items-center  text-center rounded-lg bg-blue-700 p-3 text-base border border-gray-200 font-bold text-white hover:bg-blue-800 hover:shadow mb-3">
                            <span class="ms-3 flex-1 whitespace-nowrap">Already Paid? Click Here!</span>
                        </button>
                        <div class="mx-auto grid grid-cols-3 gap-4">
                            @foreach ($packages as $package)
                                <button class="package-btn bg-blue-700 border border-gray-200 ring ring-blue-700 ring-offset-2 py-2 h-24 w-24 
                                    shadow-md text-white flex flex-col justify-between items-center rounded-lg 
                                    hover:bg-blue-800 transition duration-300 relative"
                                    data-package-id="{{ $package->id }}"
                                    data-package-name="{{ $package->name_plan }}"
                                    data-package-price="{{ $package->price }}"
                                    data-mac-address="{{ session('mac') }}"
                                    data-nas-ip="{{ $nas_ip }}">
                                    <div class="absolute top-0 w-full bg-white text-blue-700 text-[9px] font-semibold py-1 text-center rounded-t-lg">
                                        UNLIMITED
                                    </div>
                                    <span class="mt-4 font-semibold text-[16px] text-center leading-tight">
                                        {{ $package->name_plan }}
                                    </span>
                                    <span class="text-[18px] font-bold text-center leading-tight">
                                        Ksh {{ (int) $package->price }}
                                    </span>
                                    <span class="text-[14px] mb-2 text-center">
                                        {{ $package->validity }} {{ $package->validity_unit }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <input hidden type="text" id="amount">
                        <input hidden type="text" id="mac" value='$(mac)'>
                        <div class="flex flex-col items-center ">
                            <div class="mt-4">
                                <a href="#" class="inline-flex items-center text-xs font-normal text-white hover:underline ">
                                    <svg class="me-2 h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7.529 7.988a2.502 2.502 0 0 1 5 .191A2.441 2.441 0 0 1 10 10.582V12m-.01 3.008H10M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    When Paying Please wait untill Redirected?</a>
                            </div>
                        </div>
                    </div>
                <!-- </div> -->
            </div>
            <div class="container mx-auto mt-4 px-4 mb-4">
                <div class="max-w-md mx-auto effect overflow-hidden md:max-w-lg">
                    <div class="w-full p-4 bg-clip-padding backdrop-filter backdrop-blur-md bg-opacity-10 border border-gray-100 rounded-lg shadow sm:p-6 md:p-8 ">
                        <div class="flex flex-col items-center mt-2">
                            <h5 class="mb-2 text-base font-semibold text-white md:text-xl ">Redeem  Voucher</h5>
                            <!-- <p class="text-sm font-normal text-white ">Enter Voucher code recieved from admin.</p> -->
                        </div>
                        <div class="card2 login">
                            <input hidden type="text" id="mac2" value='$(mac)'>
                            <div class="relative">
                                <input  type="text" id="code" name="code" oninput="this.value = this.value.toUpperCase();" class="block w-full p-4 text-sm text-blue-500 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-200 dark:border-gray-300 dark:placeholder-blue-700  dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="PAL254" required />
                                <button id="voucher-form" class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 ">Redeem</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="login card2">
            <div class="container mx-auto mt-4 px-4 mb-4">
                <div class="max-w-md mx-auto effect overflow-hidden md:max-w-lg">
                    <div class="w-full p-4 bg-clip-padding backdrop-filter backdrop-blur-md bg-opacity-10 border border-gray-100 rounded-lg shadow sm:p-6 md:p-8 ">
                        <form id="loginF" class="login space-y-6 form" name="login" action="$(link-login-only)" method="post" $(if chap-id) onSubmit="return doLogin()" $(endif)>
                            <div class="flex flex-col items-center">
                                <h5 class="text-xl font-medium text-white ">Cash login</h5>
                            </div>
                            <input type="hidden" name="dst" value="https://www.youtube.com" />
                            <input type="hidden" name="popup" value="true" />
                            <input hidden type="text" id="mac2" value='$(mac)'>
                            <div>
                                <label for="username" class="block mb-2 text-sm font-medium text-white">Username</label>
                                <input id="usernameInput" name="username" type="text" value="" placeholder="Username"
                                    class="block w-full p-4 text-sm text-blue-500 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-200 dark:border-gray-300 dark:placeholder-blue-700  dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Username"/>
                            </div>
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-white">Password</label>
                                <input type="password" id="passwordInput" name="password" value="1234" placeholder="Password"
                                    class="block w-full p-4 text-sm text-blue-500 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-200 dark:border-gray-300 dark:placeholder-blue-700  dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
                            </div>
                            <button  type="submit"
                                class="w-full text-white bg-blue-700 border border-gray-200 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-base font-bold px-5 p-3 text-center">Connect</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <form id="login" method="post" action="{{ session('hotspot_login')['loginLink'] ?? '' }}" onSubmit="return doLogin();">
            <input name="dst" type="hidden" value="https://www.google.com" />
            <input name="popup" type="hidden" value="false" />
            <input name="username" type="hidden" value="{{ session('mac') }}"/>
            <input name="password" type="hidden"/>
        </form>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

        <script>
            function doLogin() {
                document.sendin.username.value = document.login.username.value;
                document.sendin.password.value = hexMD5('\011\373\054\364\002\233\266\263\270\373\173\323\234\313\365\337\356');
                document.sendin.submit();
                return false;
            }
            $(document).ready(function() {
                let checkoutRequestID = null;
                let pollingInterval = null;

                var msg = "You are about to pay KSH: ${amount}. Enter phonenumber below and click PAY NOW";
                const regexp = /\${([^{]+)}/g;
                let result = msg.replace(regexp, function (ignore, key) {
                    return eval(key);
                });

                $('.package-btn').click(function() {
                    let packageID = $(this).data('package-id');
                    let nasIp = $(this).data('nas-ip');
                    let macAddress = $(this).data('mac-address');
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
                                cID = response.cID;
                                Swal.fire({
                                    title: "STK Push Sent!",
                                    html: `
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <img src="/assets/Loading.gif" width="150" alt="Verifying Payment...">
                                            <br>
                                            <p>Enter M-Pesa PIN on your phone.</p>
                                        </div>
                                    `,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
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
                            ref: checkoutRequestID,
                            cID: cID,
                            nas_ip: nasIp,
                            package_id: packageID,
                            phone_number: phoneNumber,
                            mac_address: macAddress
                        },

                        success: function(response) {
                            console.log("M-Pesa Response:", response);
                            if (response.success && response.ResultCode === "0") {
                                clearInterval(pollingInterval);
                                Swal.fire({
                                    title: "Payment Successful!",
                                    text: "You are now connected.",
                                    icon: "success",
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    timer: 2000
                                }).then(() => {
                                    var frm = document.getElementById("login");
                                    frm.submit();
                                });
                            } else if (response.ResultCode === "1") {
                                clearInterval(pollingInterval);
                                Swal.fire("Payment Failed", response.ResultDesc, "error");
                            } else if (response.ResultCode === "1032") {
                                clearInterval(pollingInterval);
                                Swal.fire("Payment Canceled", response.ResultDesc, "error");
                            }
                        },
                        error: function() {
                            clearInterval(pollingInterval);
                            Swal.fire("Error", response.ResultDesc, "Could not verify payment. Try again.", "error");
                        }
                    });
                }
            });
        </script>

    </body>
</html>