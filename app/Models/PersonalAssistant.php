<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Personal Assistant Model
 * 
 * One personal assistant per tenant for document-based AI interactions
 * 
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $description
 * @property string $system_instructions
 * @property string $model
 * @property float $temperature
 * @property int $max_tokens
 * @property bool $file_analysis_enabled
 * @property array|null $uploaded_files
 * @property string|null $processed_content
 * @property bool $is_active
 * @property array|null $use_case_tags
 */
class PersonalAssistant extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'system_instructions',
        'model',
        'temperature',
        'max_tokens',
        'file_analysis_enabled',
        'uploaded_files',
        'processed_content',
        'is_active',
        'use_case_tags',
        'last_synced_at',
        'openai_assistant_id',
        'openai_vector_store_id',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'file_analysis_enabled' => 'boolean',
        'is_active' => 'boolean',
        'uploaded_files' => 'array',
        'use_case_tags' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'file_analysis_enabled' => true,
    ];

    /**
     * Available AI models
     */
    public const AVAILABLE_MODELS = [
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo (16K Context)',
        'gpt-4' => 'GPT-4',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'gpt-4o-mini' => 'GPT-4o Mini (Fast & Cost-effective)',
    ];

    /**
     * Use case categories for assistants
     */
    public const USE_CASES = [
        'faq' => 'FAQs Automation',
        'product' => 'Product Enquiries',
        'onboarding' => 'Onboarding & Setup Help',
        'csv' => 'CSV Lookups',
        'sop' => 'Internal SOPs or Team Guides',
        'general' => 'General Purpose',
    ];

    /**
     * Get the tenant that owns this assistant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the current active assistant for a tenant
     */
    public static function getForCurrentTenant(): ?self
    {
        $tenant = static::getCurrentTenant();
        if (!$tenant) {
            return null;
        }

        return static::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all assistants for current tenant
     */
    public static function getAllForCurrentTenant()
    {
        $tenant = static::getCurrentTenant();
        if (!$tenant) {
            return collect();
        }

        return static::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create or update assistant for current tenant
     */
    public static function createOrUpdateForTenant(array $data): self
    {
        $tenant = static::getCurrentTenant();
        if (!$tenant) {
            throw new \Exception('No current tenant found');
        }

        $data['tenant_id'] = $tenant->id;

        // Create new assistant (removed the single assistant constraint)
        $assistant = static::create($data);

        return $assistant;
    }

    /**
     * Get current tenant with fallback methods
     */
    protected static function getCurrentTenant()
    {
        try {
            // Primary method: Use session-based tenant identification
            $tenantId = session('current_tenant_id');

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant instanceof Tenant) {
                    return $tenant;
                }
            }

            // Fallback method: Use traditional tenant context
            if (Tenant::checkCurrent()) {
                $tenant = Tenant::current();
                if ($tenant instanceof Tenant) {
                    // Sync session with current tenant for consistency
                    session(['current_tenant_id' => $tenant->id]);
                    return $tenant;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the full system context including uploaded files
     */
    public function getFullSystemContext(): string
    {
        $context = $this->system_instructions;

        if ($this->processed_content) {
            $context .= "\n\n=== KNOWLEDGE BASE ===\n";
            $context .= $this->processed_content;
            $context .= "\n=== END KNOWLEDGE BASE ===\n";
        }

        return $context;
    }


    /**
     * Get use case badges for display
     */
    public function getUseCaseBadges(): array
    {
        if (!$this->use_case_tags) {
            return [];
        }

        return collect($this->use_case_tags)
            ->map(fn($tag) => self::USE_CASES[$tag] ?? $tag)
            ->toArray();
    }

    /**
     * Check if assistant has any uploaded files
     */
    public function hasUploadedFiles(): bool
    {
        return !empty($this->uploaded_files);
    }

    /**
     * Get total file count
     */
    public function getFileCount(): int
    {
        return count($this->uploaded_files ?? []);
    }

    /**
     * Get total processed content size
     */
    public function getContentSize(): int
    {
        return strlen($this->processed_content ?? '');
    }

    /**
     * Get uploaded files with current existence status
     */
    public function getFilesWithStatus(): array
    {
        $files = $this->uploaded_files ?? [];
        
        // Check file existence for each file
        foreach ($files as &$file) {
            if (isset($file['path'])) {
                // Use Storage facade for consistent file checking
                $file['exists'] = Storage::exists($file['path']);
                
                // Update size if file exists and size is missing or zero
                if ($file['exists'] && (!isset($file['size']) || $file['size'] === 0)) {
                    $file['size'] = Storage::size($file['path']);
                }
            } else {
                $file['exists'] = false;
            }
        }
        
        return $files;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assistant) {
            do_action('personal_assistant.before_create', $assistant);
        });

        static::created(function ($assistant) {
            do_action('personal_assistant.after_create', $assistant);
        });

        static::updating(function ($assistant) {
            do_action('personal_assistant.before_update', $assistant);
        });

        static::updated(function ($assistant) {
            do_action('personal_assistant.after_update', $assistant);
        });

        static::deleting(function ($assistant) {
            do_action('personal_assistant.before_delete', $assistant);
        });

        static::deleted(function ($assistant) {
            do_action('personal_assistant.after_delete', $assistant);
        });
    }
}
