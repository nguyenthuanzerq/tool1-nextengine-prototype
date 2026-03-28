@extends('layouts.app')

@section('content')
<div class="max-w-2xl flex flex-col h-full">

    <div class="mb-6 flex-shrink-0 flex items-center gap-3">
        <a href="{{ route('btoc.users.index') }}"
           class="inline-flex items-center justify-center h-8 w-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                {{ $isCreate ? 'ユーザー追加' : 'ユーザー編集' }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $isCreate ? 'Create User' : 'Edit User' }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ $isCreate ? route('btoc.users.store') : route('btoc.users.update', $user->id) }}"
              method="POST">
            @csrf
            @if(!$isCreate)
                @method('PUT')
            @endif

            {{-- Name --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            @if($isCreate)
                {{-- Password (create only) --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">パスワード <span class="text-red-500">*</span></label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('password') border-red-400 @enderror"
                           placeholder="8文字以上">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">パスワード（確認） <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            @else
                {{-- is_active toggle (edit only, cannot deactivate yourself) --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer
                                  {{ $user->id === auth()->id() ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">有効</span>
                    </label>
                    @if($user->id === auth()->id())
                        <p class="mt-1 text-xs text-gray-400">自分自身のステータスは変更できません。</p>
                    @endif
                    @error('is_active')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 mt-6">
                <a href="{{ route('btoc.users.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                    キャンセル
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    {{ $isCreate ? '追加する' : '保存する' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
