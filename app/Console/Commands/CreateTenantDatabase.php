<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenants:db:create {id} {--migrate : Run tenants:migrate after creating the database}';
    protected $description = 'Create the physical database for a tenant and optionally run tenant migrations.';

    public function handle(): int
    {
        $id = (string) $this->argument('id');
        /** @var Tenant|null $tenant */
        $tenant = Tenant::find($id);

        if (! $tenant) {
            $this->error("Tenant '{$id}' not found.");
            return self::FAILURE;
        }

        $dbName = $tenant->database()->getName();
        $manager = $tenant->database()->manager();

        if ($manager->databaseExists($dbName)) {
            $this->warn("Database '{$dbName}' already exists.");
        } else {
            $tenant->database()->makeCredentials();
            $manager->createDatabase($tenant);
            $this->info("Database '{$dbName}' created for tenant '{$id}'.");
        }

        if ($this->option('migrate')) {
            $this->info('Running tenant migrations...');
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->getTenantKey()],
            ]);
            $this->line(rtrim(Artisan::output()));
        }

        return self::SUCCESS;
    }
}
