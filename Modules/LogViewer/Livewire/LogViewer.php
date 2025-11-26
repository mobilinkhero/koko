<?php

namespace Modules\LogViewer\Livewire;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class LogViewer extends Component
{
    use WithPagination;

    public $selectedFile = null;

    public $searchTerm = '';

    public $logFiles = [];

    public $perPage = 15;

    public $page = 1;

    public int $tenantId;

    public $logLevels = [
        'emergency' => false,
        'alert' => false,
        'critical' => false,
        'error' => false,
        'warning' => false,
        'notice' => false,
        'info' => false,
        'debug' => false,
        'local' => false,
    ];

    public function mount()
    {
        if (! auth()->user()->is_admin) {
            return redirect(route('admin.dashboard'));
        }
        $this->refreshLogFiles();
        if (count($this->logFiles) > 0) {
            $this->selectedFile = $this->logFiles[0];
        }
    }

    public function refreshLogFiles()
    {
        $user = auth()->user();
        $tenantId = tenant_id() ?? null;
        if ($user && $user->user_type === 'tenant') {
            $base_path = storage_path("logs/tenant/{$tenantId}");
            if (File::exists($base_path)) {
                $files = File::files(storage_path("logs/tenant/{$tenantId}"));
            } else {
                $files = File::files(storage_path('logs'));
            }
        } else {
            $files = File::files(storage_path('logs'));
        }

        $this->logFiles = collect($files)
            ->filter(function ($file) {
                return Str::endsWith($file->getFilename(), '.log');
            })
            ->map(function ($file) {
                return $file->getFilename();
            })
            ->toArray();
    }

    public function selectFile($filename)
    {
        $this->selectedFile = $filename;
        $this->resetPage();
    }

    public function deleteFile()
    {
        $tenantId = tenant_id();
        try {
            if ($this->selectedFile) {
                $tenantId ? File::delete(storage_path("logs/tenant/{$tenantId}/".$this->selectedFile)) : File::delete(storage_path('logs/'.$this->selectedFile));
                $this->refreshLogFiles();
                $this->dispatch('notify', ['message' => 'Log file deleted successfully', 'type' => 'success']);

                if (count($this->logFiles) > 0) {
                    $this->selectedFile = $this->logFiles[0];
                } else {
                    $this->selectedFile = null;
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error deleting log file: '.$e->getMessage(), 'type' => 'danger']);
        }
    }

    public function clearAllLogs()
    {
        $tenantId = tenant_id();
        try {
            foreach ($this->logFiles as $file) {
                $tenantId ? File::delete(storage_path("logs/tenant/{$tenantId}/".$file)) : File::delete(storage_path('logs/'.$file));
            }
            $this->refreshLogFiles();
            $this->selectedFile = null;
            $this->dispatch('notify', ['message' => 'All log files cleared successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error clearing log files: '.$e->getMessage(), 'type' => 'danger']);
        }
    }

    public function toggleLogLevel($level)
    {
        $this->logLevels[$level] = ! $this->logLevels[$level];
        $this->resetPage();
    }

    protected function getLogEntries()
    {
        $user = auth()->user();
        $logs = collect();
        $tenantId = tenant_id();

        // Tenant-specific logic
        if ($user && $user->user_type === 'tenant') {
            if (! $this->selectedFile || ! File::exists(storage_path("logs/tenant/{$tenantId}/".$this->selectedFile))) {
                return collect();
            }
            $path = storage_path("logs/tenant/{$tenantId}/".$this->selectedFile);
        } else {
            if (! $this->selectedFile || ! File::exists(storage_path('logs/'.$this->selectedFile))) {
                return collect();
            }

            $path = storage_path('logs/'.$this->selectedFile);
        }

        // Check file existence

        if (File::size($path) > 20 * 1024 * 1024) { // 20MB limit
            return collect([
                [
                    'level' => 'error',
                    'date' => now()->format('Y-m-d H:i:s'),
                    'environment' => 'system',
                    'content' => 'Log file too large (>20MB). Please download and view externally.',
                ],
            ]);
        }

        $content = File::get($path);
        $logs = collect();

        // === [Standard Laravel format] ===
        $pattern = '/\[([\d\-\s:\.]+)\]\s+(\w+)\.(\w+):(.*?)(?=\n\[|$)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        if (! empty($matches)) {
            $logs = collect($matches)->map(function ($match) {
                return [
                    'date' => $match[1],
                    'environment' => $match[2],
                    'level' => strtolower($match[3]),
                    'content' => trim($match[4]),
                ];
            });
        } else {
            // === [Alternative format] ===
            $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.([\w\d]+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/ms';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            if (! empty($matches)) {
                $logs = collect($matches)->map(function ($match) {
                    return [
                        'date' => $match[1],
                        'environment' => $match[2],
                        'level' => strtolower($match[3]),
                        'content' => trim($match[4]),
                    ];
                });
            } else {
                // === [Fallback format] ===
                $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[\.0-9]*)\].*?(\w+)\.(\w+):(.*?)(?=\[\d{4}-\d{2}-\d{2}|$)/s';
                preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

                if (! empty($matches)) {
                    $logs = collect($matches)->map(function ($match) {
                        return [
                            'date' => $match[1],
                            'environment' => $match[2],
                            'level' => strtolower($match[3]),
                            'content' => trim($match[4]),
                        ];
                    });
                }
            }
        }

        // === [Fallback if everything else fails] ===
        if ($logs->isEmpty()) {
            $pattern = '/\[(.*?)\](.*?)(?=\[|$)/s';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            if (! empty($matches)) {
                $logs = collect($matches)->map(function ($match) {
                    $parts = explode('.', $match[1], 2);
                    $environment = 'local';
                    $level = 'info';

                    if (count($parts) > 1) {
                        $datePart = trim($parts[0]);
                        $levelPart = trim($parts[1]);

                        if (preg_match('/(\w+)\.(\w+)/', $levelPart, $levelMatches)) {
                            $environment = $levelMatches[1];
                            $level = strtolower($levelMatches[2]);
                        }
                    } else {
                        $datePart = trim($match[1]);
                    }

                    return [
                        'date' => $datePart,
                        'environment' => $environment,
                        'level' => $level,
                        'content' => trim($match[2]),
                    ];
                });
            }
        }

        // === [Search Filter] ===
        if ($this->searchTerm) {
            $logs = $logs->filter(function ($log) {
                return Str::contains(strtolower($log['content']), strtolower($this->searchTerm));
            });
        }

        // === [Log Level Filter] ===
        $enabledLevels = collect($this->logLevels)->filter()->keys()->toArray();
        if (! empty($enabledLevels)) {
            $logs = $logs->filter(function ($log) use ($enabledLevels) {
                return in_array(strtolower($log['level']), $enabledLevels);
            });
        }

        return $logs;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function render()
    {
        $logs = $this->getLogEntries();

        // Manual pagination to optimize performance with large log files
        $paginatedLogs = $logs->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        $totalPages = ceil($logs->count() / $this->perPage);

        // Ensure page is within valid range
        if ($this->page > $totalPages && $totalPages > 0) {
            $this->page = $totalPages;
            $paginatedLogs = $logs->skip(($this->page - 1) * $this->perPage)->take($this->perPage);
        }

        return view('logviewer::livewire.log-viewer', [
            'logs' => $paginatedLogs,
            'totalPages' => $totalPages,
        ]);
    }

    // Reset pagination when filters change
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
