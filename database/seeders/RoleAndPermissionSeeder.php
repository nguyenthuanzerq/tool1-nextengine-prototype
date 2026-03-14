<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset cache của Spatie để tránh lỗi
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Tạo danh sách các Quyền (Permissions) cần thiết
        $permissions = [
            'view-order', 'create-order', 'edit-order', 'delete-order', // Quyền Order
            'view-shop', 'manage-shop',                                 // Quyền Shop
            'manage-user'                                               // Quyền User (Chỉ Superadmin)
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Tạo Role
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // 4. Gán quyền cho Role
        // Superadmin lấy TẤT CẢ quyền
        $superAdminRole->givePermissionTo(Permission::all());
        
        // Admin mặc định chỉ được thao tác Order và xem Shop (không được xóa order, không quản lý shop/user)
        $adminRole->givePermissionTo([
            'view-order', 'create-order', 'edit-order', 'view-shop'
        ]);

        // 5. Nâng cấp tài khoản hiện tại của bạn thành Superadmin
        // Dựa vào DB của bạn, tài khoản id = 1 đang là admin@gmail.com
        $user = User::find(1);
        
        if ($user) {
            $user->update(['name' => 'Super Admin', 'is_active' => 1]);
            $user->assignRole($superAdminRole); // Trao ấn kiếm cho user này
        } else {
            // Đề phòng user số 1 bị xóa, tạo lại luôn
            $user = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'is_active' => 1
            ]);
            $user->assignRole($superAdminRole);
        }
    }
}