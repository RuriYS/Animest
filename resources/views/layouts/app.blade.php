<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        #debug {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: transparent;
            color: #00ff00;
            padding: 8px;
            font-family: monospace;
            font-size: 10px;
            z-index: 9999;
            pointer-events: none;
        }
    </style>
</head>

<body style="background-color: black;">
    @if(app()->environment('local', 'development'))
        <div id="debug">
            {{ $envInfo ?? 'Environment info not available' }}
        </div>
    @endif

    <div id="root">
        <noscript style="color: white;">
            You need Javascript support for this to work :3
        </noscript>
    </div>
    @vite(['resources/css/app.css', 'resources/src/App.tsx'])
</body>

</html>