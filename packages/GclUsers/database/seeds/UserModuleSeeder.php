<?php

namespace {

    use Illuminate\Database\Seeder;
    use Gcl\GclUsers\Models\Role;
    use Gcl\GclUsers\Models\NodePermission;
    use Gcl\GclUsers\Models\PermissionRole;

    class UserModuleSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            // create admin user
            $root = factory(App\User::class)->create([
                'name'      => 'Administrator',
                'email'     => 'admin@example.com',
                'password'  => bcrypt('123456'),
                'username'  => 'admin',
                'location'  => 'Da Nang',
                'country'   => 'Viet Nam',
                'biography' => 'Dev',
                'occupation'=> 'Dev',
                'website'   => 'greenglobal.vn',
                'image'     => 'avatar.jpg',
            ]);

            // create default roles
            $admin = new Role;
            $admin->name         = 'admin';
            $admin->display_name = 'Administrator';
            $admin->description  = 'User is allowed to manage all system.';
            $admin->active       = 1;
            $admin->save();

            // attach roles
            $root->attachRole($admin);

            // create root permission
            $admin = new NodePermission;
            $admin->name         = 'Root';
            $admin->display_name = 'Root permission';
            $admin->description  = 'The root.';
            $admin->save();

            // create all permission to admin
            $root = new PermissionRole;
            $root->permission_id = 1;
            $root->role_id = 1;
            $root->status = 1;
            $root->save();
        }
    }

}
