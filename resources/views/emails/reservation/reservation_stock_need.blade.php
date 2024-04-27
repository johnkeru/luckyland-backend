<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Needed for Rooms/Cottages</title>
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
            <h2>Stock Needed for Rooms</h2>
        </div>
        @if (!empty($data['roomsNeedStock']))
        <div class="reservation-info">
            <p><strong>Rooms Needing Stock:</strong></p>
            <ul>
                @foreach($data['roomsNeedStock'] as $room)
                <li>Room: {{ $room['room_name'] }}, Item: {{ $room['item_name'] }}, Quantity Needed: {{ $room['quantity_need'] }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (!empty($data['cottagesNeedStock']))
        <div class="reservation-info">
            <p><strong>Cottages Needing Stock:</strong></p>
            <ul>
                @foreach($data['cottagesNeedStock'] as $cottage)
                <li>Cottage: {{ $cottage['cottage_name'] }}, Item: {{ $cottage['item_name'] }}, Quantity Needed: {{ $cottage['quantity_need'] }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="footer">
            <p>Please ensure that the necessary items are stocked for the rooms and cottages mentioned above.</p>
            <p>If you have any questions or need assistance, feel free to contact us at <a href="mailto:Luckyland.resort58@gmail.com" class="link">Luckyland.resort58@gmail.com</a>.</p>
            <p>We look forward to resolving this matter with your help!</p>
        </div>
    </div>
</body>

</html>