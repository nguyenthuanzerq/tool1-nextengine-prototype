<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NextEngine管理</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">

    <!-- Sidebar (fixed) -->
    <aside class="fixed inset-y-0 left-0 w-72 bg-white border-r border-gray-200">
      <div class="px-6 py-5 text-2xl font-extrabold">
        NextEngine管理
      </div>

      <nav class="px-4 py-2 space-y-2 text-sm">

        <!-- Dashboard -->
        <a href="{{ route('btoc.dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.dashboard')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.dashboard') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 13h6V4H4v9zm10 7h6V11h-6v9zM4 20h6v-5H4v5zm10-9h6V4h-6v7z"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">ダッシュボード</div>
            <div class="text-xs text-gray-500">Bảng điều khiển</div>
          </div>
        </a>

        <!-- Shop -->
        <a href="{{ route('btoc.shops') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.shops*')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.shops*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-8h6v8"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">ショップ管理</div>
            <div class="text-xs text-gray-500">Quản lý Shop</div>
          </div>
        </a>

        <!-- 管理画面 -->
        <a href="{{ route('btoc.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.index*')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.index*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 6h15l-2 9H8L6 6z"/><path d="M6 6H3"/><path d="M8 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/><path d="M18 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">管理画面</div>
            <div class="text-xs text-gray-500">Quản lý đơn hàng</div>
          </div>
        </a>

        <!-- Inventory -->
        <a href="{{ route('btoc.inventory') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.inventory*')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.inventory*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">在庫・出庫</div>
            <div class="text-xs text-gray-500">Tồn kho &amp; Xuất hàng</div>
          </div>
        </a>

        <!-- Email -->
        <a href="{{ route('btoc.email.settings') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.email.settings*')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.email.settings*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16v16H4z"/><path d="M22 6l-10 7L2 6"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">発送メール設定</div>
            <div class="text-xs text-gray-500">Cấu hình email gửi hàng</div>
          </div>
        </a>

        <!-- Sync -->
        <a href="{{ route('btoc.sync.history') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl
           {{ request()->routeIs('btoc.sync.*')
                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                : 'hover:bg-gray-50' }}">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg
          {{ request()->routeIs('btoc.sync.*') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 8v4l3 3"/><path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0z"/>
            </svg>
          </span>
          <div class="leading-tight">
            <div class="font-semibold">同期履歴・ログ</div>
            <div class="text-xs text-gray-500">Lịch sử đồng bộ &amp; Log</div>
          </div>
        </a>
        
        <div class="mt-auto pt-4 border-t border-gray-100">
          <a href="#" 
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
             class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-colors">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-600">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
              </svg>
            </span>
            <div class="leading-tight">
              <div class="font-semibold">ログアウト</div>
              <div class="text-xs text-red-400">Đăng xuất</div>
            </div>
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
          </form>
        </div>
      </nav>

      
    </aside>

    <!-- Main -->
    <main class="flex-1 ml-72">
      <div class="h-16 bg-white border-b border-gray-200 flex items-center px-6">
        <div class="flex items-center gap-4">
          <select class="h-10 px-4 rounded-lg border border-gray-200 text-sm bg-white">
            <option>すべてのショップ</option>
          </select>

          <form method="POST" action="{{ route('btoc.manualSync') }}">
            @csrf
            <button type="submit" class="h-10 px-5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
              手動同期
            </button>
          </form>
        </div>

        <div class="ml-auto flex items-center gap-2 text-sm">
          <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
          <span class="text-green-700 font-semibold">自動同期: ON</span>
          <span class="text-gray-500 text-xs">(Tự động đồng bộ)</span>
        </div>
      </div>

      <div class="px-6 py-6">
        @yield('content')
      </div>
    </main>
  </div>
</body>
</html>