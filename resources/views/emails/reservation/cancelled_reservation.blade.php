<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #c43c35;
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        p {
            color: #4a5568;
            font-size: 16px;
            margin: 0 0 10px 0;
        }

        .link {
            color: #3182ce;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <p>Dear {{ $data['name'] }},<br><br>We regret to inform you that your reservation has been cancelled. Please contact us if you have any questions or concerns.<br><br>Thank you,<br>LuckyLand Resort</p>
        <p><strong>Refund:</strong> ₱{{$data['refund']}}</p>
        <p>If you have any questions or need assistance, feel free to contact us at <a href="mailto:Luckyland.resort58@gmail.com" class="link">Luckyland.resort58@gmail.com</a>.</p>
        <p>We apologize for any inconvenience caused.</p>
    </div>


</body>

</html>