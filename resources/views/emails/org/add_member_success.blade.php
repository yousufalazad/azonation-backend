<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ $orgName }}!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            margin: 50px auto;
            padding: 20px;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #007bff;
            padding: 10px;
            text-align: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h2 {
            color: #333333;
            font-size: 22px;
        }
        .content p {
            color: #666666;
            font-size: 16px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 20px;
            padding: 10px;
            background-color: #f4f4f4;
            text-align: center;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        .footer p {
            color: #999999;
            font-size: 14px;
        }
        .button {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ $orgName }}!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $individualData }},</h2>
            <p>We are excited to welcome you as a new member of <strong>{{ $orgName }}</strong>. Your membership has been successfully added, and we are thrilled to have you with us.</p>
            <p>If you have any questions or need assistance, feel free to reach out to us. We're here to help!</p>
            <a href="{{ url('/login') }}" class="button">Visit Our Website</a>
        </div>
        <div class="footer">
            <p>Thank you for being a part of {{ $orgName }}.</p>
            <br>
            <p>Powered by <a href="https://azonation.com">Azonation</a></p>
        </div>
    </div>
</body>
</html>
