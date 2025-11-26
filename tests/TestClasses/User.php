<?php

declare(strict_types=1);

namespace Akira\Debugger\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $guarded = [];

    public $timestamps = false;
}
