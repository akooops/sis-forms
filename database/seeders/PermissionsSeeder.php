<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Auth & Dashboard
            'admin.dashboard.index',
            
            // Permissions Management
            'admin.permissions.index',
            'admin.permissions.show',
            
            // Roles Management
            'admin.roles.index',
            'admin.roles.store',
            'admin.roles.show',
            'admin.roles.update',
            'admin.roles.destroy',
            
            // Users Management
            'admin.users.index',
            'admin.users.store',
            'admin.users.show',
            'admin.users.update',
            'admin.users.destroy',
            
            // Notifications Management
            'admin.notifications.index',
            'admin.notifications.unread-count',
            'admin.notifications.mark-read',
            'admin.notifications.mark-all-read',

            // Submissions Management
            'admin.submissions.index',
            'admin.submissions.destroy',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Create default roles
        $superAdminRole = Role::updateOrCreate(
            ['name' => 'Super Admin'],
            ['is_default' => false]
        );

        $adminRole = Role::updateOrCreate(
            ['name' => 'Admin'],
            ['is_default' => false]
        );

        $editorRole = Role::updateOrCreate(
            ['name' => 'Editor'],
            ['is_default' => true]
        );

        $viewerRole = Role::updateOrCreate(
            ['name' => 'Viewer'],
            ['is_default' => false]
        );

        // Assign all permissions to Super Admin
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Assign most permissions to Admin (excluding user/role management)
        $adminPermissions = Permission::whereNotIn('name', [
            'admin.users.store',
            'admin.users.update', 
            'admin.users.destroy',
            'admin.roles.store',
            'admin.roles.update',
            'admin.roles.destroy',
            'admin.permissions.show'
        ])->get();
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // Assign content management permissions to Editor
        $editorPermissions = Permission::where('name', 'like', '%articles%')
            ->orWhere('name', 'like', '%pages%')
            ->orWhere('name', 'like', '%albums%')
            ->orWhere('name', 'like', '%events%')
            ->orWhere('name', 'like', '%media%')
            ->orWhere('name', 'like', '%banners%')
            ->orWhere('name', 'like', '%documents%')
            ->orWhere('name', 'like', '%files%')
            ->get();
            
        $editorRole->permissions()->sync($editorPermissions->pluck('id'));

        // Assign view-only permissions to Viewer
        $viewerPermissions = Permission::where('name', 'like', '%.index')
            ->orWhere('name', 'like', '%.show')
            ->orWhere('name', 'like', '%inquiries%')
            ->orWhere('name', 'like', '%contact-submissions%')
            ->orWhere('name', 'like', '%visit-bookings%')
            ->orWhere('name', 'like', '%job-applications%')
            ->get();
        $viewerRole->permissions()->sync($viewerPermissions->pluck('id'));
    }
}
