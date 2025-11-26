# WhatsApp Manual Connection Flow - Data Storage Analysis

## Overview
This document explains how the manual WhatsApp account connection works and where data is stored when users connect their WhatsApp Business Account.

---

## 🔄 Connection Flow

### Step 1: User Input
**Location:** `app/Livewire/Tenant/Waba/ConnectWaba.php`

When a user wants to connect their WhatsApp account, they provide:
- **WhatsApp Business Account ID** (`wm_business_account_id`)
- **Access Token** (`wm_access_token`)

### Step 2: Validation & Duplicate Check
**Method:** `connectAccount()` in `ConnectWaba.php` (lines 89-164)

Before saving, the system checks:
```php
// Check if account ID is already used by another tenant
$is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
    ->where('value', 'like', "%$this->wm_business_account_id%")
    ->where('tenant_id', '!=', tenant_id())
    ->exists();

// Check if access token is already used by another tenant
$is_found_wm_access_token = TenantSetting::where('key', 'wm_access_token')
    ->where('value', 'like', "%$this->wm_access_token%")
    ->where('tenant_id', '!=', tenant_id())
    ->exists();
```

**Purpose:** Prevents multiple tenants from using the same WhatsApp Business Account.

---

## 💾 Data Storage Location

### Database Table: `tenant_settings`

**Schema:**
```sql
CREATE TABLE `tenant_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`tenant_id`, `group`, `key`),
  INDEX (`tenant_id`, `group`)
);
```

**Model:** `app/Models/Tenant/TenantSetting.php`

---

## 📝 What Data Gets Saved

### When `connectAccount()` is called:

**Function:** `save_tenant_setting()` in `app/Helpers/TenantHelper.php` (lines 196-224)

The following settings are saved to `tenant_settings` table:

#### 1. Initial Account Details (Step 1)
```php
save_tenant_setting('whatsapp', 'wm_business_account_id', $this->wm_business_account_id);
save_tenant_setting('whatsapp', 'wm_access_token', $this->wm_access_token);
```

**Database Records:**
- `tenant_id`: Current tenant ID
- `group`: `'whatsapp'`
- `key`: `'wm_business_account_id'`
- `value`: The WhatsApp Business Account ID

- `tenant_id`: Current tenant ID
- `group`: `'whatsapp'`
- `key`: `'wm_access_token'`
- `value`: The access token

#### 2. If Admin Webhook is Connected (Automatic Setup)
```php
save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);
```

#### 3. If Admin Webhook is NOT Connected (Manual Setup Required)
```php
save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
```

### When `connectMetaWebhook()` is called (Step 2):

```php
save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->wm_fb_app_id);
save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->wm_fb_app_secret);
save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
save_tenant_setting('whatsapp', 'is_whatsmark_connected', $whatsapp_response['status'] ? 1 : 0);
```

---

## 🔧 How `save_tenant_setting()` Works

**Location:** `app/Helpers/TenantHelper.php` (lines 196-224)

```php
function save_tenant_setting(string $group, string $key, $value, $tenant_id = null)
{
    $tenant_id = $tenant_id ?? tenant_id();
    
    if (!$tenant_id) {
        return false;
    }
    
    // Uses updateOrCreate - creates new record or updates existing one
    $setting = TenantSetting::updateOrCreate(
        [
            'tenant_id' => $tenant_id,
            'group' => $group,
            'key' => $key,
        ],
        [
            'value' => $value,
        ]
    );
    
    // Update in-memory config
    config(["tenant.{$group}.{$key}" => $value]);
    
    // Clear cache
    Cache::forget("tenant_{$tenant_id}_setting_{$group}_{$key}");
    Cache::forget("tenant_{$tenant_id}_settings_group_{$group}");
    
    return $setting;
}
```

**Key Points:**
- Uses `updateOrCreate()` - creates new record if doesn't exist, updates if it does
- Unique constraint on `(tenant_id, group, key)` ensures one value per key per tenant
- Updates in-memory config for immediate access
- Clears cache to ensure fresh data

---

## 📊 Complete Data Structure Saved

### Example Records in `tenant_settings` Table:

| id | tenant_id | group    | key                      | value                    | created_at          | updated_at          |
|----|-----------|----------|--------------------------|--------------------------|---------------------|---------------------|
| 1  | 1         | whatsapp | wm_business_account_id   | 123456789012345          | 2025-01-15 10:00:00 | 2025-01-15 10:00:00 |
| 2  | 1         | whatsapp | wm_access_token          | EAAxxxxxxxxxxxxx         | 2025-01-15 10:00:00 | 2025-01-15 10:00:00 |
| 3  | 1         | whatsapp | wm_fb_app_id             | 987654321                | 2025-01-15 10:01:00 | 2025-01-15 10:01:00 |
| 4  | 1         | whatsapp | wm_fb_app_secret         | abc123def456             | 2025-01-15 10:01:00 | 2025-01-15 10:01:00 |
| 5  | 1         | whatsapp | is_webhook_connected     | 1                        | 2025-01-15 10:01:00 | 2025-01-15 10:01:00 |
| 6  | 1         | whatsapp | is_whatsmark_connected   | 1                        | 2025-01-15 10:01:00 | 2025-01-15 10:01:00 |

---

## 🔍 How Data is Retrieved

### Loading Settings on Mount:
**Location:** `ConnectWaba.php` - `mount()` method (lines 44-87)

```php
$whatsapp_settings = tenant_settings_by_group('whatsapp');
$this->wm_fb_app_id = $whatsapp_settings['wm_fb_app_id'] ?? '';
$this->wm_business_account_id = $whatsapp_settings['wm_business_account_id'] ?? '';
$this->wm_access_token = $whatsapp_settings['wm_access_token'] ?? '';
$this->is_whatsmark_connected = $whatsapp_settings['is_whatsmark_connected'] ?? '';
$this->is_webhook_connected = $whatsapp_settings['is_webhook_connected'] ?? '';
```

**Function:** `tenant_settings_by_group()` in `app/Helpers/TenantHelper.php`
- Retrieves all settings for a group
- Uses caching (60 minutes)
- Returns array of key-value pairs

---

## 🔐 Security & Validation

### 1. Duplicate Account Prevention
- Checks if Business Account ID is already used by another tenant
- Checks if Access Token is already used by another tenant
- Prevents account sharing between tenants

### 2. Permission Check
```php
if (!checkPermission('tenant.connect_account.connect')) {
    // Redirect to dashboard
}
```

### 3. Data Validation
```php
$this->validate([
    'wm_business_account_id' => 'required',
    'wm_access_token' => 'required',
]);
```

---

## 📋 Complete Flow Summary

1. **User visits connection page** → `ConnectWaba` component loads
2. **Component mounts** → Loads existing settings from `tenant_settings` table
3. **User enters credentials** → Business Account ID + Access Token
4. **User clicks "Connect"** → `connectAccount()` method called
5. **Validation** → Checks for duplicates and validates input
6. **Save to database** → Uses `save_tenant_setting()` to store in `tenant_settings` table
7. **Verification** → If admin webhook connected, verifies connection immediately
8. **Status update** → Sets `is_whatsmark_connected` and `is_webhook_connected` flags
9. **Cache cleared** → Ensures fresh data on next load
10. **Redirect** → User redirected to WhatsApp dashboard if successful

---

## 🗂️ Related Files

- **Component:** `app/Livewire/Tenant/Waba/ConnectWaba.php`
- **Model:** `app/Models/Tenant/TenantSetting.php`
- **Helper Function:** `app/Helpers/TenantHelper.php` (lines 196-224)
- **View:** `resources/views/livewire/tenant/waba/connect-waba.blade.php`
- **Database Migration:** `platform/packages/corbital/laravel-settings/database/migrations/2025_03_26_121434_create_tenant_settings_table.php`

---

## 💡 Key Takeaways

1. **All WhatsApp connection data is stored in `tenant_settings` table**
2. **Each tenant has isolated settings** (separated by `tenant_id`)
3. **Settings are grouped by `'whatsapp'` group**
4. **Uses `updateOrCreate()` pattern** - safe for repeated saves
5. **Caching is implemented** for performance
6. **Duplicate prevention** ensures account security
7. **Two-step process:** Account connection → Webhook setup

