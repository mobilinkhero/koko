<?php

namespace Corbital\ModuleManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make {name : The name of the module}
                          {--type=addon : The type of module (core or addon or custom)}
                          {--force : Force the operation to run when the module already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $type = $this->option('type');
        $force = $this->option('force');

        // Validate module type
        if (! in_array($type, ['core', 'addon', 'custom'])) {
            $this->error("Invalid module type [{$type}]. Available types: core, addon, custom");

            return 1;
        }

        // Ensure module name is in PascalCase
        $name = Str::studly($name);

        // Use base_path with config instead of module_path to avoid errors with non-existent modules
        $modulePath = base_path('Modules/'.$name);

        if (File::exists($modulePath) && ! $force) {
            $this->error("[{$name}] module already exists!");

            return 1;
        }

        if (File::exists($modulePath) && $force) {
            $this->warn("[{$name}] module already exists. Files will be overwritten.");
            File::deleteDirectory($modulePath);
        }

        $this->createModuleStructure($name, $modulePath, $type);

        $this->info("[{$name}] module created successfully.");
        $this->info('Module type: '.($type === 'core' ? 'Core (cannot be deactivated or removed)' : ($type === 'custom' ? 'Custom' : 'Addon')));
        $this->info("To activate the module, run: php artisan module:activate {$name}");

        return 0;
    }

    /**
     * Create module directory structure and files.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string  $type
     * @return void
     */
    protected function createModuleStructure($name, $path, $type = 'addon')
    {
        // Create directory structure based on Nwidart module structure
        $directories = [
            $path,
            "{$path}/Config",
            "{$path}/Console",
            "{$path}/Database",
            "{$path}/Database/Migrations",
            "{$path}/Database/Seeders",
            "{$path}/Database/Factories",
            "{$path}/Http",
            "{$path}/Http/Controllers",
            "{$path}/Http/Middleware",
            "{$path}/Models",
            "{$path}/Providers",
            "{$path}/resources",
            "{$path}/resources/assets",
            "{$path}/resources/lang",
            "{$path}/resources/lang",
            "{$path}/resources/assets/js",
            "{$path}/resources/assets/css",
            "{$path}/resources/views",
            "{$path}/resources/lang",
            "{$path}/Routes",
            "{$path}/Livewire", // Livewire directory for components
        ];

        foreach ($directories as $directory) {
            File::makeDirectory($directory, 0755, true, true);
        }

        // Create module.json file
        $moduleJson = [
            'name' => $name,
            'alias' => Str::kebab($name),
            'namespace' => "Modules\\{$name}\\",
            'provider' => "Modules\\{$name}\\Providers\\{$name}ServiceProvider",
            'author' => 'Corbital Technologies',
            'url' => 'https://codecanyon.net/user/corbitaltech',
            'version' => '1.0.0',
            'description' => "The {$name} Module",
            'keywords' => [],
            'order' => 0,
            'providers' => [
                "Modules\\{$name}\\Providers\\{$name}ServiceProvider",
            ],
            'aliases' => [],
            'require' => [],
            'conflicts' => [],
            'type' => $type,
        ];

        File::put(
            "{$path}/module.json",
            json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // Create main module class
        $this->createModuleClass($name, $path);

        // Create service provider
        $this->createServiceProvider($name, $path);

        // Create RouteServiceProvider
        $this->createRouteServiceProvider($name, $path);

        // Create route files
        $routesWeb = $this->getStub('routes-web', ['name' => $name]);
        $routesApi = $this->getStub('routes-api', ['name' => $name]);

        File::put("{$path}/Routes/web.php", ltrim($routesWeb));
        File::put("{$path}/Routes/api.php", ltrim($routesApi));

        // Create view file
        $viewIndex = $this->getStub('view-index', ['name' => $name]);
        File::put("{$path}/resources/views/index.blade.php", ltrim($viewIndex));

        // Create config file
        $config = $this->getStub('config', ['name' => $name]);
        File::put("{$path}/Config/config.php", ltrim($config));

        // Create controller file
        $controller = $this->getStub('controller', [
            'name' => $name,
            'namespace' => "Modules\\{$name}\\Http\\Controllers",
        ]);
        File::put("{$path}/Http/Controllers/{$name}Controller.php", ltrim($controller));

        // Create readme file
        $readme = $this->getStub('readme', ['name' => $name, 'type' => $type]);
        File::put("{$path}/README.md", ltrim($readme));

        // Create composer.json file
        $composer = $this->getStub('composer', [
            'name' => strtolower($name),
            'studlyName' => $name,
        ]);
        File::put("{$path}/composer.json", ltrim($composer));

        // Create vite.config.js file
        $vite = $this->getStub('vite', [
            'name' => strtolower($name),
            'studlyName' => $name,
        ]);
        File::put("{$path}/vite.config.js", ltrim($vite));

        // Create package.json file
        $package = $this->getStub('package', [
            'name' => strtolower($name),
            'studlyName' => $name,
        ]);
        File::put("{$path}/package.json", ltrim($package));

        // Create assets file
        File::put("{$path}/resources/assets/css/app.css", '');
        File::put("{$path}/resources/assets/js/app.js", '');

        // Create language files
        File::put("{$path}/resources/lang/en.json", ltrim('{}'));
        File::put("{$path}/resources/lang/tenant_en.json", ltrim('{}'));
    }

    /**
     * Create main module class.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function createModuleClass($name, $path)
    {
        $content = $this->getStub('module-class', [
            'name' => $name,
            'namespace' => "Modules\\{$name}",
        ]);

        // Ensure no blank lines at the beginning of the file
        $content = ltrim($content);

        File::put("{$path}/{$name}.php", $content);
    }

    /**
     * Create service provider for the module.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function createServiceProvider($name, $path)
    {
        $content = $this->getStub('service-provider', [
            'name' => $name,
            'namespace' => "Modules\\{$name}\\Providers",
        ]);

        // Ensure no blank lines at the beginning of the file
        $content = ltrim($content);

        File::put("{$path}/Providers/{$name}ServiceProvider.php", $content);
    }

    /**
     * Create the module's RouteServiceProvider class.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function createRouteServiceProvider($name, $path)
    {
        $content = <<<'EOT'
<?php

namespace Modules\{{ name }}\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\{{ name }}\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        // Load web routes without namespace prefixing to allow both controller and Livewire routes in one file
        Route::middleware('web')
            ->group(module_path('{{ name }}', '/Routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('{{ name }}', '/Routes/api.php'));
    }
}
EOT;

        foreach (['name' => $name] as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        File::put("{$path}/Providers/RouteServiceProvider.php", ltrim($content));
    }

    /**
     * Get stub content with replacements.
     *
     * @param  string  $stub
     * @return string
     */
    protected function getStub($stub, array $replacements)
    {
        $stubPath = config('modules.stubs.path', __DIR__.'/stubs');

        $stubFiles = [
            'module-class' => $stubPath.'/module.stub',
            'service-provider' => $stubPath.'/provider.stub',
            'routes-web' => $stubPath.'/routes-web.stub',
            'routes-api' => $stubPath.'/routes-api.stub',
            'view-index' => $stubPath.'/view-index.stub',
            'config' => $stubPath.'/config.stub',
            'controller' => $stubPath.'/controller.stub',
            'readme' => $stubPath.'/readme.stub',
            'composer' => $stubPath.'/composer.stub',
            'vite' => $stubPath.'/vite.stub',
            'package' => $stubPath.'/package.stub',
        ];

        // Use built-in stubs if stub file doesn't exist
        if (! isset($stubFiles[$stub]) || ! File::exists($stubFiles[$stub])) {
            return $this->getBuiltInStub($stub, $replacements);
        }

        $content = File::get($stubFiles[$stub]);

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        return $content;
    }

    /**
     * Get built-in stub content.
     *
     * @param  string  $stub
     * @return string
     */
    protected function getBuiltInStub($stub, array $replacements)
    {
        switch ($stub) {
            case 'module-class':
                $content = <<<'EOT'
<?php

namespace {{ namespace }};

use Corbital\ModuleManager\Support\Module;
use Corbital\ModuleManager\Facades\ModuleEvents;

class {{ name }} extends Module
{
    /**
     * Register event listeners for this module.
     *
     * @return void
     */
    public function registerHooks()
    {
        // Register event hooks here
    }

    /**
     * Called when the module is activated.
     *
     * @return void
     */
    public function activate()
    {
        parent::activate();
        // Code to run when the module is activated
    }

    /**
     * Called when the module is deactivated.
     *
     * @return void
     */
    public function deactivate()
    {
        // Code to run when the module is deactivated
    }

    /**
     * Called after the module has been activated.
     *
     * @return void
     */
    public function activated()
    {
        // Code to run after the module is activated
    }

    /**
     * Called after the module has been deactivated.
     *
     * @return void
     */
    public function deactivated()
    {
        // Code to run after the module is deactivated
    }
}
EOT;
                break;

            case 'service-provider':
                $content = <<<'EOT'
<?php

namespace {{ namespace }};

use Illuminate\Support\ServiceProvider;

class {{ name }}ServiceProvider extends ServiceProvider
{
    /**
     * The module name.
     *
     * @var string
     */
    protected $moduleName = '{{ name }}';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(base_path('Modules/' . $this->moduleName . '/Database/Migrations'));
        // Routes are now handled by RouteServiceProvider
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the RouteServiceProvider
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    protected function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . strtolower($this->moduleName));

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleName);
        } else {
            $this->loadTranslationsFrom(base_path('Modules/' . $this->moduleName . '/resources/lang'), $this->moduleName);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            base_path('Modules/' . $this->moduleName . '/Config/config.php') => config_path($this->moduleName . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            base_path('Modules/' . $this->moduleName . '/Config/config.php'), $this->moduleName
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    protected function registerViews()
    {
        $viewPath = resource_path('views/modules/' . strtolower($this->moduleName));

        $sourcePath = base_path('Modules/' . $this->moduleName . '/resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge([$sourcePath], [
            $viewPath
        ]), $this->moduleName);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
EOT;
                break;

            case 'routes-web':
                $content = <<<'EOT'
<?php

use Illuminate\Support\Facades\Route;
use Modules\{{ name }}\Http\Controllers\{{ name }}Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded without namespace prefixing.
|
| This allows you to use both:
| 1. Controllers: Use full namespace: \Modules\{{ name }}\Http\Controllers\YourController::class
| 2. Livewire: Use component class: \Modules\{{ name }}\Livewire\YourComponent::class
|
*/

Route::middleware('web')->group(function () {
    Route::prefix('{{ name }}')->group(function () {
        // Controller route example (use full namespace)
        Route::get('/', [\Modules\{{ name }}\Http\Controllers\{{ name }}Controller::class, 'index'])->name('{{ name }}.index');

        // Livewire component route example (uncomment when you have a component)
        // use Modules\{{ name }}\Livewire\Dashboard;
        // Route::get('/dashboard', Dashboard::class)->name('{{ name }}.dashboard');
    });
});
EOT;
                break;

            case 'routes-api':
                $content = <<<'EOT'
<?php

use Illuminate\Support\Facades\Route;
use Modules\{{ name }}\Http\Controllers\{{ name }}Controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the ServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware('api')->prefix('api')->group(function () {
    Route::prefix('{{ name }}')->group(function () {
        // API routes here
    });
});
EOT;
                break;

            case 'view-index':
                $content = <<<'EOT'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('{{ name }}') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Welcome to the {{ name }} module!</h3>
                    <p>This is a sample module for your Laravel application.</p>
                    <p class="mt-4">You can customize this view in <code class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Modules/{{ name }}/resources/views/index.blade.php</code></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOT;
                break;

            case 'config':
                $content = <<<'EOT'
<?php

return [
    'name' => '{{ name }}',
    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the configuration options for the {{ name }} module.
    |
    */
];
EOT;
                break;

            case 'controller':
                $content = <<<'EOT'
<?php

namespace {{ namespace }};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class {{ name }}Controller extends Controller
{
    /**
     * Display the module welcome screen
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('{{ name }}::index');
    }
}
EOT;
                break;

            case 'readme':
                $content = <<<'EOT'
# {{ name }} Module

This is the {{ name }} module for the application.

## Type
{{ type }} ({{ type === 'core' ? 'Cannot be deactivated or removed' : 'Can be activated, deactivated, and removed' }})

## Installation

1. Copy this folder to `Modules/{{ name }}`
2. Activate the module using `php artisan module:activate {{ name }}`

## Features

- List features here

## Configuration

- Document module configuration options here
EOT;
                break;

            case 'composer':
                $content = <<<'EOT'
{
    "name": "app/{{ name }}",
    "description": "{{ studlyName }} module for application",
    "type": "laravel-module",
    "authors": [
        {
            "name": "Your Name",
            "email": "your.email@example.com"
        }
    ],
    "require": {},
    "extra": {
        "laravel": {
            "providers": [
                "Modules\\{{ studlyName }}\\Providers\\{{ studlyName }}ServiceProvider"
            ]
        }
    },
    "autoload": {
        "psr-4": {
            "Modules\\{{ studlyName }}\\": ""
        }
    }
}
EOT;
                break;

            case 'vite':
                $content = <<<'EOT'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readdirSync, statSync } from 'fs';
import { join,relative,dirname } from 'path';
import { fileURLToPath } from 'url';

export default defineConfig({
    build: {
        outDir: '../../public/build-$LOWER_NAME$',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-$LOWER_NAME$',
            input: [
                __dirname + '/resources/assets/sass/app.scss',
                __dirname + '/resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
EOT;
                break;

            case 'package':
                $content = <<<'EOT'
{
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "axios": "^1.1.2",
    "laravel-vite-plugin": "^0.7.5",
    "sass": "^1.69.5",
    "postcss": "^8.3.7",
    "vite": "^4.0.0"
  }
}
EOT;
                break;

            default:
                return '';
        }

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{ {$key} }}", $value, $content);
        }

        return $content;
    }
}
