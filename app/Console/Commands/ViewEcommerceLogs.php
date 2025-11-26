<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ViewEcommerceLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ecommerce:logs {--lines=50 : Number of lines to show} {--follow : Follow log file}';

    /**
     * The console command description.
     */
    protected $description = 'View e-commerce logs from home directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $homeDir = $_SERVER['HOME'] ?? '/home/default';
        $logFile = $homeDir . '/ecomorcelog.log';
        $lines = $this->option('lines');
        $follow = $this->option('follow');

        if (!file_exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return Command::FAILURE;
        }

        $this->info("Reading e-commerce logs from: {$logFile}");
        $this->info(str_repeat('=', 60));

        if ($follow) {
            $this->info("Following log file (press Ctrl+C to stop)...");
            
            // Show last few lines first
            $this->line(shell_exec("tail -n {$lines} '{$logFile}'"));
            
            // Follow new lines
            $handle = popen("tail -f '{$logFile}'", 'r');
            if ($handle) {
                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        $this->line(trim($line));
                    }
                }
                pclose($handle);
            }
        } else {
            // Show last N lines
            $content = shell_exec("tail -n {$lines} '{$logFile}'");
            $this->line($content);
        }

        return Command::SUCCESS;
    }
}
