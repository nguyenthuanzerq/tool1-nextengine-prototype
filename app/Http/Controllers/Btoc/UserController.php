<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('id', 'desc')->paginate(10);
        return view('btoc.users.index', ['users' => $users]);
    }

    public function create()
    {
        $isCreate = true;
        $user = new User();
        $roles = Role::all();
        $permissions = Permission::all(); // Lấy tất cả các quyền từ DB

        return view('btoc.users.save', [
            'isCreate' => $isCreate,
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions // Truyền ra view
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
            'permissions' => 'nullable|array' // Validate mảng quyền
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('12345678'),
            'is_active' => 1 
        ]);

        // 1. Gán chức danh (Role)
        $user->assignRole($request->role);
        
        // 2. Gán các quyền chi tiết được tích ở Checkbox (Direct Permissions)
        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('btoc.users.index')->with('success', 'Đã tạo tài khoản và cấp quyền thành công. Mật khẩu mặc định là: 12345678');
    }

    public function edit($id)
    {
        $isCreate = false;
        $user = User::findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();

        return view('btoc.users.save', [
            'isCreate' => $isCreate,
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|exists:roles,name',
            'permissions' => 'nullable|array'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // 1. Đồng bộ lại chức danh
        $user->syncRoles($request->role);
        
        // 2. Đồng bộ lại các quyền chi tiết (Cấp thêm hoặc thu hồi)
        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('btoc.users.index')->with('success', 'Cập nhật thông tin và quyền hạn thành công.');
    }

    public function toggleStatus($id)
    {
        if (auth()->id() == $id) {
            return redirect()->back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình!');
        }

        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', "Đã thay đổi trạng thái tài khoản của {$user->name}.");
    }
}