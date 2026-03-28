<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('btoc.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('btoc.users.save', [
            'isCreate' => true,
            'user'     => new User,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'     => '名前を入力してください。',
            'email.required'    => 'メールアドレスを入力してください。',
            'email.unique'      => 'このメールアドレスは既に使用されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed'=> 'パスワードが一致しません。',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('btoc.users.index')->with('success', 'ユーザーを追加しました。');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('btoc.users.save', [
            'isCreate' => false,
            'user'     => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required'  => '名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.unique'   => 'このメールアドレスは既に使用されています。',
        ]);

        // Prevent deactivating yourself
        if ($user->id === Auth::id() && isset($validated['is_active']) && ! $validated['is_active']) {
            return back()->withErrors(['is_active' => '自分自身を無効化することはできません。']);
        }

        $user->update($validated);

        return redirect()->route('btoc.users.index')->with('success', 'ユーザー情報を更新しました。');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', '自分自身を削除することはできません。');
        }

        if (User::count() <= 1) {
            return back()->with('error', 'ユーザーが1人しかいないため削除できません。');
        }

        $user->delete();

        return redirect()->route('btoc.users.index')->with('success', 'ユーザーを削除しました。');
    }

    public function changePassword($id)
    {
        $user = User::findOrFail($id);

        return view('btoc.users.change_password', ['user' => $user]);
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => '現在のパスワードを入力してください。',
            'password.required'         => '新しいパスワードを入力してください。',
            'password.confirmed'        => '新しいパスワードが一致しません。',
        ]);

        // Only the user themselves (or future admin logic) should change a password.
        // For now: only the authenticated user can change their own password.
        if ($user->id !== Auth::id()) {
            abort(403);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('btoc.users.index')->with('success', 'パスワードを変更しました。');
    }
}
