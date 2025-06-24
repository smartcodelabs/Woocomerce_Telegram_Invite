<?php
function generate_mail($link, $laufzeit, $kunde)
{
    // Base64-kodiertes Logo
    $base64_logo = 'YOUR LOGO DATA';

    return '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellbestätigung</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #c3aebd;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #EF7BA7;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #EF7BA7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="' . $base64_logo . '" alt="YOURSHOP Logo">
            </div>
            <h1>Bestellung abgeschlossen</h1>
        </div>
        <div class="content">
            <p>Hallo ' . $kunde . ',</p>
            <p>deine Bestellung bei YOURSHOP  ist erfolgreich abgeschlossen. Vielen Dank für deinen Einkauf!</p>
            <p>Hier ist der Link zu deinem bestellten Telegram-Kanal:</p>
            <a href="' . $link . '" class="btn">Zu deinem Telegram-Kanal</a>
            <p>Die Laufzeit deines Zugangs beträgt: ' . $laufzeit . ' Monate.</p>
        </div>
        <div class="footer">
           <p>Falls du noch Fragen hast, kontaktiere unseren Kundenservice unter <a href="mailto:admin@YOURSHOP.COM">admin@YOURSHOP.COM</a>.</p>
            <p>Vielen Dank, dass du bei <a href="https://YOURSHOP.COM">YOURSHOP</a> Shop eingekauft hast!</p>

        </div>
    </div>
</body>
</html>';
}

