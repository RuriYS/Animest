<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        #debug {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            right: 0;
            background: rgba(0, 0, 0, 30%);
            color: #00ff00;
            padding: 8px;
            font-family: monospace;
            font-weight: 800;
            font-size: 0.6rem;
            text-shadow: 2px 2px #000;
            z-index: 9999;
            pointer-events: none;
            border: 0px solid #000;
            border-radius: 10px;
            min-width: 180px;
        }

        #debug span:first-child {
            text-align: center;
            display: block;
            margin-bottom: 8px;
            color: #fc6087;
        }

        .debug-line {
            display: flex;
            justify-content: space-between;
            white-space: nowrap;
            padding: 0 10px;
        }
    </style>
</head>

<body style="background-color: black;">
    @if(app()->environment('local', 'development'))
        <div id="debug">
            <span>[Developer mode]</span>
            <span class="debug-line">
                <span>Environment:</span>
                <span>{{ $env }}</span>
            </span>
            <span class="debug-line">
                <span>Database:</span>
                <span>{{ $db }}</span>
            </span>
        </div>
    @endif

    <div id="root"></div>
    @vite(['resources/css/app.css', 'resources/src/App.tsx'])
</body>

</html>