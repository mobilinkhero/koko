<?php

namespace Corbital\ModuleManager\Http\Controllers;

use Corbital\ModuleManager\Facades\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;

class ModuleController extends Controller
{
    /**
     * Display a listing of the modules.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {

        return view('modules::index');
    }

    /**
     * Display the specified module.
     *
     * @param  string  $name
     * @return \Illuminate\View\View
     */
    public function show($name)
    {
        $module = ModuleManager::get($name);

        if (! $module) {
            abort(404, "[{$name}] module not found.");
        }

        return view('modules::show', compact('module'));
    }

    /**
     * Show the form for uploading a new module.
     *
     * @return \Illuminate\View\View
     */
    public function showUploadForm()
    {
        return view('modules::upload');
    }

    /**
     * Upload a new module.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'module_file' => 'required|file|mimes:zip|max:10240',
        ]);

        if ($validator->fails()) {
            app_log('Module upload validation failed', 'warning', null, [
                'errors' => $validator->errors()->toArray(),
            ]);

            session()->flash('notification', [
                'type' => 'danger',
                'message' => $validator,
            ]);

            return redirect()->route('admin.modules.upload')
                ->withInput();
        }

        $moduleFile = $request->file('module_file');
        $tempPath = storage_path('app/temp/'.Str::random(16));

        // Ensure the temp directory exists
        try {
            if (! File::exists(dirname($tempPath))) {
                File::makeDirectory(dirname($tempPath), 0755, true, true);
            }
        } catch (\Exception $e) {
            app_log('Failed to create temp directory', 'error', $e, [
                'path' => dirname($tempPath),
            ]);

            session()->flash('notification', [
                'type' => 'danger',
                'message' => 'Failed to create temporary directory: '.$e->getMessage(),
            ]);

            return redirect()->route('admin.modules.upload');
        }

        try {
            // Extract the module zip file
            $zip = new ZipArchive;
            $res = $zip->open($moduleFile->getRealPath());

            if ($res !== true) {
                app_log('Failed to open ZIP file', 'error', null, [
                    'error_code' => $res,
                ]);

                session()->flash('notification', [
                    'type' => 'danger',
                    'message' => "Failed to open ZIP file: {$res}",
                ]);

                return redirect()->route('admin.modules.upload');
            }

            $zip->extractTo($tempPath);
            $zip->close();

            // Find the module.json file
            $moduleJsonPath = $this->findModuleJson($tempPath);

            if (! $moduleJsonPath) {
                File::deleteDirectory($tempPath);

                session()->flash('notification', [
                    'type' => 'danger',
                    'message' => 'Invalid module package: module.json not found',
                ]);

                return redirect()->route('admin.modules.upload');
            }

            $moduleJson = json_decode(File::get($moduleJsonPath), true);

            if (! isset($moduleJson['name'])) {
                File::deleteDirectory($tempPath);
                app_log('Invalid module.json file - missing name field', 'error', null);

                session()->flash('notification', [
                    'type' => 'danger',
                    'message' => 'Invalid module.json: name field is required',
                ]);

                return redirect()->route('admin.modules.upload');
            }

            $moduleName = $moduleJson['name'];

            $moduleDirectory = dirname($moduleJsonPath);

            // Explicitly define the module path to avoid conflicts or null values
            $modulesDirectory = rtrim(config('modules.directory', base_path('Modules')), '/');
            $targetPath = $modulesDirectory.'/'.$moduleName;

            // Ensure the modules directory exists
            if (! File::exists($modulesDirectory)) {
                File::makeDirectory($modulesDirectory, 0755, true, true);
            }

            // Check if module already exists
            $isUpdate = File::exists($targetPath);

            if ($isUpdate) {
                // Create backup of existing module
                $backupPath = storage_path('app/backup/modules/'.$moduleName.'_'.date('Y-m-d_H-i-s'));

                // Ensure backup directory exists
                if (! File::exists(dirname($backupPath))) {
                    File::makeDirectory(dirname($backupPath), 0755, true, true);
                }

                File::copyDirectory($targetPath, $backupPath);

                // Deactivate module if it's active
                if (ModuleManager::isActive($moduleName)) {

                    ModuleManager::deactivate($moduleName);
                    $wasActive = true;
                } else {
                    $wasActive = false;
                }

                // Remove old module directory

                File::deleteDirectory($targetPath);
            }

            // Move module to modules directory

            if (! File::exists(dirname($targetPath))) {
                File::makeDirectory(dirname($targetPath), 0755, true, true);
            }
            File::copyDirectory($moduleDirectory, $targetPath);

            // Refresh the modules cache

            ModuleManager::refreshCache();

            // Reactivate module if it was active before
            if ($isUpdate && isset($wasActive) && $wasActive) {
                try {
                    ModuleManager::activate($moduleName);
                } catch (\Exception $activationException) {
                    app_log('Failed to reactivate module after update', 'warning', $activationException, [
                        'module' => $moduleName,
                    ]);
                }
            }

            // Clean up temp directory
            File::deleteDirectory($tempPath);

            // For new module installations, automatically activate if it's a core module
            if (! $isUpdate) {
                // Refresh again to ensure the module info is up-to-date
                ModuleManager::refreshCache();
                // Get the updated module information
                $moduleInfo = ModuleManager::get($moduleName);

                // Check if it's a core type module
                if ($moduleInfo && isset($moduleInfo['info']['type']) && $moduleInfo['info']['type'] === 'core') {

                    ModuleManager::activate($moduleName);
                    $message = "[{$moduleName}] module has been uploaded and activated successfully.";
                } else {
                    $message = "[{$moduleName}] module has been uploaded successfully.";
                }
            } else {
                $message = "[{$moduleName}] module has been updated successfully.";
            }

            // Clear route cache to ensure routes are registered properly
            $this->clearApplicationCaches();

            session()->flash('notification', [
                'type' => 'success',
                'message' => $message,
            ]);

            // Use absolute admin URL path with session flash data
            return redirect()->to('/admin/modules');
        } catch (\Exception $e) {
            app_log('Module upload processing failed', 'error', $e, [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up temp directory
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            session()->flash('notification', [
                'type' => 'danger',
                'message' => 'Failed to process module: '.$e->getMessage(),
            ]);

            return redirect()->route('admin.modules.upload');
        }
    }

    /**
     * Activate the specified module.
     *
     * @param  string  $name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($name)
    {
        if (ModuleManager::activate($name)) {
            $this->clearApplicationCaches();

            session()->flash('notification', [
                'type' => 'success',
                'message' => "[{$name}] module activated successfully.",
            ]);

            return redirect()->back();
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => "Failed to activate [{$name}] module.",
        ]);

        return redirect()->back();
    }

    /**
     * Deactivate the specified module.
     *
     * @param  string  $name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate($name)
    {
        if (ModuleManager::deactivate($name)) {
            $this->clearApplicationCaches();

            session()->flash('notification', [
                'type' => 'success',
                'message' => "[{$name}] module deactivated successfully.",
            ]);

            return redirect()->back();

        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => "Failed to deactivate [{$name}] module. It may be a core module or required by other active modules.",
        ]);

        return redirect()->back();

    }

    /**
     * Remove the specified module.
     *
     * @param  string  $name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove($name)
    {
        if (ModuleManager::remove($name)) {
            session()->flash('notification', [
                'type' => 'success',
                'message' => "[{$name}] module removed successfully.",
            ]);

            return redirect()->route('modules.index');
        }

        session()->flash('notification', [
            'type' => 'danger',
            'message' => "Failed to remove [{$name}] module. It may be a core module or still active.",
        ]);

        return redirect()->back();
    }

    /**
     * Find module.json in the extracted package.
     *
     * @param  string  $path
     * @return string|null
     */
    protected function findModuleJson($path)
    {
        // First, check if module.json exists directly in the extracted directory
        if (File::exists($path.'/module.json')) {
            return $path.'/module.json';
        }

        // If not, it might be in a subdirectory (usually when the zip contains a root folder)
        $directories = File::directories($path);

        foreach ($directories as $directory) {
            if (File::exists($directory.'/module.json')) {
                return $directory.'/module.json';
            }
        }

        return null;
    }

    /**
     * Clear application caches to ensure module changes take effect.
     *
     * @return void
     */
    protected function clearApplicationCaches()
    {
        try {

            // Clear route cache
            Artisan::call('route:clear');

            // Clear config cache
            Artisan::call('config:clear');

            // Clear view cache
            Artisan::call('view:clear');
        } catch (\Exception $e) {
            app_log('Failed to clear application caches', 'error', $e, [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
