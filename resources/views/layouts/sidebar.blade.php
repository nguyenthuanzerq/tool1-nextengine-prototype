<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col
              -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">

    <div class="px-5 py-4 border-b border-gray-100 flex-shrink-0">
        <div class="text-base font-bold text-gray-900">NextEngine管理</div>
        <div class="text-xs text-gray-400 mt-0.5">Admin Panel</div>
    </div>

    <nav class="px-3 py-3 space-y-1 text-sm flex-1 overflow-y-auto">

        <a href="{{ route('btoc.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.dashboard') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">ダッシュボード</div>
                <div class="text-xs text-gray-400">Dashboard</div>
            </div>
        </a>

        <a href="{{ route('btoc.shop.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.shop.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.shop.*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-8h6v8" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">ショップ管理</div>
                <div class="text-xs text-gray-400">Shop Management</div>
            </div>
        </a>

        <a href="{{ route('btoc.orders.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.orders.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.orders.*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                    <path d="M9 12h6M9 16h4" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">受注管理</div>
                <div class="text-xs text-gray-400">Order Management</div>
            </div>
        </a>

        <a href="{{ route('btoc.inventory') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.inventory*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.inventory*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    <path d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">在庫管理</div>
                <div class="text-xs text-gray-400">Inventory</div>
            </div>
        </a>

        <a href="{{ route('btoc.sync.history') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.sync.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.sync.*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 8v4l3 3" />
                    <path d="M3.05 11a9 9 0 1 0 .5-3" />
                    <path d="M3 4v4h4" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">同期履歴・ログ</div>
                <div class="text-xs text-gray-400">Sync History &amp; Log</div>
            </div>
        </a>

        <a href="{{ route('btoc.users.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
                {{ request()->routeIs('btoc.users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0
                {{ request()->routeIs('btoc.users.*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </span>
            <div class="leading-tight min-w-0">
                <div class="font-semibold text-sm truncate">ユーザー管理</div>
                <div class="text-xs text-gray-400">User Management</div>
            </div>
        </a>

    </nav>

    <div class="flex-shrink-0 p-3 border-t border-gray-100">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-500 flex-shrink-0">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                </svg>
            </span>
            <div class="leading-tight">
                <div class="font-semibold text-sm">ログアウト</div>
                <div class="text-xs text-red-400">Logout</div>
            </div>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

</aside>
