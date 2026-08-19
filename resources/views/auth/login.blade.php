<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }
        .login-card {
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .login-card:hover {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.25) !important;
        }
        .btn-primary {
            transition: transform 0.15s ease;
        }
        .btn-primary:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-4">
                <div class="card login-card shadow rounded-4 p-4">
                    <div class="card-body">
                        <h1 class="h3 text-center mb-4">Login</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="loginForm" method="POST" action="{{ route('login.submit') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="login" class="form-label">Email or Phone</label>
                                <input
                                    id="login"
                                    type="text"
                                    name="login"
                                    class="form-control @error('login') is-invalid @enderror"
                                    value="{{ old('login') }}"
                                    required
                                    autofocus
                                >
                                @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password">
                                        <svg id="togglePasswordIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button type="submit" id="loginSubmit" class="btn btn-primary w-100">
                                <span id="loginSubmitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="loginSubmitText">Login</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function () {
            var eyeIcon = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>';
            var eyeSlashIcon = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-1.79.234l1.192 1.192A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755zm-2.943 1.299.734.733a6.4 6.4 0 0 1-1.985.293c-2.12 0-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8a13 13 0 0 1 1.66-2.043C3.879 4.668 5.879 3.5 8 3.5q.088 0 .175.005L1.173 8a13 13 0 0 1 1.66-2.043"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299-1.155-1.155A2.5 2.5 0 0 1 5.5 8a2.5 2.5 0 0 1 .5-1.5l.823.823a1.5 1.5 0 0 0-.323.677l-.5-.5m1.354 1.354.756.756a2.5 2.5 0 0 1-2.5-2.5l.5.5a1.5 1.5 0 0 0 1.244 1.244"/><path d="M0.359 0.646a0.5 0.5 0 0 1 0.707 0l14.288 14.288a0.5 0.5 0 0 1-0.708 0.708L0.359 1.354a0.5 0.5 0 0 1 0-0.708"/>';

            $('#togglePassword').on('click', function () {
                var $password = $('#password');
                var isPassword = $password.attr('type') === 'password';
                $password.attr('type', isPassword ? 'text' : 'password');
                $('#togglePasswordIcon').html(isPassword ? eyeSlashIcon : eyeIcon);
                $(this).attr('aria-label', isPassword ? 'Hide password' : 'Show password');
            });

            $('#login, #password').on('input', function () {
                $(this).removeClass('is-invalid');
            });

            $('#loginForm').on('submit', function () {
                $('#loginSubmit').prop('disabled', true);
                $('#loginSubmitSpinner').removeClass('d-none');
                $('#loginSubmitText').text('Logging in...');
            });
        });
    </script>
</body>
</html>
