<?php

namespace App\Services;

/**
 * Hook Registry Service
 *
 * This service provides documentation for all available hooks in the application.
 * It uses the millat/laravel-hooks package for the actual hook functionality.
 */
class HookRegistry
{
    /**
     * Available action hooks in the application
     */
    public static function getAvailableActions(): array
    {
        return [
            // User hooks
            'user.before_create' => 'Fired before a new user is created',
            'user.after_create' => 'Fired after a new user is created',
            'user.before_update' => 'Fired before a user is updated',
            'user.after_update' => 'Fired after a user is updated',
            'user.before_delete' => 'Fired before a user is deleted',
            'user.after_delete' => 'Fired after a user is deleted',
            'user.before_login' => 'Fired before a user logs in',
            'user.after_login' => 'Fired after a user logs in',
            'user.before_logout' => 'Fired before a user logs out',
            'user.after_logout' => 'Fired after a user logs out',
            'user.password_changed' => 'Fired when user password is changed',
            'user.role_changed' => 'Fired when user role is changed',
            'user.before_deactivate' => 'Fired before a user is deactivated',
            'user.after_deactivate' => 'Fired after a user is deactivated',
            'user.before_activate' => 'Fired before a user is activated',
            'user.after_activate' => 'Fired after a user is activated',

            // Tenant hooks
            'tenant.before_create' => 'Fired before a new tenant is created',
            'tenant.after_create' => 'Fired after a new tenant is created',
            'tenant.before_update' => 'Fired before a tenant is updated',
            'tenant.after_update' => 'Fired after a tenant is updated',
            'tenant.before_delete' => 'Fired before a tenant is deleted',
            'tenant.after_delete' => 'Fired after a tenant is deleted',
            'tenant.before_activate' => 'Fired before a tenant is activated',
            'tenant.after_activate' => 'Fired after a tenant is activated',
            'tenant.before_deactivate' => 'Fired before a tenant is deactivated',
            'tenant.after_deactivate' => 'Fired after a tenant is deactivated',
            'tenant.status_changed' => 'Fired when tenant status changes',

            // Subscription hooks
            'subscription.before_create' => 'Fired before a subscription is created',
            'subscription.after_create' => 'Fired after a subscription is created',
            'subscription.before_update' => 'Fired before a subscription is updated',
            'subscription.after_update' => 'Fired after a subscription is updated',
            'subscription.before_delete' => 'Fired before a subscription is deleted',
            'subscription.after_delete' => 'Fired after a subscription is deleted',
            'subscription.before_cancel' => 'Fired before a subscription is cancelled',
            'subscription.after_cancel' => 'Fired after a subscription is cancelled',
            'subscription.before_renew' => 'Fired before a subscription is renewed',
            'subscription.after_renew' => 'Fired after a subscription is renewed',
            'subscription.before_upgrade' => 'Fired before a subscription is upgraded',
            'subscription.after_upgrade' => 'Fired after a subscription is upgraded',
            'subscription.before_downgrade' => 'Fired before a subscription is downgraded',
            'subscription.after_downgrade' => 'Fired after a subscription is downgraded',

            // Plan hooks
            'plan.before_create' => 'Fired before a plan is created',
            'plan.after_create' => 'Fired after a plan is created',
            'plan.before_update' => 'Fired before a plan is updated',
            'plan.after_update' => 'Fired after a plan is updated',
            'plan.before_delete' => 'Fired before a plan is deleted',
            'plan.after_delete' => 'Fired after a plan is deleted',
            'plan.before_activate' => 'Fired before a plan is activated',
            'plan.after_activate' => 'Fired after a plan is activated',
            'plan.before_deactivate' => 'Fired before a plan is deactivated',
            'plan.after_deactivate' => 'Fired after a plan is deactivated',
            'plan.price_changed' => 'Fired when plan price is changed',

            // Feature hooks
            'feature.before_create' => 'Fired before a feature is created',
            'feature.after_create' => 'Fired after a feature is created',
            'feature.before_update' => 'Fired before a feature is updated',
            'feature.after_update' => 'Fired after a feature is updated',
            'feature.before_delete' => 'Fired before a feature is deleted',
            'feature.after_delete' => 'Fired after a feature is deleted',

            // Invoice hooks
            'invoice.before_create' => 'Fired before an invoice is created',
            'invoice.after_create' => 'Fired after an invoice is created',
            'invoice.before_update' => 'Fired before an invoice is updated',
            'invoice.after_update' => 'Fired after an invoice is updated',
            'invoice.before_delete' => 'Fired before an invoice is deleted',
            'invoice.after_delete' => 'Fired after an invoice is deleted',
            'invoice.before_paid' => 'Fired before an invoice is marked as paid',
            'invoice.after_paid' => 'Fired after an invoice is marked as paid',
            'invoice.before_void' => 'Fired before an invoice is voided',
            'invoice.after_void' => 'Fired after an invoice is voided',

            // Transaction hooks
            'transaction.before_create' => 'Fired before a transaction is created',
            'transaction.after_create' => 'Fired after a transaction is created',
            'transaction.before_update' => 'Fired before a transaction is updated',
            'transaction.after_update' => 'Fired after a transaction is updated',
            'transaction.before_approve' => 'Fired before a transaction is approved',
            'transaction.after_approve' => 'Fired after a transaction is approved',
            'transaction.before_reject' => 'Fired before a transaction is rejected',
            'transaction.after_reject' => 'Fired after a transaction is rejected',
            'transaction.succeeded' => 'Fired when a transaction succeeds',
            'transaction.failed' => 'Fired when a transaction fails',

            // API Token hooks
            'api_token.before_create' => 'Fired before an API token is created',
            'api_token.after_create' => 'Fired after an API token is created',
            'api_token.before_use' => 'Fired before an API token is used',
            'api_token.after_use' => 'Fired after an API token is used',
            'api_token.quota_exceeded' => 'Fired when API token quota is exceeded',

            // Email hooks
            'email.config_updated' => 'Fired when email configuration is updated',

            // Status hooks
            'status.before_create' => 'Fired before a status is created',
            'status.after_create' => 'Fired after a status is created',
            'status.before_update' => 'Fired before a status is updated',
            'status.after_update' => 'Fired after a status is updated',
            'status.before_delete' => 'Fired before a status is deleted',
            'status.after_delete' => 'Fired after a status is deleted',

            // Source hooks
            'source.before_create' => 'Fired before a source is created',
            'source.after_create' => 'Fired after a source is created',
            'source.before_update' => 'Fired before a source is updated',
            'source.after_update' => 'Fired after a source is updated',
            'source.before_delete' => 'Fired before a source is deleted',
            'source.after_delete' => 'Fired after a source is deleted',

            // Contact hooks
            'contact.before_create' => 'Fired before a contact is created',
            'contact.after_create' => 'Fired after a contact is created',
            'contact.before_update' => 'Fired before a contact is updated',
            'contact.after_update' => 'Fired after a contact is updated',
            'contact.before_delete' => 'Fired before a contact is deleted',
            'contact.after_delete' => 'Fired after a contact is deleted',

            'auth.before_attempt' => 'Fired before an authentication attempt',
            'auth.login_failed' => 'Fired when authentication fails',

            'model.booted' => 'Fired when a model is booted',
            'email.template_rendered' => 'Fired after an email template is rendered',

            'settings.before_save' => 'Fired before application settings are saved',
            'settings.after_save' => 'Fired after application settings are saved',
        ];
    }

    /**
     * Available filter hooks in the application
     */
    public static function getAvailableFilters(): array
    {
        return [
            // Email filters
            'email.subject' => 'Filter email subject',
            'email.content' => 'Filter email content',

            // Sidebar menu
            'tenant_sidebar.main_menu' => 'Filter main menu items',
            'tenant_sidebar.setup_menus' => 'Filter tenant setup menus',
            'admin_sidebar.main_menu' => 'Filter admin sidebar menus',
            'admin_sidebar.setup_menus' => 'Filter admin setup menus',

            'settings.value' => 'Filter application settings values',

        ];
    }

    /**
     * Register all documented hooks (for IDE autocomplete and documentation)
     */
    public static function registerDocumentedHooks(): void
    {
        // This method doesn't actually register hooks, it's for documentation
        // The actual hook registration is done via add_action() and add_filter()
        // when modules or services need to hook into these events.

        // Example of how to use hooks:
        /*
        // Add an action hook
        add_action('user.after_create', function($user) {
            // Do something after user is created
            Log::info('User created: ' . $user->name);
        });

        // Add a filter hook
        add_filter('user.email', function($email) {
            // Modify email before save
            return strtolower(trim($email));
        });
        */
    }

    /**
     * Get hook documentation for a specific hook
     */
    public static function getHookDocumentation(string $hook): ?string
    {
        $actions = static::getAvailableActions();
        $filters = static::getAvailableFilters();

        return $actions[$hook] ?? $filters[$hook] ?? null;
    }

    /**
     * Check if a hook is documented
     */
    public static function isDocumentedHook(string $hook): bool
    {
        $actions = static::getAvailableActions();
        $filters = static::getAvailableFilters();

        return isset($actions[$hook]) || isset($filters[$hook]);
    }
}
