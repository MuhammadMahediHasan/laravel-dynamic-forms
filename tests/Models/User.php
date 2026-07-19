<?php

namespace MuhammadMahediHasan\Df\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MuhammadMahediHasan\Df\Tests\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $guarded = [];

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
