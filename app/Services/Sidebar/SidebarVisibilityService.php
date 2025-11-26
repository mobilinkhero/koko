<?php

namespace App\Services\Sidebar;

use Illuminate\Support\Facades\Auth;

class SidebarVisibilityService
{
    /**
     * Check if user is admin
     */
    public static function isAdmin($user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user && $user->is_admin === true && $user->user_type === 'admin';
    }

    /**
     * Check if WhatsApp is not connected
     */
    public static function isWhatsAppNotConnected(): bool
    {
        return get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') == 0 ||
            get_tenant_setting_from_db('whatsapp', 'is_webhook_connected') == 0;
    }

    /**
     * Check if WhatsApp is connected
     */
    public static function isWhatsAppConnected(): bool
    {
        return get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected') == 1 &&
            get_tenant_setting_from_db('whatsapp', 'is_webhook_connected') == 1;
    }

    /**
     * Check if canned responses are enabled
     */
    public static function areCannedResponsesEnabled(): bool
    {
        return get_tenant_setting_from_db('whatsapp', 'enable_canned_response') == 1;
    }

    /**
     * Check if user has permission for subscription management
     */
    public static function canManageSubscriptions($user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user && $user->can('tenant.subscription.view');
    }

    /**
     * Check if user has permission for billing management
     */
    public static function canManageBilling($user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user && $user->can('tenant.billing.view');
    }

    /**
     * Check if Tickets module is enabled
     */
    public static function isTicketsModuleEnabled(): bool
    {
        return module_exists('Tickets') && module_enabled('Tickets');
    }

    /**
     * Check if LogViewer module is enabled and user is admin
     */
    public static function canViewLogs($user = null): bool
    {
        $user = $user ?? Auth::user();

        return module_exists('LogViewer') && module_enabled('LogViewer') &&
               $user && $user->is_admin === true && $user->user_type === 'admin';
    }

    /**
     * Check if user is tenant admin
     */
    public static function isTenantAdmin($user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user && $user->is_admin && $user->user_type === 'tenant';
    }

    /**
     * Check if LogViewer module is enabled and user is tenant admin
     */
    public static function canViewTenantLogs($user = null): bool
    {
        $user = $user ?? Auth::user();

        return module_exists('LogViewer') && module_enabled('LogViewer') &&
               $user && $user->is_admin && $user->user_type === 'tenant';
    }
}
