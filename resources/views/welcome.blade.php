<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>API</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            @keyframes vibrate {
                0% { transform: translateX(1px); }
                25% { transform: translateX(-0.1px); }
                50% { transform: translateX(0.5px); }
                75% { transform: translateX(-0.1px); }
                100% { transform: translateX(1px); }
            }
    
            .vibrate {
                animation: vibrate 0.5s infinite;
            }
        </style>
    </head>
    <body class="flex items-center justify-center min-h-screen bg-slate-900">
        <div class="text-center p-8 flex justify-center items-center">
            <p class="text-2xl font-bold text-white">API is running </p> 
            <h1 class="vibrate text-2xl">💡</h1>
        </div>
    </body>
</html>
