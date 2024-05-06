<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Departed Notification</title>
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

        .rooms-to-clean {
            margin-bottom: 20px;
        }

        .rooms-to-clean p {
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
            <h2>Rooms Left to Clean</h2>
        </div>
        <div class="rooms-to-clean">
            @if (isset($data['rooms']) && count($data['rooms']) > 0)
            <p><strong>Customer:</strong> {{ $data['customerName'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            <p><strong>Rooms Left to Clean:</strong></p>
            <ul>
                @foreach($data['rooms'] as $room)
                <li><strong>Name:</strong> {{ $room['name'] }}, <strong>Type:</strong> {{ $room['roomType']['type'] }}</li>
                @endforeach
            </ul>
            @else
            <p>No rooms left to clean.</p>
            @endif
        </div>
        <div class="footer">
            <p>Please take necessary action.</p>
        </div>
    </div>
</body>

</html>