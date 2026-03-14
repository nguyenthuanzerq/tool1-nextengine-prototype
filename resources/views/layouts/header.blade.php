<div class="h-16 bg-white border-b border-gray-200 flex items-center px-6">
    <div class="flex items-center gap-4">
        <select class="h-10 px-4 rounded-lg border border-gray-200 text-sm bg-white">
            <option>すべてのショップ</option>
        </select>

        <form method="POST" action="#">
            @csrf
            <button type="submit"
                class="h-10 px-5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
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
