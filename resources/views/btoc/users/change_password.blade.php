@extends('layouts.app')

@section('content')
<div class="max-w-lg flex flex-col h-full">

    <div class="mb-6 flex-shrink-0 flex items-center gap-3">
        <a href="{{ route('btoc.users.index') }}"
           class="inline-flex items-center justify-center h-8 w-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">パスワード変更</h1>
            <p class="text-sm text-gray-500 mt-0.5">Change Password — {{ $user->name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('btoc.users.update_password', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">現在のパスワード <span class="text-red-500">*</span></label>
                <input type="password" name="current_password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('current_password') border-red-400 @enderror">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード <span class="text-red-500">*</span></label>
                <input type="password" name="password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('password') border-red-400 @enderror"
                       placeholder="8文字以上">
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード（確認） <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 mt-6">
                <a href="{{ route('btoc.users.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                    キャンセル
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    変更する
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
