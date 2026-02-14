<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gold Manufacturing & Billing ERP - Installation Successful</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .logo p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .success-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            display: inline-block;
            margin: 20px 0;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px 0 rgba(245, 87, 108, 0.4);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .info-card h3 {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-card p {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .features {
            margin-top: 40px;
        }

        .features h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .features ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .features li {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            border-radius: 10px;
            padding-left: 45px;
            position: relative;
        }

        .features li:before {
            content: "✓";
            position: absolute;
            left: 15px;
            font-size: 1.5rem;
            color: #4ade80;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .features ul {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>💎 Gold Manufacturing & Billing ERP</h1>
            <p>Multi-Tenant SaaS Platform</p>
        </div>

        <div style="text-align: center;">
            <span class="success-badge">✓ Installation Successful</span>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>Framework</h3>
                <p>CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></p>
            </div>
            <div class="info-card">
                <h3>PHP Version</h3>
                <p><?= phpversion() ?></p>
            </div>
            <div class="info-card">
                <h3>Environment</h3>
                <p><?= ENVIRONMENT ?></p>
            </div>
            <div class="info-card">
                <h3>Version</h3>
                <p>1.0.0</p>
            </div>
        </div>

        <div class="features">
            <h2>🚀 System Features</h2>
            <ul>
                <li>Multi-Tenant Architecture</li>
                <li>Gold Weight Tracking</li>
                <li>Invoice Management</li>
                <li>Payment Processing</li>
                <li>Customer Management</li>
                <li>Supplier Management</li>
                <li>Product Master</li>
                <li>Ledger System</li>
                <li>Report Generation</li>
                <li>Role-Based Access Control</li>
                <li>Audit Logging</li>
                <li>RESTful API</li>
            </ul>
        </div>

        <div class="footer">
            <p>Ready for development! Check QUICK_START.md for next steps.</p>
            <p style="margin-top: 10px;">Powered by CodeIgniter 4 • Built with ❤️ for Gold Industry</p>
        </div>
    </div>
</body>
</html>
