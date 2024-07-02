<?php

use App\Permission;
use App\RolePermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataForRolesForVideoModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //permissions
        $permission_admin = new Permission([
            'name' => 'CED Videos',
            'description' => 'Create, edit and delete videos.',
            'role_id' => 1
        ]);
        $permission_admin->save();

        $permission_artist = new Permission([
            'name' => 'CED Videos(artist)',
            'description' => 'Create, edit and delete videos.',
            'role_id' => 2
        ]);
        $permission_artist->save();

        //role_permission
        $role_permission_admin = new RolePermission();
        $role_permission_admin->permission_id = $permission_admin->id;
        $role_permission_admin->role_id = 1;
        $role_permission_admin->save();

        $role_permission_artist = new RolePermission();
        $role_permission_artist->permission_id = $permission_artist->id;
        $role_permission_artist->role_id = 2;
        $role_permission_artist->save();


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
