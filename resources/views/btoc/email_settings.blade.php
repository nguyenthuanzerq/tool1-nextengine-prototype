@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        発送メール設定
        <span class="block text-sm text-gray-500 font-normal mt-1">
            Cấu hình email gửi hàng
        </span>
    </h1>

    <form method="POST" action="#">
        @csrf

        <div class="grid grid-cols-2 gap-6">

            {{-- LEFT PANEL --}}
            <div class="space-y-6">

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold mb-4">
                        メールテンプレート
                        <span class="block text-sm text-gray-500 font-normal mt-1">
                            Email Template
                        </span>
                    </h2>

                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                テンプレート名
                            </label>
                            <input type="text"
                                   name="template_name"
                                   value="{{ $templateName }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                件名
                            </label>
                            <input type="text"
                                   name="subject"
                                   value="{{ $subject }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                本文
                            </label>
                            <textarea name="body"
                                      rows="16"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm">{{ $body }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- VARIABLES --}}
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">
                        利用可能な変数
                    </h3>

                    <div class="space-y-1 text-sm text-blue-900">
                        <code>{customer_name}</code>
                        <code>{order_id}</code>
                        <code>{tracking_number}</code>
                        <code>{carrier}</code>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            name="preview"
                            value="1"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        プレビュー (Xem trước)
                    </button>

                    <button type="submit"
                            name="save"
                            value="1"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                        保存 (Lưu)
                    </button>
                </div>

            </div>

            {{-- RIGHT PANEL --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold mb-4">
                    プレビュー
                </h2>

                @if($showPreview)

                    <div class="border border-gray-300 rounded-lg">

                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-300">
                            <div class="text-xs text-gray-600 mb-1">件名:</div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $previewSubject }}
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="whitespace-pre-wrap text-sm text-gray-900">
                                {{ $previewBody }}
                            </div>
                        </div>

                    </div>

                @else

                    <div class="flex items-center justify-center h-[400px] border-2 border-dashed border-gray-300 rounded-lg text-gray-500 text-sm">
                        プレビューボタンをクリックしてください
                    </div>

                @endif
            </div>

        </div>
    </form>
</div>
@endsection