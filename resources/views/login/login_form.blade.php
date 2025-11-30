<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>WorkEase KCE - Login</title>
     <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/kce.png') }}">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="description" content="WorkEase KCE Login">
    <meta name="author" content="WorkEase KCE">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    {{-- Google Fonts --}}
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" />

    {{-- Custom Notifications CSS --}}
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">

    <style>
        body {
            background: #f2f2f2 url('https://assets.siakadcloud.com/assets/v1/img/pattern/pat_04.png') repeat;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            display: flex;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 35px 0 rgb(154 161 171 / 20%);
            max-width: 900px;
            width: 100%;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(rgba(0, 0, 0, 0.3),
                    rgba(0, 0, 0, 0.3)),
                url('{{ asset('assets/img/ptkce.png') }}') center center no-repeat;
            background-size: cover;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            border-radius: 10px 0 0 10px;
        }

        .left-section h2 {
            font-weight: 700;
            font-size: 26px;
        }

        .right-section {
            flex: 0.9;
            padding: 50px 40px;
            text-align: center;
        }

        .right-section img {
            width: 120px;
            margin-bottom: 20px;
        }

        .login-title {
            font-weight: 600;
            font-size: 22px;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px 40px 10px 12px;
            font-size: 14px;
            width: 100%;
        }

        /* Tombol mata */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            margin: 0;
            line-height: 1;
        }

        .password-toggle i {
            font-size: 16px;
        }

        .password-toggle:hover {
            color: #0067bd;
        }

        /* Hide browser's native password reveal button - More aggressive approach */
        input[type="password"]::-webkit-credentials-auto-fill-button,
        input[type="password"]::-webkit-strong-password-auto-fill-button,
        input[type="password"]::-webkit-inner-spin-button,
        input[type="password"]::-webkit-outer-spin-button {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            position: absolute !important;
            right: -9999px !important;
        }

        /* Hide password reveal button for Chrome/Edge */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* For Firefox */
        input[type="password"] {
            -moz-appearance: textfield;
        }

        /* Ensure input has enough padding for only our custom button */
        #password.form-control {
            padding-right: 45px !important;
        }

        /* Ensure our custom button is always on top and properly positioned */
        .password-toggle {
            z-index: 10;
            right: 12px !important;
        }

        .login-btn {
            background-color: #0067bd;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            width: 100%;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .login-btn:hover {
            background-color: #004680;
        }

        .text-danger {
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                margin: 20px;
            }

            .left-section {
                border-radius: 10px 10px 0 0;
                min-height: 200px;
            }

            .right-section {
                padding: 30px;
            }
        }
    </style>

    {{-- Stack for styles from components --}}
    @stack('styles')
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            {{-- Left Section --}}
            <div class="left-section">
                <div>
                    <h4>Selamat Datang</h4>
                    <h2>WorkEase KCE</h2>
                    <p>Sistem Informasi Karyawan Terpadu</p>
                </div>
            </div>

            {{-- Right Section --}}
            <div class="right-section">
                <img src="{{ asset('assets/img/kce.png') }}" alt="KCE Logo">
                <h1 class="login-title">Masuk ke Akun Anda</h1>

                <form method="post" action="{{ route('login.authenticate') }}" class="no-transition">
                    {{ csrf_field() }}

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="Masukkan email" value="{{ old('email') }}" required autocomplete="off" />
                        </div>
                        @error('email')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Masukkan password" required autocomplete="off" />
                            <button type="button" class="password-toggle" id="passwordToggle"
                                aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Button --}}
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Page Transition Component --}}
    @include('components.page-transition')

    {{-- Notifications Component --}}
    @include('components.notifications')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Password Toggle Script --}}
    <script>
        const toggle = document.getElementById('passwordToggle');
        const password = document.getElementById('password');
        toggle.addEventListener('click', () => {
            if (password.type === 'password') {
                password.type = 'text';
                toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                password.type = 'password';
                toggle.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });

        // Show transition on login form submission - langsung ke animasi selamat datang
        document.querySelector('form').addEventListener('submit', function(e) {
            // Tidak perlu animasi memverifikasi, langsung submit form
            // Animasi selamat datang akan muncul di halaman dashboard
        });
    </script>

    {{-- Stack for scripts from components --}}
    @stack('scripts')
</body>

</html>
