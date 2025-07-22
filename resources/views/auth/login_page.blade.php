<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="AF-TMS login portal - Access your account securely">
    <title>AF-TMS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset_path('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <img src="{{ asset_path('/img/logo_full.png') }}" alt="admin Logo" class="logo">
    <div class="subtitle">Test Management System</div>

    <div class="login-card">
        <h1 class="login-title">Welcome Back</h1>
        <p class="login-subtitle">Please sign in to continue</p>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('auth') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <i class="far fa-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                        value="{{ old('email') }}" required autofocus>
                </div>
                <div class="invalid-feedback" id="emailError" style="display: none;">
                    Please enter a valid email address
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <i class="fas fa-key"></i>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password" required>
                    <i class="far fa-eye-slash password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>

    <div class="copyright">
        Copyright © 2024 - {{ now()->year }} AF-TMS — Supported by QaraTMS
    </div>

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Password Recovery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>For password recovery, please contact:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-user me-3 text-primary"></i>
                            <a href="https://admin.slack.com/archives/" target="_blank"
                                class="text-decoration-none">
                                <strong>Master Roblox</strong>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary ms-auto copy-message"
                                data-message="Hi, I need to reset my password for the AF-TMS system. Could you please help me regain access? Thank you.">
                                <i class="fas fa-copy me-1"></i> Copy message
                            </button>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-user me-3 text-primary"></i>
                            <a href="https://admin.slack.com/archives/" target="_blank"
                                class="text-decoration-none">
                                <strong>Guru Mentari</strong>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary ms-auto copy-message"
                                data-message="Hi, I need to reset my password for the AF-TMS system. Could you please help me regain access? Thank you.">
                                <i class="fas fa-copy me-1"></i> Copy message
                            </button>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-user me-3 text-primary"></i>
                            <a href="https://admin.slack.com/archives/" target="_blank"
                                class="text-decoration-none">
                                <strong>Bocah Tua Nakal</strong>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary ms-auto copy-message"
                                data-message="Hi , I need to reset my password for the AF-TMS system. Could you please help me regain access? Thank you.">
                                <i class="fas fa-copy me-1"></i> Copy message
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset_path('js/auth.js') }}"></script>
</body>

</html>