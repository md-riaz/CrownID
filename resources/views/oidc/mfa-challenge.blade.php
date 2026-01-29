<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - {{ $realm->display_name ?? $realm->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .mfa-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .mfa-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .mfa-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }
        .mfa-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 8px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
        }
        .btn-verify:active {
            transform: translateY(0);
        }
        .help-text {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="mfa-container">
        <div class="mfa-header">
            <h1>Two-Factor Authentication</h1>
            <p>Enter your verification code</p>
        </div>

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/realms/' . $realm->name . '/protocol/openid-connect/mfa') }}">
            @csrf
            
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    required 
                    autofocus
                    maxlength="8"
                    placeholder="000000"
                >
            </div>

            <button type="submit" class="btn-verify">Verify</button>
        </form>

        <div class="help-text">
            Enter the 6-digit code from your authenticator app<br>
            or use a backup code if needed.
        </div>
    </div>
</body>
</html>
