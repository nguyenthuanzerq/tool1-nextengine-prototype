@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] flex flex-col h-full">

    @if(session('success'))
        <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex-shrink-0 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">ユーザー管理</h1>
            <p class="text-sm text-gray-500 mt-0.5">User Management</p>
        </div>
        <a href="{{ route('btoc.users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
            </svg>
            新規追加
        </a>
    </div>

    <div class="flex-1 flex flex-col min-h-0 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前 / Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メールアドレス</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">登録日</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            @if($user->id === auth()->id())
                                <div class="text-xs text-blue-500 mt-0.5">自分</div>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-600 font-mono">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-4 text-sm">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                    有効
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span>
                                    無効
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-xs text-gray-500">
                            {{ $user->created_at?->format('Y-m-d') }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                <a href="{{ route('btoc.users.edit', $user->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                                    編集
                                </a>

                                @if($user->id === auth()->id())
                                    <a href="{{ route('btoc.users.change_password', $user->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                                        パスワード変更
                                    </a>
                                @endif

                                @if($user->id !== auth()->id())
                                    <form action="{{ route('btoc.users.destroy', $user->id) }}" method="POST"
                                          onsubmit="return confirm('このユーザーを削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-red-200 text-red-600 bg-white hover:bg-red-50 transition">
                                            削除
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                            ユーザーが見つかりません。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} / {{ $users->total() }} 件</span>
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
