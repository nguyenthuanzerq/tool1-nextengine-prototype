@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto">
        <a href="{{ route('btoc.users.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
            ← Quay lại
        </a>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-2xl font-semibold text-gray-900 mb-6">
            {{ $isCreate ? 'Thêm Nhân Viên Mới' : 'Chỉnh Sửa Thông Tin' }}
        </h1>

        <form method="POST" action="{{ $isCreate ? route('btoc.users.store') : route('btoc.users.update', $user->id) }}">
            @csrf
            @if (!$isCreate)
                @method('PUT')
            @endif

            {{-- KHỐI CẤU HÌNH PHÂN QUYỀN ĐỘNG --}}
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-6 bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Thông tin cơ bản</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên hiển thị <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ Email <span
                                class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò (Role) <span
                                class="text-red-500">*</span></label>
                        <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            <option value="">-- Chọn vai trò --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('role') == $role->name || (!$isCreate && $user->hasRole($role->name)) ? 'selected' : '' }}>
                                    {{ strtoupper($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isCreate)
                            <p class="text-xs text-gray-500 mt-2">Mật khẩu mặc định sau khi tạo là: <strong>12345678</strong>.
                                Nhân viên có thể tự đổi sau khi đăng nhập.</p>
                        @endif
                    </div>
                </div>

                <div class="col-span-6 bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Cấp quyền chi tiết (Dynamic Permissions)
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">Tích chọn các thao tác mà nhân viên này được phép thực hiện trong hệ
                        thống.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            // Mảng map tên quyền tiếng Anh sang tiếng Việt cho thân thiện
                            $permissionLabels = [
                                'view-order' => 'Xem danh sách Đơn hàng',
                                'create-order' => 'Thêm mới Đơn hàng',
                                'edit-order' => 'Chỉnh sửa Đơn hàng',
                                'delete-order' => 'Xóa Đơn hàng',
                                'view-shop' => 'Xem danh sách Shop',
                                'manage-shop' => 'Quản lý Shop (Thêm/Sửa/Xóa)',
                                'manage-user' => 'Quản lý Nhân sự (Superadmin)',
                            ];
                        @endphp

                        @foreach ($permissions as $permission)
                            <label
                                class="inline-flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-blue-50 transition">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-5 h-5"
                                    {{-- Logic tự động tích nếu User đang có quyền này --}}
                                    {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) ||
                                    (!$isCreate && $user->hasDirectPermission($permission->name))
                                        ? 'checked'
                                        : '' }}>
                                <span class="ml-3 text-sm text-gray-700 font-medium">
                                    {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                @can('manage-user')
                    <a href="{{ route('btoc.users.index') }}"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Hủy</a>
                @endcan

                @can('manage-user')
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Lưu thông
                        tin</button>
                @endcan
            </div>
        </form>
    </div>
@endsection
