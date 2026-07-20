<?php

namespace MuhammadMahediHasan\Df\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamic-forms:install {--force : Overwrite existing config and assets files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install laravel-dynamic-forms package and set up frontend dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Laravel Dynamic Forms...');

        // 1. Publish config and assets
        $this->call('vendor:publish', [
            '--provider' => 'MuhammadMahediHasan\\Df\\DynamicFormsServiceProvider',
            '--force' => (bool) $this->option('force'),
        ]);

        // 2. Read host package.json
        $packageJsonPath = base_path('package.json');

        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);

            $updated = false;

            // Ensure devDependencies or dependencies has vuedraggable
            $hasVuedraggable = isset($packageJson['dependencies']['vuedraggable']) ||
                               isset($packageJson['devDependencies']['vuedraggable']);

            if (!$hasVuedraggable) {
                if (!isset($packageJson['devDependencies'])) {
                    $packageJson['devDependencies'] = [];
                }
                $packageJson['devDependencies']['vuedraggable'] = '^4.1.0';
                $updated = true;
                $this->info('Added vuedraggable dependency to package.json.');
            }

            if ($updated) {
                file_put_contents(
                    $packageJsonPath,
                    json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
                );
                $this->warn('package.json updated. Please run "npm install && npm run dev" or "npm install && npm run build".');
            } else {
                $this->info('All frontend dependencies are already present in package.json.');
            }
        } else {
            $this->warn('package.json not found in the project root. Please manually install vuedraggable: npm install vuedraggable');
        }

        // 3. Seed FormInputs catalog
        if ($this->confirm('Would you like to seed the default Form Inputs catalog into your database?', true)) {
            $this->call('db:seed', [
                '--class' => 'MuhammadMahediHasan\\Df\\Database\\Seeders\\FormInputSeeder',
            ]);
            $this->info('Default Form Inputs catalog seeded successfully.');
        }

        $this->info('Laravel Dynamic Forms package installation complete!');

        return self::SUCCESS;
    }
}
