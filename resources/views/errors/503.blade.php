<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <title>MAINTENANCE MODE | Animest</title>
    <style>
        body {
            font-family: "Inter", system-ui;
            background-color: #000;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #ff6b6b;
        }

        p {
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #ffffff;
            border-bottom-color: #ff6b6b;
            border-radius: 50%;
            display: inline-block;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Animest is Currently Under Maintenance</h1>
        <p>We're working hard to improve your experience. Please check back soon!</p>
    </div>
</body>

</html>