@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            スタッフ管理 <span class="block text-sm text-gray-500 font-normal mt-1">Quản lý Nhân viên</span>
        </h1>
        
        @can('manage-user')
        <a href="{{ route('btoc.users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition shadow-sm">
            + 新規追加 (Thêm mới)
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm">
                    <th class="px-6 py-4 font-medium text-gray-700">Tên nhân viên</th>
                    <th class="px-6 py-4 font-medium text-gray-700">Email</th>
                    <th class="px-6 py-4 font-medium text-gray-700">Vai trò (Role)</th>
                    <th class="px-6 py-4 font-medium text-gray-700 text-center">Trạng thái</th>
                    <th class="px-6 py-4 font-medium text-gray-700 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            {{-- Hiển thị Role Badge --}}
                            @foreach($user->roles as $role)
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $role->name === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ strtoupper($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Hoạt động</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Đã khóa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-right space-x-2">
                            <a href="{{ route('btoc.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Sửa</a>
                            
                            {{-- Nút Khóa / Mở khóa --}}
                            @if(auth()->id() != $user->id)
                                <form action="{{ route('btoc.users.toggle_status', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-medium {{ $user->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $user->is_active ? 'Khóa' : 'Mở khóa' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">{{ $users->links() }}</div>
    </div>
</div>
@endsection