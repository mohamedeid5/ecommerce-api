<?php

namespace Tests\Traits;

use Spatie\Permission\Models\Role;

trait CreatesRoles
{
    protected function createRoles(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'sanctum']);
    }
}
