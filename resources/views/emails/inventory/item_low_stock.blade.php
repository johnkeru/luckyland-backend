<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Low Stock Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        p {
            margin: 0 0 10px 0;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }

        ul li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Item Low Stock Notification</h1>
        <p>Dear User,</p>
        <p>The following inventories are low in stock:</p>
        <ul>
            @foreach($data as $item)
            <li><strong>{{ $item->name }}:</strong> {{ $item->currentQuantity }} remaining</li>
            @endforeach
        </ul>
        <p>Please take necessary actions to restock these items.</p>
        <p>Regards,<br>Your LuckyLand Resort</p>
    </div>
</body>

</html>