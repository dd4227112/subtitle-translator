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
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        Show
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
            $('#togglePassword').on('click', function () {
                var $password = $('#password');
                var isPassword = $password.attr('type') === 'password';
                $password.attr('type', isPassword ? 'text' : 'password');
                $(this).text(isPassword ? 'Hide' : 'Show');
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
