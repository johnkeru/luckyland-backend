<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Confirmation</title>
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #333;
        }

        .reservation-info {
            /* margin-bottom: 20px; */
        }

        .reservation-info p {
            color: #666;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }

        .footer p {
            color: #888;
        }

        .link {
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Reschedule Confirmation</h2>
        </div>
        <div class="reservation-info">
            <p><strong>Updated Balance:</strong> ₱{{ $data['balance'] }}</p>
            <p><strong>Customer Name:</strong> {{ $data['customerName'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
        </div>
        <div class="footer">
            <p>Customer {{$data['customerName']}} has just made a reschedule successfully.</p>
            <p>Please ensure all necessary adjustments are made accordingly.</p>
        </div>
    </div>
</body>

</html>