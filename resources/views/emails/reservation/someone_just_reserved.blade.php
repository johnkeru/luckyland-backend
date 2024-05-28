<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reservation Notification</title>
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

        /* .reservation-info {
            margin-bottom: 20px;
        } */

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
            <h2>New Reservation Notification</h2>
        </div>
        <div class="reservation-info">
            <p><strong>Reservation ID:</strong> {{ $data['reservationHASH'] }}</p>
            <p><strong>Customer:</strong> {{ $data['customerName'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            <p><strong>Arrival Date and Time:</strong> {{ $data['arrivalDateTime'] }}</p>
            <p><strong>Departure Date and Time:</strong> {{ $data['departureDateTime'] }}</p>

            @if (isset($data['rooms']) && count($data['rooms']) > 0)
                <p><strong>Rooms:</strong></p>
                <ul>
                    @foreach ($data['rooms'] as $room)
                        <li><strong>Name:</strong> {{ $room['name'] }}, <strong>Type:</strong>
                            {{ $room['roomType']['type'] }}</li>
                    @endforeach
                </ul>
            @endif

            @if (isset($data['cottages']) && count($data['cottages']) > 0)
                <p><strong>Cottages:</strong></p>
                <ul>
                    @foreach ($data['cottages'] as $cottage)
                        <li><strong>Name:</strong> {{ $cottage['name'] }}, <strong>Type:</strong>
                            {{ $cottage['cottageType']['type'] }}</li>
                    @endforeach
                </ul>
            @endif

            @if (isset($data['others']) && count($data['others']) > 0)
                <p><strong>Others:</strong></p>
                <ul>
                    @foreach ($data['others'] as $other)
                        <li><strong>Name:</strong> {{ $other['name'] }}, <strong>Type:</strong>
                            {{ $other['otherType']['type'] }}</li>
                    @endforeach
                </ul>
            @endif

            <p><strong>Total:</strong> ₱{{ $data['total'] }}</p>
            <p><strong>Paid:</strong> ₱{{ $data['paid'] }}</p>
            <p><strong>Balance:</strong> ₱{{ $data['balance'] }}</p>
            <p><strong>Status:</strong> {{ $data['status'] }}</p>

            <p>If needed, the reservation can be rescheduled <a href="{{ $data['rescheduleLink'] }}"
                    class="link">here</a>.</p>
        </div>
        <div class="footer">
            <p>Please take necessary action.</p>
        </div>
    </div>
</body>

</html>
