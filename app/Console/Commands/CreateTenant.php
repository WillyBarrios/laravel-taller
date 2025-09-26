<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class CreateTenant extends Command
{
    protected $signature = 'tenants:create {id} {--domain=}';
    protected $description = 'Create a tenant with optional domain';

    public function handle(): int
    {
        $id = $this->argument('id');
        $domain = $this->option('domain');

        if (Tenant::find($id)) {
            $this->warn("Tenant '{$id}' already exists.");
            return self::SUCCESS;
        }

        $tenant = Tenant::create(['id' => $id]);
        if ($domain) {
            $tenant->domains()->create(['domain' => $domain]);
        }

        $this->info("Tenant '{$id}' created" . ($domain ? " with domain '{$domain}'" : ''));
        return self::SUCCESS;
    }
}
