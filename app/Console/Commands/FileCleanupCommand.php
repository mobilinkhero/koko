<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class FileCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup {--file=unused-files.json : Path to JSON file with unused files list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up unused files from the project based on a JSON list';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting unused files cleanup process...');

        // Step 1: Get JSON file path from option or use default
        $path = $this->option('file');

        $jsonFilePath = base_path($path);
        if (! File::exists($jsonFilePath)) {
            $this->error("JSON file not found at: {$jsonFilePath}");

            app_log("JSON file not found at: {$jsonFilePath}", 'error');

            return 1;
        }

        // Step 2: Read JSON file
        $this->info("Reading file list from: {$jsonFilePath}");

        try {
            $filesList = json_decode(File::get($jsonFilePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON file: '.json_last_error_msg());
                app_log('Invalid JSON file: '.json_last_error_msg(), 'error');

                return 1;
            }

            // Step 3: Process each file
            $results = [
                'deleted' => [],
                'not_found' => [],
                'error' => [],
            ];

            $this->output->progressStart(count($filesList));

            foreach ($filesList as $filePath) {
                $this->output->progressAdvance();

                // Check if file exists
                if (File::exists(base_path($filePath))) {
                    try {
                        // Attempt to delete the file
                        File::delete(base_path($filePath));
                        $results['deleted'][] = [
                            'path' => $filePath,
                            'status' => 'Deleted',
                            'timestamp' => now()->format('Y-m-d H:i:s'),
                        ];

                        $this->line(" <fg=green>✓</> Deleted: {$filePath}");
                    } catch (\Exception $e) {
                        $results['error'][] = [
                            'path' => $filePath,
                            'error' => $e->getMessage(),
                            'timestamp' => now()->format('Y-m-d H:i:s'),
                        ];

                        $this->line(" <fg=red>✗</> Error deleting: {$filePath}");
                    }
                } else {
                    $results['not_found'][] = [
                        'path' => $filePath,
                        'status' => 'Not Found',
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                    ];
                    $this->line(" <fg=yellow>⚠</> Not found: {$filePath}");
                }
            }

            $this->output->progressFinish();

            // Step 4: Save results to JSON file (for reference)
            $jsonResultsPath = storage_path('app/cleanup-results.json');
            File::put($jsonResultsPath, json_encode($results, JSON_PRETTY_PRINT));

            // Step 4b: Save results to a Markdown file with tables
            $mdResultsPath = storage_path('app/cleanup-report.md');
            $this->saveMarkdownReport($results, $mdResultsPath);

            $this->info('Cleanup completed. Results saved to:');
            $this->line("JSON: {$jsonResultsPath}");
            $this->line("Report: {$mdResultsPath}");

            // Step 5: Run Composer commands
            $this->runCleanupCommands();

            // Display summary
            $this->displaySummary($results);

            return 0;
        } catch (\Exception $e) {
            $this->error('Error reading JSON file: '.$e->getMessage());
            app_log('Error reading JSON file', 'error', $e);

            return 1;
        }
    }

    /**
     * Save cleanup results as a Markdown report with tables
     *
     * @param  array  $results  The cleanup results
     * @param  string  $filePath  Where to save the markdown file
     * @return void
     */
    protected function saveMarkdownReport(array $results, string $filePath)
    {
        $lines = [];

        $lines[] = '# File Cleanup Report';
        $lines[] = '';
        $lines[] = 'Generated: '.now()->format('Y-m-d H:i:s');
        $lines[] = '';

        // Summary section
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = '| Category | Count |';
        $lines[] = '| --- | --- |';
        $lines[] = '| Files Deleted | '.count($results['deleted']).' |';
        $lines[] = '| Files Not Found | '.count($results['not_found']).' |';
        $lines[] = '| Errors | '.count($results['error']).' |';
        $lines[] = '';

        // Deleted files table
        if (count($results['deleted']) > 0) {
            $lines[] = '## Deleted Files';
            $lines[] = '';
            $lines[] = '| File Path | Timestamp |';
            $lines[] = '| --- | --- |';

            foreach ($results['deleted'] as $file) {
                $lines[] = '| `'.$file['path'].'` | '.$file['timestamp'].' |';
            }

            $lines[] = '';
        }

        // Not found files table
        if (count($results['not_found']) > 0) {
            $lines[] = '## Files Not Found';
            $lines[] = '';
            $lines[] = '| File Path | Timestamp |';
            $lines[] = '| --- | --- |';

            foreach ($results['not_found'] as $file) {
                $lines[] = '| `'.$file['path'].'` | '.$file['timestamp'].' |';
            }

            $lines[] = '';
        }

        // Errors table
        if (count($results['error']) > 0) {
            $lines[] = '## Errors';
            $lines[] = '';
            $lines[] = '| File Path | Error | Timestamp |';
            $lines[] = '| --- | --- | --- |';

            foreach ($results['error'] as $file) {
                $lines[] = '| `'.$file['path'].'` | '.$file['error'].' | '.$file['timestamp'].' |';
            }

            $lines[] = '';
        }

        // Join all lines with line breaks and save to file
        File::put($filePath, implode("\r\n", $lines));
    }

    /**
     * Run Composer commands to clean up after file deletion
     *
     * @return void
     */
    protected function runCleanupCommands()
    {
        $this->info('Running system cleanup commands...');

        // Array of Artisan commands to run instead of Composer
        $artisanCommands = [
            'optimize:clear',
            'view:clear',
            'cache:clear',
            'config:clear',
            'route:clear',
        ];

        foreach ($artisanCommands as $command) {
            $this->info('Running: php artisan '.$command);

            try {
                // Call Artisan commands directly using the Artisan facade
                $exitCode = Artisan::call($command);

                if ($exitCode === 0) {
                    $this->info('Command completed successfully');
                } else {
                    $this->warn('Command returned non-zero exit code: '.$exitCode);
                    $this->line(Artisan::output());
                }
            } catch (\Exception $e) {
                $this->error('Error running command: '.$e->getMessage());
            }
        }

        $this->info('System cleanup completed');
    }

    /**
     * Display summary of cleanup operation
     *
     * @return void
     */
    protected function displaySummary(array $results)
    {
        $this->newLine();
        $this->info('=== Cleanup Summary ===');
        $this->info('Files deleted: '.count($results['deleted']));
        $this->info('Files not found: '.count($results['not_found']));
        $this->info('Files with errors: '.count($results['error']));
        $this->newLine();
    }
}
