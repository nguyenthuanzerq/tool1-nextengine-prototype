<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - NextEngine Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-xl border border-gray-200 p-8 shadow-lg">
            
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">
                    NextEngine 管理
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    BtoC NextEngine Management Tool
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email 
                    </label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="admin@example.com"
                           required 
                           autofocus>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        パスワード
                    </label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           placeholder="••••••••"
                           required>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-600">
                            私を覚えてますか
                        </label>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-[#1e293b] hover:bg-black text-white font-semibold py-3 rounded-lg transition-colors duration-200 shadow-md">
                    ログイン
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-widest">
                    © 2026 NextEngine Tool
                </p>
            </div>
        </div>
    </div>

</body>
</html>