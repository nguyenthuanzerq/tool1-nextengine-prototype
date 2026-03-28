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

    {{-- Mobile sidebar overlay --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"
         onclick="closeSidebar()"></div>

    <div class="h-screen flex overflow-hidden">

        @include('layouts.sidebar')

        <main class="flex-1 md:ml-64 h-screen flex flex-col overflow-hidden">

            {{-- Mobile top bar --}}
            <div class="md:hidden flex-shrink-0 flex items-center justify-between px-4 py-3 bg-white border-b border-gray-200 z-20">
                <button onclick="toggleSidebar()"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                    <svg id="icon-menu" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="icon-close" viewBox="0 0 24 24" class="h-5 w-5 hidden" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
                <span class="text-sm font-bold text-gray-900">NextEngine管理</span>
                <div class="w-9"></div>{{-- spacer to center title --}}
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4 md:px-6 md:py-6">
                @yield('content')
            </div>

        </main>

    </div>

    <script>
        function toggleSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebar-overlay');
            const iconMenu  = document.getElementById('icon-menu');
            const iconClose = document.getElementById('icon-close');
            const isOpen   = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                iconMenu.classList.remove('hidden');
                iconClose.classList.add('hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                iconMenu.classList.add('hidden');
                iconClose.classList.remove('hidden');
            }
        }

        function closeSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebar-overlay');
            const iconMenu  = document.getElementById('icon-menu');
            const iconClose = document.getElementById('icon-close');

            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            iconMenu.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    </script>

</body>

</html>
