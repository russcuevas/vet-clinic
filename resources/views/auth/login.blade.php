<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>San Modesto Vet Clinic - Portal Login</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Assets in public/css/ -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer-animation.css') }}?v={{ time() }}">

    @if(session('success'))
        <meta name="flash-success" content="{{ session('success') }}">
    @endif
    @if(session('error'))
        <meta name="flash-error" content="{{ session('error') }}">
    @endif

    <style>
        body {
            background: radial-gradient(circle at top center, #10233D 0%, #060D17 60%, #04070C 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle background gold glow */
        body::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-card-container {
            width: 100%;
            max-width: 490px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(17, 19, 28, 0.92);
            border: 1px solid var(--gold-border);
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), 0 0 35px rgba(212, 175, 55, 0.12);
            backdrop-filter: blur(16px);
            padding: 2.5rem 2.25rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo-wrapper {
            margin-bottom: 1.25rem;
        }

        .login-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.02em;
        }

        .login-title span {
            color: var(--gold-primary);
        }

        .login-subtitle {
            font-size: 0.82rem;
            color: var(--text-secondary);
            margin-top: 0.35rem;
        }

        .demo-roles-container {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--black-border);
        }

        .demo-roles-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--gold-primary);
            text-align: center;
            margin-bottom: 0.85rem;
        }

        .demo-buttons-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.65rem;
        }

        .btn-demo-role {
            padding: 0.55rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--navy-dark);
            border: 1px solid var(--navy-border);
            color: var(--text-secondary);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.15rem;
        }

        .btn-demo-role strong {
            color: var(--white);
            font-size: 0.8rem;
        }

        .btn-demo-role:hover {
            border-color: var(--gold-primary);
            color: var(--gold-light);
            background: var(--navy-surface);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-card-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo-wrapper" style="width: 100%; display: flex; justify-content: center; margin-bottom: 0.75rem;">
                    <x-logo size="lg" :showText="false" />
                </div>
                <p class="login-subtitle" style="font-size: 0.82rem; color: var(--text-secondary); letter-spacing: 0.04em;">
                    Sign in to access your role workspace
                </p>
            </div>

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control" 
                        placeholder="e.g. staff@sanmodesto.com" 
                        value="{{ old('email', 'admin@sanmodesto.com') }}" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="req">*</span></label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control" 
                        placeholder="••••••••" 
                        value="password"
                        required
                    >
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: var(--gold-primary);">
                        <span>Remember credentials</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-gold" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                    <span>Sign In to System</span>
                </button>
            </form>

            <!-- Quick 1-Click Role Switchers for testing -->
            <div class="demo-roles-container">
                <div class="demo-roles-title">⚡ Quick 1-Click Role Login</div>
                <div class="demo-buttons-grid">
                    <button type="button" class="btn-demo-role" onclick="fillCredentials('admin@sanmodesto.com', 'Admin Portal')">
                        <strong>Admin</strong>
                        <span>Full Control</span>
                    </button>
                    <button type="button" class="btn-demo-role" onclick="fillCredentials('cashier@sanmodesto.com', 'Cashier Portal')">
                        <strong>Cashier</strong>
                        <span>Billing & POS</span>
                    </button>
                    <button type="button" class="btn-demo-role" onclick="fillCredentials('vet@sanmodesto.com', 'Veterinarian Portal')">
                        <strong>Veterinarian</strong>
                        <span>Medical & Rx</span>
                    </button>
                    <button type="button" class="btn-demo-role" onclick="fillCredentials('manager@sanmodesto.com', 'Manager Portal')">
                        <strong>Manager</strong>
                        <span>Sales Reports</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Animated Walking Pet at Bottom of Login Screen -->
    <div class="login-walking-pet-footer">
        <div class="login-pet-walker">
            <img src="{{ asset('uploads/logos/footer.gif') }}" alt="Walking Companion" class="login-pet-gif">
        </div>
        <div class="login-ground-line"></div>
    </div>

    <!-- Container for Top-Right Toast Alerts -->
    <div class="toast-container"></div>

    <script src="{{ asset('js/toast.js') }}"></script>
    <script>
        function fillCredentials(email, roleName) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            window.Toast.show('info', 'Role Selected', `Credentials for ${roleName} loaded. Click Sign In to enter!`);
        }
    </script>
</body>
</html>
