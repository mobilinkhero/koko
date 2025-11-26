<?php

namespace Corbital\ModuleManager\Services;

use Corbital\ModuleManager\Facades\SemVer;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModuleManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Modules collection.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * Module instances cache.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Create a new Module Manager instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get all modules.
     *
     * @return array
     */
    public function all()
    {
        if (empty($this->modules)) {
            $this->scan();
        }

        return $this->modules;
    }

    /**
     * Get active modules.
     *
     * @return array
     */
    public function active()
    {
        return array_filter($this->all(), function ($module) {
            return $module['active'];
        });
    }

    /**
     * Get a specific module.
     *
     * @param  string  $name
     * @return array|null
     */
    public function get($name)
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Check if a module exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->all()[$name]);
    }

    /**
     * Check if a module is active.
     *
     * @param  string  $name
     * @return bool
     */
    public function isActive($name)
    {
        $module = $this->get($name);

        return $module ? $module['active'] : false;
    }

    /**
     * Activate a module.
     *
     * @param  string  $name
     * @param  array  $activationStack  Track modules being activated to prevent circular dependencies
     * @param  array  $validationData  Data for validation (e.g., Envato credentials)
     * @return bool
     */
    public function activate($name, array $activationStack = [], array $validationData = [])
    {
        if (! $this->has($name)) {
            app_log("Failed to activate module: {$name} does not exist", 'error', null, [
                'module' => $name,
            ]);

            return [
                'success' => false,
                'message' => "Failed to activate module: {$name} does not exist",
            ];
        }

        // Fire before_activate hook
        $hooksService = app('module.hooks');

        $response = [];
        // Check if validation is required and validate if needed
        if ($hooksService->requiresEnvatoValidation($name)) {
            if (empty($validationData['envato_username']) || empty($validationData['envato_purchase_code'])) {
                app_log("Envato validation required for module {$name} but credentials not provided", 'error', null, [
                    'module' => $name,
                ]);

                return [
                    'success' => false,
                    'message' => "Envato validation required for module {$name} but credentials not provided",
                ];
            }

            $response = $hooksService->validateEnvatoPurchase($name, $validationData['envato_username'], $validationData['envato_purchase_code']);
        }

        // Fire general validation hook
        if (isset($response['success']) && ! $response['success']) {
            app_log("Module activation validation failed for {$name}", 'error', null, [
                'module' => $name,
            ]);

            return [
                'success' => false,
                'message' => $response['message'] ?? "Module activation validation failed for {$name}",
            ];
        }

        $hooksService->validateModuleActivation($name);

        // Prevent circular dependencies by tracking the activation stack
        if (in_array($name, $activationStack)) {
            app_log("Circular dependency detected when activating module: {$name}", 'error', null, [
                'module' => $name,
            ]);

            return [
                'success' => false,
                'message' => "Circular dependency detected when activating module: {$name}",
            ];
        }

        // Add current module to activation stack
        $activationStack[] = $name;

        // Get module info
        $module = $this->get($name);

        // Check for conflicts with active modules
        $conflicts = $this->checkConflicts($name);
        if (! empty($conflicts)) {
            $conflictList = implode(', ', array_keys($conflicts));
            app_log("{$name} module conflicts with active modules: {$conflictList}", 'error', null, [
                'module' => $name,
            ]);

            return [
                'success' => false,
                'message' => "{$name} module conflicts with active modules: {$conflictList}",
            ];
        }

        // Check and activate dependencies with semantic versioning support
        $dependencies = $this->parseDependencies($module);
        foreach ($dependencies as $dependency => $versionConstraint) {
            // Check if dependency exists
            if (! $this->has($dependency)) {
                app_log("Dependency {$dependency} for {$name} module is not installed", 'error', null, [
                    'module' => $name,
                ]);

                return [
                    'success' => false,
                    'message' => "Dependency {$dependency} for {$name} module is not installed",
                ];
            }

            // Get the dependency's version
            $dependencyModule = $this->get($dependency);
            $dependencyVersion = $dependencyModule['info']['version'] ?? '1.0.0';

            // Check if the installed version satisfies the constraint
            if (! SemVer::satisfies($dependencyVersion, $versionConstraint)) {
                app_log("{$name} module requires {$dependency} {$versionConstraint}, but version {$dependencyVersion} is installed", 'error', null);

                return [
                    'success' => false,
                    'message' => "{$name} module requires {$dependency} {$versionConstraint}, but version {$dependencyVersion} is installed",
                ];
            }

            // Activate the dependency if it's not active
            if (! $this->isActive($dependency)) {
                if (! $this->activate($dependency, $activationStack)) {
                    app_log("Failed to activate dependency {$dependency} for {$name} module", 'error', null, [
                        'module' => $name,
                    ]);

                    return [
                        'success' => false,
                        'message' => "Failed to activate dependency {$dependency} for {$name} module",
                    ];
                }
            }
        }

        // Call activate method if module class exists
        $this->callModuleMethod($name, 'activate');

        if (! $this->isActive($name)) {
            // No longer update modules.php config file, rely only on modules_statuses.json

            // Update modules_statuses.json
            $this->updateModuleStatusesFile($name, true);

            // Update in-memory state
            $this->modules[$name]['active'] = true;

            // Run migrations if configured to do so
            $autoMigrate = config('modules.auto_migrations.enabled', true);
            if ($autoMigrate) {
                $this->runModuleMigrations($name);

                // Run seeders if configured to do so
                $autoSeed = config('modules.auto_migrations.seed', true);
                if ($autoSeed) {
                    $this->runModuleSeeders($name);
                }
            }

            // Call activated method if module class exists
            $this->callModuleMethod($name, 'activated');

            // Clear route cache to ensure routes are registered
            $this->clearRoutesCache();
        }

        $hooksService->processAfterActivation($name);

        return [
            'success' => true,
            'message' => "$name module activated successfully.",
        ];
    }

    /**
     * Deactivate a module.
     *
     * @param  string  $name
     * @return bool
     */
    public function deactivate($name)
    {
        $hooksService = app('module.hooks');

        if (! $this->has($name)) {
            return [
                'success' => false,
                'message' => "$name module not found, nothing to deactivate.",
            ];
        }

        $module = $this->get($name);

        // Core module check removed to allow core modules to be deactivated

        // Check if other active modules depend on this one
        $dependentModules = $this->findDependentModules($name);
        foreach ($dependentModules as $moduleName => $constraint) {
            // Deactivate dependent module first
            if (! $this->deactivate($moduleName)) {
                app_log("Failed to deactivate dependent module {$moduleName} when deactivating {$name}", 'error', null, [
                    'module' => $name,
                ]);

                return [
                    'success' => false,
                    'message' => "Failed to deactivate dependent module {$moduleName} when deactivating {$name}",
                ];
            }
        }

        // Call deactivate method if module class exists
        $this->callModuleMethod($name, 'deactivate');

        if ($this->isActive($name)) {
            // No longer update modules.php config file, rely only on modules_statuses.json

            // Update modules_statuses.json
            $this->updateModuleStatusesFile($name, false);

            // Update in-memory state
            $this->modules[$name]['active'] = false;

            // Call deactivated method if module class exists
            $this->callModuleMethod($name, 'deactivated');

            // Clear route cache to ensure routes are updated
            $this->clearRoutesCache();
        }

        $hooksService->processAfterDeActivation($name);

        return [
            'success' => true,
            'message' => "$name module deactivated successfully.",
        ];
    }

    /**
     * Remove a module.
     *
     * @param  string  $name
     * @return array
     */
    public function remove($name)
    {
        $hooksService = app('module.hooks');

        if ($this->isActive($name)) {
            return [
                'success' => false,
                'message' => "Cannot remove $name module because it is not active.",
            ];
        }
        if (! $this->has($name)) {
            return [
                'success' => false,
                'message' => "$name module not found, nothing to deactivate.",
            ];
        }

        // Core module check removed to allow core modules to be removed

        // Use direct path calculation instead of module_path to avoid errors if module is already partially removed
        $modulePath = base_path('Modules/'.$name);

        if (File::exists($modulePath)) {
            // Deactivate the module first if it's active
            if ($this->isActive($name)) {
                $this->deactivate($name);
            }

            // Remove the module from modules_statuses.json file
            $this->removeFromModuleStatusesFile($name);

            // Delete the module directory
            File::deleteDirectory($modulePath);

            // Remove from in-memory modules array
            unset($this->modules[$name]);

            // Clear route cache to ensure module routes are removed
            $this->clearRoutesCache();

            $hooksService->processAfterRemoval($name);

            return [
                'success' => true,
                'message' => "$name module removed successfully.",
            ];
        }

        return [
            'success' => false,
            'message' => "$name module directory does not exist, cannot remove.",
        ];
    }

    /**
     * Remove a module from the modules_statuses.json file
     *
     * @param  string  $name
     * @return void
     */
    protected function removeFromModuleStatusesFile($name)
    {
        $modulesStatusesFile = base_path('modules_statuses.json');

        if (File::exists($modulesStatusesFile)) {
            $modulesStatuses = json_decode(File::get($modulesStatusesFile), true) ?? [];

            // Remove the module entry if it exists
            if (array_key_exists($name, $modulesStatuses)) {
                unset($modulesStatuses[$name]);

                // Write the updated statuses back to the file
                File::put(
                    $modulesStatusesFile,
                    json_encode($modulesStatuses, JSON_PRETTY_PRINT)
                );

            }
        }
    }

    /**
     * Check if a module is a core module.
     *
     * @param  string  $name
     * @return bool
     */
    public function isCore($name)
    {
        $module = $this->get($name);

        return $module && isset($module['info']['type']) && $module['info']['type'] === 'core';
    }

    /**
     * Check if a module is an addon module.
     *
     * @param  string  $name
     * @return bool
     */
    public function isAddon($name)
    {
        $module = $this->get($name);

        return $module && (! isset($module['info']['type']) || $module['info']['type'] === 'addon');
    }

    /**
     * Check if a module is a custom module.
     *
     * @param  string  $name
     * @return bool
     */
    public function isCustom($name)
    {
        $module = $this->get($name);

        return $module && isset($module['info']['type']) && $module['info']['type'] === 'custom';
    }

    /**
     * Load all active modules.
     *
     * @return void
     */
    public function loadModules()
    {
        foreach ($this->active() as $name => $module) {
            $this->loadModule($name, $module);
        }
    }

    /**
     * Load a specific module.
     *
     * @param  string  $name
     * @return void
     */
    protected function loadModule($name, array $module)
    {
        $providerClass = $module['info']['provider'] ?? null;

        if ($providerClass) {
            $this->app->register($providerClass);
        } else {
            // Use _module_path helper instead of module_path to avoid errors
            $providerPath = _module_path($name, 'src/Providers/ModuleServiceProvider.php');

            if (File::exists($providerPath)) {
                $namespace = config('modules.namespaces.modules')."\\{$name}\\src\\Providers\\ModuleServiceProvider";
                $this->app->register($namespace);
            }
        }

        // Register the module instance
        $instance = $this->getInstance($name);

        // Register hooks if the module instance exists
        if ($instance && method_exists($instance, 'registerHooks')) {
            $instance->registerHooks();
        }
    }

    /**
     * Scan modules directory and collect modules.
     *
     * @return array
     */
    protected function scan()
    {
        $this->modules = [];
        $modulesDir = config('modules.directory');
        $activeModules = config('modules.active', []);

        // Load modules_statuses.json if exists (this is the new part)
        $modulesStatuses = [];
        $modulesStatusesFile = base_path('modules_statuses.json');
        if (File::exists($modulesStatusesFile)) {
            $modulesStatuses = json_decode(File::get($modulesStatusesFile), true) ?? [];
        }

        if (File::isDirectory($modulesDir)) {
            $directories = File::directories($modulesDir);

            foreach ($directories as $directory) {
                $name = basename($directory);

                // Skip Core module from being treated as a regular module
                if ($name === 'Core') {
                    continue;
                }

                $infoPath = "{$directory}/module.json";
                $composerPath = "{$directory}/composer.json";
                $info = [];

                if (File::exists($infoPath)) {
                    $info = json_decode(File::get($infoPath), true) ?? [];
                } elseif (File::exists($composerPath)) {
                    $composerJson = json_decode(File::get($composerPath), true) ?? [];
                    $info = [
                        'name' => $composerJson['name'] ?? $name,
                        'description' => $composerJson['description'] ?? '',
                        'version' => $composerJson['version'] ?? '1.0.0',
                        'author' => $composerJson['authors'][0]['name'] ?? '',
                        'require' => $composerJson['require'] ?? [],
                        'conflicts' => $composerJson['conflict'] ?? [],
                        'type' => 'addon', // Default type is addon
                    ];
                }

                // Ensure type is either 'core' or 'addon', default to 'addon'
                if (! isset($info['type']) || ! in_array($info['type'], ['core', 'addon', 'custom'])) {
                    $info['type'] = 'addon';
                }

                // Ensure conflicts array exists
                if (! isset($info['conflicts'])) {
                    $info['conflicts'] = [];
                }

                // Check active status from modules_statuses.json if available
                // Otherwise fall back to config value
                $isActive = in_array($name, $activeModules);
                if (array_key_exists($name, $modulesStatuses)) {
                    $isActive = (bool) $modulesStatuses[$name];
                }

                $this->modules[$name] = [
                    'name' => $name,
                    'path' => $directory,
                    'info' => $info,
                    'active' => $isActive,
                ];
            }
        }

        return $this->modules;
    }

    /**
     * Save active modules to config.
     *
     * @return void
     */
    protected function saveActiveModules(array $activeModules)
    {
        $configPath = config_path('modules.php');
        $config = File::get($configPath);

        $pattern = "/('active'\s*=>\s*\[)[^\]]*(\])/";
        $replacement = "$1\n        '".implode("',\n        '", $activeModules)."'\n    $2";

        $updatedConfig = preg_replace($pattern, $replacement, $config);
        File::put($configPath, $updatedConfig);
    }

    /**
     * Get a module instance.
     *
     * @param  string  $name
     * @return Module|null
     */
    public function getInstance($name)
    {
        if (! $this->has($name)) {
            return null;
        }

        if (! isset($this->instances[$name])) {
            $module = $this->get($name);
            $moduleNamespace = config('modules.namespaces.modules');
            $namespace = $module['info']['namespace'] ?? "{$moduleNamespace}\\{$name}";
            $className = rtrim($namespace, '\\').'\\'.$name;

            if (class_exists($className)) {
                $this->instances[$name] = new $className;
            } else {
                return null;
            }
        }

        return $this->instances[$name];
    }

    /**
     * Call a method on the module class if it exists.
     *
     * @param  string  $name
     * @param  string  $method
     * @return mixed|null
     */
    protected function callModuleMethod($name, $method, array $parameters = [])
    {
        $instance = $this->getInstance($name);

        if ($instance && method_exists($instance, $method)) {
            return $instance->{$method}(...$parameters);
        }

        return null;
    }

    /**
     * Parse dependencies with semantic versioning.
     *
     * @return array Key-value pairs of dependency name and version constraint
     */
    protected function parseDependencies(array $module)
    {
        $dependencies = [];
        $require = $module['info']['require'] ?? [];

        // Handle different types of dependency specifications
        foreach ($require as $key => $value) {
            // If it's a numeric key, it's a simple dependency name without version constraint
            if (is_numeric($key)) {
                $dependencies[$value] = '*';
            } else {
                // Otherwise, it's a dependency with version constraint
                $dependencies[$key] = $value;
            }
        }

        return $dependencies;
    }

    /**
     * Find all active modules that depend on a given module.
     *
     * @param  string  $name
     * @return array Key-value pairs of module name and version constraint
     */
    protected function findDependentModules($name)
    {
        $dependent = [];

        foreach ($this->active() as $moduleName => $module) {
            if ($moduleName == $name) {
                continue;
            }

            $dependencies = $this->parseDependencies($module);
            if (isset($dependencies[$name])) {
                $dependent[$moduleName] = $dependencies[$name];
            }
        }

        return $dependent;
    }

    /**
     * Check if a module has conflicts with active modules.
     *
     * @param  string  $name
     * @return array Conflicting modules
     */
    protected function checkConflicts($name)
    {
        $conflicts = [];
        $module = $this->get($name);

        // Check if this module conflicts with active modules
        $moduleConflicts = $module['info']['conflicts'] ?? [];
        foreach ($moduleConflicts as $conflict) {
            // Handle conflicts with or without version constraints
            $conflictName = $conflict;
            $versionConstraint = '*';

            // If it has a version constraint
            if (preg_match('/^([^\s]+)\s+(.+)$/', $conflict, $matches)) {
                $conflictName = $matches[1];
                $versionConstraint = $matches[2];
            }

            // Check if the conflicting module is active
            if ($this->isActive($conflictName)) {
                $conflictingModule = $this->get($conflictName);
                $conflictingVersion = $conflictingModule['info']['version'] ?? '1.0.0';

                // If the constraint applies to this version
                if ($versionConstraint === '*' || SemVer::satisfies($conflictingVersion, $versionConstraint)) {
                    $conflicts[$conflictName] = $versionConstraint;
                }
            }
        }

        // Also check if any active module conflicts with this one
        foreach ($this->active() as $activeModuleName => $activeModule) {
            $activeModuleConflicts = $activeModule['info']['conflicts'] ?? [];

            foreach ($activeModuleConflicts as $conflict) {
                // Handle conflicts with or without version constraints
                $conflictName = $conflict;
                $versionConstraint = '*';

                // If it has a version constraint
                if (preg_match('/^([^\s]+)\s+(.+)$/', $conflict, $matches)) {
                    $conflictName = $matches[1];
                    $versionConstraint = $matches[2];
                }

                // Check if this is the module we're trying to activate
                if ($conflictName === $name) {
                    $thisModuleVersion = $module['info']['version'] ?? '1.0.0';

                    // If the constraint applies to this version
                    if ($versionConstraint === '*' || SemVer::satisfies($thisModuleVersion, $versionConstraint)) {
                        $conflicts[$activeModuleName] = 'conflicts with this module';
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Force refresh the modules cache.
     * This will clear the internal modules array and force a rescan.
     *
     * @return void
     */
    public function refreshCache()
    {
        // Clear the internal modules cache
        $this->modules = [];

        // Clear the instances cache
        $this->instances = [];

        // Force a rescan of modules directory
        $this->scan();

        // Clear route and config caches to ensure latest values
        $this->clearRoutesCache();

        // Manually reload the config from disk
        $configPath = config_path('modules.php');
        if (file_exists($configPath)) {
            $freshConfig = require $configPath;
            config(['modules' => $freshConfig]);
        }

        // Clear any other application caches that might hold module data
        \Illuminate\Support\Facades\Cache::forget('module_list_data');
        \Illuminate\Support\Facades\Cache::forget('modules_all');
        \Illuminate\Support\Facades\Cache::forget('modules_active');
    }

    /**
     * Update the modules_statuses.json file
     *
     * @param  string  $name
     * @return void
     */
    protected function updateModuleStatusesFile($name, bool $active)
    {
        $modulesStatusesFile = base_path('modules_statuses.json');
        $modulesStatuses = [];

        if (File::exists($modulesStatusesFile)) {
            $modulesStatuses = json_decode(File::get($modulesStatusesFile), true) ?? [];
        }

        // Update the status for this module
        $modulesStatuses[$name] = $active;

        // Write the updated statuses back to the file
        File::put(
            $modulesStatusesFile,
            json_encode($modulesStatuses, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Run migrations for a specific module.
     *
     * @param  string  $name
     * @return bool
     */
    public function runModuleMigrations($name)
    {
        if (! $this->has($name)) {
            return false;
        }

        $module = $this->get($name);

        // Define the possible migration paths for this module
        $migrationPaths = [
            _module_path($name, 'Database/Migrations'),
            _module_path($name, 'src/Database/Migrations'),
            _module_path($name, 'database/migrations'),
        ];

        // Filter only existing paths
        $existingPaths = array_filter($migrationPaths, function ($path) {
            return File::isDirectory($path);
        });

        if (empty($existingPaths)) {
            // No migration directory found
            return false;
        }

        try {
            foreach ($existingPaths as $migrationPath) {
                // Run the migrations for this path
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $migrationPath),
                    '--force' => true, // Needed to run in production without confirmation
                ]);

                $output = Artisan::output();

            }

            return true;
        } catch (\Exception $e) {
            app_log("Failed to run migrations for module {$name}: ".$e->getMessage(), 'error', $e, [
                'module' => $name,
            ]);

            return false;
        }
    }

    /**
     * Run seeders for a specific module.
     *
     * @param  string  $name
     * @return bool
     */
    public function runModuleSeeders($name)
    {
        if (! $this->has($name)) {
            return false;
        }

        // Look for potential seeder class names
        $possibleSeederClasses = [
            "\\Modules\\{$name}\\Database\\Seeders\\{$name}DatabaseSeeder",
            "\\Modules\\{$name}\\Database\\Seeders\\DatabaseSeeder",
            "\\Modules\\{$name}\\src\\Database\\Seeders\\{$name}DatabaseSeeder",
            "\\Modules\\{$name}\\src\\Database\\Seeders\\DatabaseSeeder",
            "\\Modules\\{$name}\\database\\seeders\\{$name}DatabaseSeeder",
            "\\Modules\\{$name}\\database\\seeders\\DatabaseSeeder",
            "\\App\\Modules\\{$name}\\Database\\Seeders\\{$name}DatabaseSeeder",
            "\\App\\Modules\\{$name}\\Database\\Seeders\\DatabaseSeeder",
        ];

        // Check if any of the seeder classes exist
        $seederClass = null;
        foreach ($possibleSeederClasses as $class) {
            if (class_exists($class)) {
                $seederClass = $class;
                break;
            }
        }

        if ($seederClass) {
            try {
                // Run the seeder
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true, // Needed to run in production without confirmation
                ]);

                $output = Artisan::output();

                return true;
            } catch (\Exception $e) {
                app_log("Failed to run seeder for module {$name}: ".$e->getMessage(), 'error', $e);

                return false;
            }
        } else {
            // No seeder class was found for this module

            return false;
        }
    }

    /**
     * Clear routes cache to ensure module routes are properly registered/unregistered.
     *
     * @return void
     */
    protected function clearRoutesCache()
    {
        try {
            if (function_exists('artisan')) {
                Artisan::call('route:clear');

                // Also clear config cache to ensure all module configurations are loaded
                Artisan::call('config:clear');
            }
        } catch (\Exception $e) {
            app_log('Failed to clear route or config cache: '.$e->getMessage(), 'error', $e);
        }
    }
}
