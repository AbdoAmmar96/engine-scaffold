<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'editor', 'agent'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@bp-eg.com'],
            [
                'name'     => 'Amr Shalaby',
                'password' => 'password', // ⚠️ غيّرها فور أول دخول
            ]
        );

        $admin->syncRoles(['admin']);
    }
}
