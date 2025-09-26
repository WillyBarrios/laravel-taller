<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\Usuario;

class CreateTenantUser extends Command
{
    protected $signature = 'tenants:user:create {tenant} {email} {password} {nombre} {rol=admin}';
    protected $description = 'Create a user inside a specific tenant database';

    public function handle(): int
    {
        $tenantId = (string) $this->argument('tenant');
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');
        $nombre = (string) $this->argument('nombre');
        $rol = (string) $this->argument('rol');

        /** @var Tenant|null $tenant */
        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            $this->error("Tenant '{$tenantId}' not found");
            return self::FAILURE;
        }

        // Initialize tenancy for this tenant
        tenancy()->initialize($tenant);

        try {
            if (Usuario::where('email', $email)->exists()) {
                $this->warn("User with email '{$email}' already exists in tenant '{$tenantId}'.");
            } else {
                Usuario::create([
                    'nombre' => $nombre,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'rol' => $rol,
                ]);
                $this->info("User '{$email}' created in tenant '{$tenantId}'.");
            }
        } finally {
            tenancy()->end();
        }

        return self::SUCCESS;
    }
}
