<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Email Service</title>
    <link rel="stylesheet" href="../emailservice.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
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
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
            display: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            color: #666;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .email-service-logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="email-service-logo">📧 The Framers Method</div>
        
        <div class="login-header">
            <h1>Email Service Login</h1>
            <p>Enter your credentials to access the admin panel</p>
        </div>
        
        <div id="error-message" class="error-message"></div>
        
        <form id="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn" id="login-btn">
                Sign In
            </button>
            
            <div class="loading" id="loading">
                Authenticating...
            </div>
        </form>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error-message');
            const loadingDiv = document.getElementById('loading');
            const loginBtn = document.getElementById('login-btn');
            
            // Clear previous errors
            errorDiv.style.display = 'none';
            
            // Show loading state
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing In...';
            loadingDiv.style.display = 'block';
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Success - redirect to dashboard
                    window.location.href = result.redirect;
                } else {
                    // Show error
                    errorDiv.textContent = result.message;
                    errorDiv.style.display = 'block';
                    
                    // Reset form
                    document.getElementById('password').value = '';
                    document.getElementById('password').focus();
                }
                
            } catch (error) {
                errorDiv.textContent = 'Login failed. Please try again.';
                errorDiv.style.display = 'block';
            } finally {
                // Reset button state
                loginBtn.disabled = false;
                loginBtn.textContent = 'Sign In';
                loadingDiv.style.display = 'none';
            }
        });
        
        // Focus username field on load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Enter key handling
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('login-form').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>