<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Required Actions - {{ $realm->display_name ?? $realm->name }}</title>
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
        .action-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .action-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .action-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }
        .action-header p {
            color: #666;
            font-size: 14px;
        }
        .action-list {
            margin-bottom: 30px;
        }
        .action-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .action-info h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .action-info p {
            font-size: 13px;
            color: #666;
        }
        .btn-complete {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-complete:hover {
            transform: translateY(-2px);
        }
        .btn-complete:active {
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
    <div class="action-container">
        <div class="action-header">
            <h1>Action Required</h1>
            <p>Please complete the following actions to continue</p>
        </div>

        <div class="action-list">
            @foreach($actions as $action)
                <div class="action-item">
                    <div class="action-info">
                        <h3>
                            @if($action->action === 'verify_email')
                                Verify Email Address
                            @elseif($action->action === 'update_password')
                                Update Password
                            @elseif($action->action === 'configure_totp')
                                Configure Two-Factor Authentication
                            @endif
                        </h3>
                        <p>
                            @if($action->action === 'verify_email')
                                You need to verify your email address before continuing.
                            @elseif($action->action === 'update_password')
                                Your password needs to be updated for security reasons.
                            @elseif($action->action === 'configure_totp')
                                Set up two-factor authentication to secure your account.
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ url('/realms/' . $realm->name . '/protocol/openid-connect/required-action') }}">
                        @csrf
                        <input type="hidden" name="action" value="{{ $action->action }}">
                        <button type="submit" class="btn-complete">Complete</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="help-text">
            You must complete all required actions to continue.
        </div>
    </div>
</body>
</html>
