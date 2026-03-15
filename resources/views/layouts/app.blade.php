<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NextEngine管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex">

        @include('layouts.sidebar')

        <main class="flex-1 ml-72">
            @include('layouts.header')
            <div class="px-6 py-6">
                @yield('content')
            </div>
        </main>

    </div>
</body>

</html>
