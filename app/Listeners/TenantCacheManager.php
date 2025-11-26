<?php

namespace App\Listeners;

use App\Events\Tenant\TenantCreated;
use App\Events\Tenant\TenantDeleted;
use App\Events\Tenant\TenantStatusChanged;
use App\Events\Tenant\TenantUpdated;
use App\Models\Language;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\Tenant\TenantEmailTemplate;
use App\Models\TenantLanguage;
use Corbital\Settings\Models\TenantSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class TenantCacheManager
{
    /**
     * The language model instance.
     */
    protected Language $language;

    public Role $role;

    /**
     * Create a new tenant cache manager instance.
     */
    public function __construct()
    {
        $this->language = new Language;
        $this->role = new Role;
    }

    /**
     * Handle tenant created events.
     */
    public function handleTenantCreated(TenantCreated $event): void
    {
        // For a new tenant, we don't need to clear cache, but we can
        // proactively cache its information for faster first access
        $tenant = $event->tenant;

        $this->initTenantInformation($tenant);
        Cache::put("tenant:{$tenant->subdomain}", $tenant, now()->addMinutes(10));
    }

    /**
     * Handle tenant updated events.
     */
    public function handleTenantUpdated(TenantUpdated $event): void
    {
        $tenant = $event->tenant;

        // Clear any cached tenant data
        Cache::forget("tenant:{$tenant->subdomain}");

        // If subdomain changed, also clear the old cache
        if (isset($event->changedAttributes['subdomain'])) {
            $oldSubdomain = $tenant->getOriginal('subdomain');
            Cache::forget("tenant:{$oldSubdomain}");
        } else {
            // Re-cache with updated information
            Cache::put("tenant:{$tenant->subdomain}", $tenant, now()->addMinutes(10));

        }
    }

    /**
     * Handle tenant status changed events.
     */
    public function handleTenantStatusChanged(TenantStatusChanged $event): void
    {
        $tenant = $event->tenant;

        // Clear any cached tenant data
        Cache::forget("tenant:{$tenant->subdomain}");
        // If tenant is now active, we can proactively cache it again
        if ($event->newStatus === 'active') {
            Cache::put("tenant:{$tenant->subdomain}", $tenant, now()->addMinutes(10));
        }

        // Additional actions based on status change
        if ($event->oldStatus === 'active' && $event->newStatus !== 'active') {
            // Notify tenant users or perform other deactivation tasks
            // This could be moved to a separate service
        }
    }

    /**
     * Handle tenant deleted events.
     */
    public function handleTenantDeleted(TenantDeleted $event): void
    {
        $tenant = $event->tenant;

        // Clear tenant caches
        Cache::forget("tenant:{$tenant->subdomain}");
        Cache::forget("tenant_{$tenant->id}");
        Cache::forget("translations.{$tenant->id}_tenant.");

        // Note: Tenant data deletion is now handled by the cleanup command
        // This event is only fired after the tenant data has already been deleted
    }

    protected function deleteDirectory($dir)
    {
        if (! is_dir($dir)) {
            return true;
        }

        foreach (glob($dir.'/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return rmdir($dir);
    }

    private function initTenantInformation($tenant): void
    {
        // Assign language to tenant : Start
        $this->language->name = 'English';
        $this->language->code = 'en';
        $this->language->status = 1;
        $this->language->tenant_id = $tenant->id;
        $language_exists = Language::where('code', 'en')
            ->where('tenant_id', $tenant->id)
            ->exists();

        if (! $language_exists) {
            $this->language->save();
        }

        $tenantSettings = get_batch_settings(['tenant.set_default_tenant_language']);
        if ($tenantSettings['tenant.set_default_tenant_language'] && $tenantSettings['tenant.set_default_tenant_language'] != 'en') {
            $this->language->code = $tenantSettings['tenant.set_default_tenant_language'];
            $language_exists = Language::where('code', $this->language->code)
                ->where('tenant_id', $tenant->id)
                ->exists();

            $source = public_path('lang')."/tenant_{$this->language->code}.json";
            $destination = resource_path("lang/translations/tenant/{$tenant->id}/tenant_{$this->language->code}.json");
            $tenantLanguage = TenantLanguage::where('code', $this->language->code)->first();

            if (File::exists($source) && ! $language_exists && ! empty($tenantLanguage)) {
                File::ensureDirectoryExists(dirname($destination));
                File::copy($source, $destination);
                $this->language->name = $tenantLanguage->name;
                $this->language->status = 1;
                $this->language->tenant_id = $tenant->id;
                $this->language->save();
            }
        }

        TenantSetting::updateOrCreate(
            ['group' => 'system', 'key' => 'active_language', 'tenant_id' => $tenant->id],
            ['value' => $this->language->code]
        );
        // Assign language to tenant : End

        // Add Role to tenant : Start
        $name = 'Agent';

        $this->role = Role::firstOrCreate([
            'name' => $name,
            'tenant_id' => $tenant->id,
            'guard_name' => 'web',
        ]);

        $permissionNames = ['tenant.chat.view'];

        // Sync permissions by name
        $this->role->syncPermissions($permissionNames);

        // Add Role to tenant : End

        // Assign default statuses to the tenant : Start
        $statuses = config('custom-saas.tenant_status');
        foreach ($statuses as $status) {
            $status['tenant_id'] = $tenant->id; // Ensure tenant_id is set
            Status::updateOrCreate(
                ['name' => $status['name'], 'tenant_id' => $status['tenant_id']],
                $status
            );
        }
        // Assign default statuses to the tenant : End

        // Assign default source to the tenant : Start
        $sources = config('custom-saas.tenant_source');

        foreach ($sources as $source) {
            $source['tenant_id'] = $tenant->id; // Ensure tenant_id is set
            Source::updateOrCreate(
                ['name' => $source['name'], 'tenant_id' => $source['tenant_id']],
                $source
            );
        }
        // Assign default source to the tenant : End

        // Create dynamic tables for the tenant : Start
        $tables = [
            'chats' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->comment('Reference to tenant');
                $table->string('name', 100);
                $table->string('receiver_id', 20);
                $table->text('last_message')->nullable();
                $table->dateTime('last_msg_time')->nullable();
                $table->string('wa_no', 20)->nullable();
                $table->string('wa_no_id', 20)->nullable();
                $table->dateTime('time_sent');
                $table->string('type', 500)->nullable();
                $table->string('type_id', 500)->nullable();
                $table->text('agent')->nullable();
                $table->boolean('is_ai_chat')->default(false);
                $table->text('ai_message_json')->nullable();
                $table->boolean('is_bots_stoped')->nullable();
                $table->dateTime('bot_stoped_time')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            },
            'chat_messages' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->comment('Reference to tenant');
                $table->unsignedInteger('interaction_id');
                $table->string('sender_id', 20);
                $table->string('url', 255)->nullable();
                $table->longText('message');
                $table->string('status', 20)->nullable();
                $table->text('status_message')->nullable();
                $table->dateTime('time_sent');
                $table->string('message_id', 500)->nullable();
                $table->string('staff_id', 500)->nullable();
                $table->string('type', 20)->nullable();
                $table->boolean('is_read')->default(false);
                $table->text('ref_message_id')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

                // Indexes for better performance
                $table->index('tenant_id');
                $table->index(['message_id', 'tenant_id'], 'idx_message_id_tenant_id'); // Critical for webhook status updates
                $table->index(['tenant_id', 'interaction_id']); // For chat message queries
                $table->index(['tenant_id', 'time_sent']); // For chronological queries
            },
            'contacts' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->comment('Reference to tenant');
                $table->string('firstname', 191);
                $table->string('lastname', 191);
                $table->string('company', 191)->nullable();
                $table->enum('type', ['lead', 'customer'])->default('lead');
                $table->text('description')->nullable();
                $table->integer('country_id')->nullable();
                $table->string('zip', 15)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->text('address')->nullable();
                $table->unsignedBigInteger('assigned_id')->nullable();
                $table->unsignedBigInteger('status_id');
                $table->unsignedBigInteger('source_id');
                $table->string('email', 191)->nullable();
                $table->string('website', 100)->nullable();
                $table->string('phone', 20)->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->integer('addedfrom')->nullable();
                $table->json('custom_fields_data')->nullable();
                $table->dateTime('dateassigned')->nullable();
                $table->dateTime('last_status_change')->nullable();
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index('status_id');
                $table->index('source_id');
                $table->json('group_id')->nullable();
                $table->index('assigned_id');
                $table->index('type');
                $table->index('phone');
                $table->index(['firstname', 'lastname']);
                $table->index('is_enabled');
                $table->index('tenant_id');
                $table->index(['tenant_id', 'email']);
                $table->index(['tenant_id', 'phone']);
                $table->index(['tenant_id', 'status_id']);
                $table->index(['tenant_id', 'created_at']);
                $table->index(['tenant_id', 'firstname', 'lastname', 'email', 'phone'], 'idx_tenant_contact_search');
            },
            'contact_notes' => function (Blueprint $table) use ($tenant) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->comment('Reference to tenant');
                $table->unsignedBigInteger('contact_id');
                $table->text('notes_description');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('contact_id')->references('id')->on($tenant->subdomain.'_contacts')->onDelete('cascade');

                $table->index('contact_id');
                $table->index('tenant_id');
            },
        ];

        $tableNames = [];
        foreach ($tables as $baseName => $schema) {
            $tableName = "{$tenant->subdomain}_{$baseName}";
            $tableNames[$baseName] = $tableName;

            if (! Schema::hasTable($tableName)) {
                Schema::create($tableName, $schema);
            }
        }

        // Create tenant default settings : Start
        $tenantSettings = config('custom-saas.tenant_default_settings');
        foreach ($tenantSettings as $setting) {
            $setting['tenant_id'] = $tenant->id; // Ensure tenant_id is set
            $setting['value'] = ($setting['key'] == 'tenant_table_names') ? json_encode($tableNames) : ($setting['key'] == 'timezone' && ! empty($tenant->timezone) ? $tenant->timezone : $setting['value']);
            TenantSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key'], 'tenant_id' => $setting['tenant_id']],
                ['value' => $setting['value']]
            );
        }
        // Create tenant default settings : End

        // Create tenant email templates : Start
        $emailTemplates = config('custom-saas.tenant_email_templates');
        foreach ($emailTemplates as $template) {
            $template['tenant_id'] = $tenant->id; // Ensure tenant_id is set
            TenantEmailTemplate::updateOrInsert(
                ['slug' => $template['slug'], 'tenant_id' => $template['tenant_id']],
                $template
            );
        }
        // Create tenant email templates : End
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(TenantCreated::class, [self::class, 'handleTenantCreated']);
        $events->listen(TenantUpdated::class, [self::class, 'handleTenantUpdated']);
        $events->listen(TenantDeleted::class, [self::class, 'handleTenantDeleted']);
        $events->listen(TenantStatusChanged::class, [self::class, 'handleTenantStatusChanged']);
    }
}

/*
    * This class manages the caching of tenant data.
    * It listens to tenant-related events and updates the cache accordingly.
    * The cache is used to speed up tenant lookups and reduce database queries.
    * It also handles tenant status changes, ensuring that inactive tenants are not cached.
    * The cache is cleared when a tenant is deleted or updated.
*/
