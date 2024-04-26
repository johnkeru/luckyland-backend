<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333333;
        }

        p {
            margin-bottom: 15px;
            color: #555555;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #999999;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Password Reset</h2>
        <p>Hello,</p>
        <p>You are receiving this email because we received a password reset request for your account.</p>
        <p>Please click the following link to reset your password:</p>
        <p><a href="{{ $data['resetToken'] }}">{{ $data['resetToken'] }}</a></p>
        <p>If you did not request a password reset, no further action is required.</p>
        <p class="footer">Thank you!</p>
    </div>
</body>

</html>