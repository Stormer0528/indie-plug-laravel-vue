<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'role_permission';
}
