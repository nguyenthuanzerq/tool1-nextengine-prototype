@extends('layouts.app')

@section('content')
    {{-- Mở rộng toàn màn hình, giảm padding --}}
    <div class="p-2 sm:p-4 w-full mx-auto">

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tiêu đề & Nút thao tác --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-4">
            <h1 class="text-xl font-semibold text-gray-900">
                ECプラットフォーム管理
                <span class="block text-xs text-gray-500 font-normal mt-0.5">Quản lý Nền tảng EC</span>
            </h1>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Nút Xóa hàng loạt --}}
                <button type="button" onclick="submitBulkAction('{{ route('btoc.ec-platforms.destroy', 0) }}', 'DELETE')"
                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded text-[11px] font-medium transition shadow-sm whitespace-nowrap">
                    一括削除 (Xóa hàng loạt)
                </button>
                {{-- Nút Thêm mới --}}
                <a href="{{ route('btoc.ec-platforms.create') }}" 
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium transition shadow-sm whitespace-nowrap">
                    + 新規追加 (Thêm mới)
                </a>
            </div>
        </div>

        {{-- Bảng dữ liệu (EC Platforms List) --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-sm font-semibold text-gray-800">プラットフォームリスト (Danh sách nền tảng)</h2>
                <span class="text-xs text-gray-500">Tổng cộng: {{ $platforms->total() ?? 0 }} nền tảng</span>
            </div>

            {{-- Form xử lý thao tác hàng loạt --}}
            <form id="bulk-action-form" method="POST" action="">
                @csrf
                @method('DELETE') {{-- Mặc định là DELETE cho nút xóa hàng loạt --}}
                
                <div class="overflow-x-auto relative sm:rounded-b-lg">
                    <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 text-[12px] leading-tight hover:bg-gray-100">
                                {{-- Ghim TRÁI: Checkbox --}}
                                <th class="px-2 py-2 text-center w-8 sticky left-0 z-20 bg-gray-100 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                    <input type="checkbox" id="check-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                
                                <th class="px-3 py-2.5 font-medium text-gray-700">Mã (Code)<br><span class="text-[10px] font-normal text-gray-500">Mã nền tảng</span></th>
                                <th class="px-3 py-2.5 font-medium text-gray-700">プラットフォーム名<br><span class="text-[10px] font-normal text-gray-500">Tên nền tảng</span></th>
                                <th class="px-3 py-2.5 font-medium text-gray-700">認証タイプ<br><span class="text-[10px] font-normal text-gray-500">Loại xác thực</span></th>
                                <th class="px-3 py-2.5 font-medium text-gray-700">Website</th>
                                <th class="px-3 py-2.5 font-medium text-gray-700">ショップ数<br><span class="text-[10px] font-normal text-gray-500">Số lượng shop</span></th>
                                <th class="px-3 py-2.5 font-medium text-gray-700">ステータス<br><span class="text-[10px] font-normal text-gray-500">Trạng thái</span></th>
                                
                                {{-- Ghim PHẢI: Hành động --}}
                                <th class="px-3 py-2.5 font-medium text-gray-700 sticky right-0 z-20 bg-gray-100 border-l border-gray-200 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.05)] text-center w-24">
                                    操作<br><span class="text-[10px] font-normal text-gray-500">Hành động</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($platforms ?? [] as $p)
                                <tr class="bg-white hover:bg-blue-50 transition group text-[12px]">
                                    
                                    {{-- Ghim TRÁI: Checkbox --}}
                                    <td class="px-2 py-1.5 text-center sticky left-0 z-10 bg-inherit border-r border-gray-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    
                                    <td class="px-3 py-1.5 font-medium text-gray-900 truncate max-w-[100px]" title="{{ $p->code }}">
                                        {{ $p->code }}
                                    </td>
                                    <td class="px-3 py-1.5 text-gray-900 truncate max-w-[150px]" title="{{ $p->name }}">
                                        {{ $p->name }}
                                    </td>
                                    <td class="px-3 py-1.5 text-gray-600 whitespace-nowrap">
                                        <span class="bg-gray-100 px-1.5 py-0.5 rounded text-[10px]">{{ $p->auth_type }}</span>
                                    </td>
                                    <td class="px-3 py-1.5 text-blue-600 truncate max-w-[150px]" title="{{ $p->website_url }}">
                                        @if($p->website_url)
                                            <a href="{{ $p->website_url }}" target="_blank" class="hover:underline">{{ $p->website_url }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    {{-- Giả lập số lượng shop (sau này sẽ dùng relationship) --}}
                                    <td class="px-3 py-1.5 text-gray-900 font-medium">0</td>
                                    
                                    <td class="px-3 py-1.5 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $p->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $p->is_active ? '稼働中 (Hoạt động)' : '停止中 (Đã tắt)' }}
                                        </span>
                                    </td>

                                    {{-- Ghim PHẢI: Hành động --}}
                                    <td class="px-3 py-1.5 text-gray-600 sticky right-0 z-10 bg-inherit border-l border-gray-100 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('btoc.ec-platforms.edit', $p->id) }}" 
                                               class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded text-[11px] font-medium transition whitespace-nowrap">
                                                編集
                                            </a>
                                            <button type="button" 
                                                    onclick="deleteSingle('{{ route('btoc.ec-platforms.destroy', $p->id) }}')"
                                                    class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded text-[11px] font-medium transition whitespace-nowrap">
                                                削除
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-12 text-center text-sm text-gray-500">条件に一致する受注はありません。<br>(Không tìm thấy đơn hàng nào phù hợp)</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            
            {{-- Form xóa dùng chung (cho xóa đơn lẻ) --}}
            <form id="master-delete-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            
            {{-- Phân trang --}}
            @if (isset($platforms) && $platforms->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $platforms->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Script xử lý UI (Checkall, Bulk Actions) --}}
    <script>
        // Check all checkboxes
        document.getElementById('check-all').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Xử lý thao tác hàng loạt (Xóa)
        function submitBulkAction(actionUrl, method = 'DELETE') {
            let form = document.getElementById('bulk-action-form');
            let checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                alert('対象のプラットフォームを選択してください。(Vui lòng chọn ít nhất một nền tảng)');
                return;
            }
            
            if (method === 'DELETE' && !confirm('選択した nền tảng を削除してもよろしいですか？\n(Bạn có chắc chắn muốn xóa các nền tảng đã chọn không?)')) {
                return;
            }
            
            form.action = actionUrl;
            // Nếu route cần method đặc biệt (PUT/DELETE) thì Laravel cần thẻ hidden
            let methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = method;
            
            form.submit();
        }

        // Xử lý xóa đơn lẻ
        function deleteSingle(actionUrl) {
            if (confirm('この nền tảng を削除してもよろしいですか？\n(Bạn có chắc chắn muốn xóa nền tảng này không?)')) {
                let form = document.getElementById('master-delete-form');
                form.action = actionUrl;
                form.submit();
            }
        }
    </script>
@endsection