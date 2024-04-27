<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
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
            <h2>Reservation Confirmation</h2>
        </div>
        <div class="reservation-info">
            <p><strong>Reservation ID:</strong> {{ $data['reservationHASH'] }}</p>
            <p><strong>Customer:</strong> {{ $data['customerName'] }}</p>
            <p><strong>Arrival Date and Time:</strong> {{ $data['arrivalDateTime'] }}</p>
            <p><strong>Departure Date and Time:</strong> {{ $data['departureDateTime'] }}</p>

            @if (isset($data['rooms']) && count($data['rooms']) > 0)
            <p><strong>Rooms:</strong></p>
            <ul>
                @foreach($data['rooms'] as $room)
                <li><strong>Name:</strong> {{ $room['name'] }}, <strong>Type:</strong> {{ $room['roomType']['type'] }}</li>
                @endforeach
            </ul>
            @endif

            @if (isset($data['cottages']) && count($data['cottages']) > 0)
            <p><strong>Cottages:</strong></p>
            <ul>
                @foreach($data['cottages'] as $cottage)
                <li><strong>Name:</strong> {{ $cottage['name'] }}, <strong>Type:</strong> {{ $cottage['cottageType']['type'] }}</li>
                @endforeach
            </ul>
            @endif

            <p><strong>Total:</strong> ₱{{ $data['total'] }}</p>
            <p><strong>Paid:</strong> ₱{{ $data['paid'] }}</p>
            <p><strong>Balance:</strong> ₱{{ $data['balance'] }}</p>
            <p><strong>Status:</strong> {{ $data['status'] }}</p>
        </div>
        <div class="footer">
            <p>Please note that your reservation will be canceled if you do not arrive between 2pm and 8pm.</p>
            {{-- Reschedule Link --}}
            <p>If you wish to reschedule your reservation, you can do so <a href="{{ $data['rescheduleLink'] }}" class="link">here</a>.</p>
            <p>If you have any questions or need assistance, feel free to contact us at <a href="mailto:Luckyland.resort58@gmail.com" class="link">Luckyland.resort58@gmail.com</a>.</p>
            <p>We look forward to welcoming you!</p>
        </div>
    </div>
</body>

</html>