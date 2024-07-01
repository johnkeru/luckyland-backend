<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reservation Notification</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: url('https://example.com/resort-background.jpg') no-repeat center center fixed;
            /* Replace with your image URL */
            background-size: cover;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #444444;
            font-size: 24px;
            margin: 0;
        }

        .reservation-info p {
            color: #555555;
            font-size: 16px;
            margin: 10px 0;
        }

        .reservation-info p strong {
            color: #333333;
        }

        .reservation-info ul {
            padding-left: 20px;
            margin: 10px 0;
            list-style: none;
        }

        .reservation-info ul li {
            color: #555555;
            font-size: 16px;
            margin: 5px 0;
        }

        .reservation-info ul li ul {
            padding-left: 20px;
        }

        .reservation-info ul li ul li {
            color: #666666;
            font-size: 14px;
        }

        .link {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }

        .link:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }

        .footer p {
            color: #777777;
            font-size: 14px;
            margin: 0;
        }

        .card {
            background-color: #ffffff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-item {
            margin: 10px 0;
            color: #555555;
            font-size: 16px;
        }

        .highlight {
            background-color: #f5f5f5;
            border-left: 5px solid #ffcc00;
            padding-left: 15px;
        }

        .sub-item {
            margin-left: 20px;
            color: #666666;
            font-size: 14px;
        }

        .section-title {
            font-size: 18px;
            color: #333333;
            margin: 10px 0;
            font-weight: bold;
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
            <p><strong>Guests:</strong> {{ $data['guests'] }}</p>
            <p><strong>Arrival Date and Time:</strong> {{ $data['arrivalDateTime'] }}</p>
            <p><strong>Departure Date and Time:</strong> {{ $data['departureDateTime'] }}</p>

            @if (isset($data['roomAddOns']) && count($data['roomAddOns']) > 0)
                <div class="card">
                    <p><strong>Rooms:</strong></p>
                    <ul>
                        @foreach ($data['roomAddOns'] as $room)
                            <li class="card-item highlight">
                                <strong>Name:</strong> {{ $room->name }},
                                <strong>Type:</strong> {{ $room->roomType->type }}
                                @if ($room->items->count() > 0)
                                    <p class="section-title">Add Ons</p>
                                    <ul>
                                        @foreach ($room->items as $item)
                                            <li class="sub-item"><strong>Item:</strong> {{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($data['cottageAddOns']) && count($data['cottageAddOns']) > 0)
                <div class="card">
                    <p><strong>Cottages:</strong></p>
                    <ul>
                        @foreach ($data['cottageAddOns'] as $cottage)
                            <li class="card-item highlight">
                                <strong>Name:</strong> {{ $cottage->name }},
                                <strong>Type:</strong> {{ $cottage->cottageType->type }}
                                @if ($cottage->items->count() > 0)
                                    <p class="section-title">Add Ons</p>
                                    <ul>
                                        @foreach ($cottage->items as $item)
                                            <li class="sub-item"><strong>Item:</strong> {{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($data['otherAddOns']) && count($data['otherAddOns']) > 0)
                <div class="card">
                    <p><strong>Others:</strong></p>
                    <ul>
                        @foreach ($data['otherAddOns'] as $other)
                            <li class="card-item highlight">
                                <strong>Name:</strong> {{ $other->name }},
                                <strong>Type:</strong> {{ $other->otherType->type }}
                                @if ($other->items->count() > 0)
                                    <p class="section-title">Add Ons</p>
                                    <ul>
                                        @foreach ($other->items as $item)
                                            <li class="sub-item"><strong>Item:</strong> {{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p><strong>Total:</strong> ₱{{ $data['total'] }}</p>
            <p><strong>Initial Payment:</strong> ₱{{ $data['paid'] }}</p>
            <p><strong>Balance:</strong> ₱{{ $data['balance'] }}</p>
            <p><strong>Status:</strong> {{ $data['status'] }}</p>

            <p>If needed, the reservation can be rescheduled or cancelled <a href="{{ $data['rescheduleLink'] }}"
                    class="link">here</a>.</p>
        </div>
        <div class="footer">
            <p>Please take necessary action.</p>
        </div>
    </div>
</body>

</html>
