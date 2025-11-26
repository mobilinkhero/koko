# WhatsApp Manual Connection Setup - Complete Documentation

## Overview
This document explains the complete manual WhatsApp Business Account connection process, including all data that gets saved to the database at each step.

---

## 🔄 Connection Flow

### Step 1: User Initiates Connection
**Location:** `app/Livewire/Tenant/Waba/ConnectWaba.php`

User provides:
- **WhatsApp Business Account ID** (`wm_business_account_id`)
- **Access Token** (`wm_access_token`)

### Step 2: Validation & Duplicate Check
**Method:** `connectAccount()` in `ConnectWaba.php`

Before saving, system validates:
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

## 💾 Database Storage

### Primary Table: `tenant_settings`

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

### Secondary Table: `whatsapp_templates`

**Purpose:** Stores WhatsApp message templates synced from API

**Model:** `app/Models/Tenant/WhatsappTemplate.php`

---

## 📝 Complete Data Saved - Step by Step

### Phase 1: Initial Connection (`connectAccount()`)

When user clicks "Connect" button, these records are saved to `tenant_settings`:

#### 1. Core Account Credentials
```php
save_tenant_setting('whatsapp', 'wm_business_account_id', $this->wm_business_account_id);
save_tenant_setting('whatsapp', 'wm_access_token', $this->wm_access_token);
```

**Database Records:**
- `tenant_id`: Current tenant ID
- `group`: `'whatsapp'`
- `key`: `'wm_business_account_id'`
- `value`: WhatsApp Business Account ID (e.g., `'123456789012345'`)

- `tenant_id`: Current tenant ID
- `group`: `'whatsapp'`
- `key`: `'wm_access_token'`
- `value`: Access Token (e.g., `'EAAxxxxxxxxxxxxx'`)

---

### Phase 2: Automatic Setup (If Admin Webhook Connected)

If admin has configured webhook globally, these are saved automatically:

#### 2. Connection Status Flags
```php
save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
```

#### 3. Facebook App Credentials (from admin settings)
```php
save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->admin_fb_app_id);
save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->admin_fb_app_secret);
```

**Then:** `loadTemplatesFromWhatsApp()` is called automatically

---

### Phase 3: Template Synchronization

**Method:** `loadTemplatesFromWhatsApp()` in `app/Traits/WhatsApp.php`

**Saves to:** `whatsapp_templates` table (NOT `tenant_settings`)

**Process:**
1. Fetches all message templates from WhatsApp API
2. For each template, saves/updates record in `whatsapp_templates` table
3. Deletes templates that no longer exist in API

**Template Record Structure:**
```php
WhatsappTemplate::updateOrCreate(
    [
        'template_id' => $templateData['id'],
        'tenant_id' => $tenant_id,
    ],
    [
        'template_name' => $templateData['name'],
        'language' => $templateData['language'],
        'status' => $templateData['status'],
        'category' => $templateData['category'],
        'header_data_text' => ...,
        'body_data' => ...,
        'footer_data' => ...,
        'buttons_data' => ...,
        // ... more fields
    ]
);
```

**Note:** This can create 0, 10, 50, 100+ records depending on user's WhatsApp account templates.

---

### Phase 4: Manual Webhook Setup (If Admin Webhook NOT Connected)

If admin webhook is not connected, user must complete Step 2 manually:

**Method:** `connectMetaWebhook()` in `ConnectWaba.php`

#### 4. User-Provided Facebook Credentials
```php
save_tenant_setting('whatsapp', 'wm_fb_app_id', $this->wm_fb_app_id);
save_tenant_setting('whatsapp', 'wm_fb_app_secret', $this->wm_fb_app_secret);
save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
save_tenant_setting('whatsapp', 'is_whatsmark_connected', $whatsapp_response['status'] ? 1 : 0);
```

**Then:** `loadTemplatesFromWhatsApp()` is called to sync templates

---

### Phase 5: Dashboard Initialization (When User Visits WABA Page)

**Component:** `DisconnectWaba.php` - `mount()` method

When user visits the WhatsApp Business Account dashboard page, additional data is automatically saved if empty:

#### 5. Phone Number Configuration
```php
if (empty($tenantWpSettings['wm_default_phone_number_id']) || empty($tenantWpSettings['wm_default_phone_number'])) {
    $default_number = preg_replace('/\D/', '', $this->phone_numbers[array_key_first($this->phone_numbers)]['display_phone_number']);
    $default_number_id = preg_replace('/\D/', '', $this->phone_numbers[array_key_first($this->phone_numbers)]['id']);
    save_tenant_setting('whatsapp', 'wm_default_phone_number', $default_number);
    save_tenant_setting('whatsapp', 'wm_default_phone_number_id', $default_number_id);
}
```

#### 6. Health Status Data
```php
if (empty($tenantWpSettings['wm_health_data']) || empty($tenantWpSettings['wm_health_check_time'])) {
    $helthStatus = $this->getHealthStatus();
    save_tenant_setting('whatsapp', 'wm_health_check_time', date('l jS F Y g:i:s a'));
    save_tenant_setting('whatsapp', 'wm_health_data', json_encode($helthStatus['data']));
}
```

#### 7. Profile Picture URL
```php
$data = $this->getProfile();
$profile_data = collect($data['data'])->firstWhere('messaging_product', 'whatsapp');
save_tenant_setting('whatsapp', 'wm_profile_picture_url', $profile_data['profile_picture_url'] ?? '');
```

#### 8. Webhook Connection Status (Updated)
```php
$webhook_configuration_url = array_column(array_column($phone_numbers['data'], 'webhook_configuration'), 'application');
if (in_array(route('whatsapp.webhook'), $webhook_configuration_url)) {
    save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
} else {
    save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
}
```

---

## 📊 Complete Settings Summary

### All `tenant_settings` Records Created (Group: `whatsapp`)

| Key | Value Type | When Saved | Required |
|-----|------------|-------------|----------|
| `wm_business_account_id` | String | Phase 1 | ✅ Yes |
| `wm_access_token` | String | Phase 1 | ✅ Yes |
| `is_webhook_connected` | Integer (0/1) | Phase 2 or 4 | ✅ Yes |
| `is_whatsmark_connected` | Integer (0/1) | Phase 2 or 4 | ✅ Yes |
| `wm_fb_app_id` | String | Phase 2 or 4 | ✅ Yes |
| `wm_fb_app_secret` | String | Phase 2 or 4 | ✅ Yes |
| `wm_default_phone_number` | String | Phase 5 | ⚠️ Optional |
| `wm_default_phone_number_id` | String | Phase 5 | ⚠️ Optional |
| `wm_health_check_time` | String (Date) | Phase 5 | ⚠️ Optional |
| `wm_health_data` | JSON String | Phase 5 | ⚠️ Optional |
| `wm_profile_picture_url` | String (URL) | Phase 5 | ⚠️ Optional |

### `whatsapp_templates` Table Records

**Created:** During Phase 3 (Template Synchronization)

**Count:** Variable (depends on user's WhatsApp account)

**Fields:**
- `tenant_id`
- `template_id` (from WhatsApp API)
- `template_name`
- `language`
- `status`
- `category`
- `header_data_text`
- `header_data_format`
- `body_data`
- `footer_data`
- `buttons_data`
- `header_params_count`
- `body_params_count`
- `footer_params_count`
- `header_file_url`
- `header_variable_value` (JSON)
- `body_variable_value` (JSON)
- `created_at`
- `updated_at`

---

## 🔧 Helper Functions

### `save_tenant_setting()`
**Location:** `app/Helpers/TenantHelper.php` (line 196)

**Functionality:**
- Uses `updateOrCreate()` - creates new or updates existing
- Updates in-memory config
- Clears cache for immediate effect

**Usage:**
```php
save_tenant_setting('whatsapp', 'key_name', 'value');
```

### `tenant_settings_by_group()`
**Location:** `app/Helpers/TenantHelper.php`

**Functionality:**
- Retrieves all settings for a specific group
- Uses caching (60 minutes)
- Returns array of key-value pairs

**Usage:**
```php
$whatsapp_settings = tenant_settings_by_group('whatsapp');
$account_id = $whatsapp_settings['wm_business_account_id'] ?? '';
```

---

## 🔐 Security & Validation

### 1. Duplicate Prevention
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

### 4. Webhook Validation
```php
$this->validate([
    'wm_fb_app_id' => 'required',
    'wm_fb_app_secret' => 'required',
]);
```

---

## 📋 Complete Flow Diagram

```
User Enters Credentials
        ↓
[Step 1] Validation & Duplicate Check
        ↓
[Phase 1] Save: wm_business_account_id, wm_access_token
        ↓
    ┌───────────────────────┐
    │ Admin Webhook         │
    │ Connected?            │
    └───────────────────────┘
        ↓                    ↓
      YES                   NO
        ↓                    ↓
[Phase 2] Auto Setup    [Phase 4] Manual Setup
- Save FB credentials   - User enters FB credentials
- Connect webhook       - Save FB credentials
- Verify connection     - Connect webhook
        ↓                    ↓
[Phase 3] Sync Templates (loadTemplatesFromWhatsApp)
- Save to whatsapp_templates table
        ↓
[Phase 5] User Visits Dashboard (DisconnectWaba mounts)
- Save phone numbers (if empty)
- Save health data (if empty)
- Save profile picture URL
- Update webhook status
        ↓
Connection Complete ✅
```

---

## 🗂️ Related Files

### Components
- **Connection Component:** `app/Livewire/Tenant/Waba/ConnectWaba.php`
- **Dashboard Component:** `app/Livewire/Tenant/Waba/DisconnectWaba.php`

### Models
- **Settings Model:** `app/Models/Tenant/TenantSetting.php`
- **Template Model:** `app/Models/Tenant/WhatsappTemplate.php`

### Traits
- **WhatsApp Trait:** `app/Traits/WhatsApp.php`
  - `loadTemplatesFromWhatsApp()` - Syncs templates
  - `getPhoneNumbers()` - Gets phone numbers
  - `getHealthStatus()` - Gets health status
  - `getProfile()` - Gets profile data
  - `connectWebhook()` - Connects webhook

### Helpers
- **Tenant Helper:** `app/Helpers/TenantHelper.php`
  - `save_tenant_setting()` - Save single setting
  - `tenant_settings_by_group()` - Get all settings in group
  - `save_batch_tenant_setting()` - Save multiple settings

### Views
- **Connection View:** `resources/views/livewire/tenant/waba/connect-waba.blade.php`
- **Dashboard View:** `resources/views/livewire/tenant/waba/disconnect-waba.blade.php`

### Database
- **Settings Migration:** `platform/packages/corbital/laravel-settings/database/migrations/2025_03_26_121434_create_tenant_settings_table.php`

---

## 📝 Example Database Records

### `tenant_settings` Table (Complete Example)

```sql
-- Phase 1: Initial Connection
INSERT INTO tenant_settings (tenant_id, `group`, `key`, value) VALUES
(1, 'whatsapp', 'wm_business_account_id', '123456789012345'),
(1, 'whatsapp', 'wm_access_token', 'EAAxxxxxxxxxxxxx');

-- Phase 2/4: Webhook Setup
INSERT INTO tenant_settings (tenant_id, `group`, `key`, value) VALUES
(1, 'whatsapp', 'is_webhook_connected', '1'),
(1, 'whatsapp', 'is_whatsmark_connected', '1'),
(1, 'whatsapp', 'wm_fb_app_id', '987654321'),
(1, 'whatsapp', 'wm_fb_app_secret', 'abc123def456');

-- Phase 5: Dashboard Initialization
INSERT INTO tenant_settings (tenant_id, `group`, `key`, value) VALUES
(1, 'whatsapp', 'wm_default_phone_number', '1234567890'),
(1, 'whatsapp', 'wm_default_phone_number_id', '987654321098765'),
(1, 'whatsapp', 'wm_health_check_time', 'Monday 15th January 2025 10:30:00 am'),
(1, 'whatsapp', 'wm_health_data', '{"health_status":{"status":"GREEN"}}'),
(1, 'whatsapp', 'wm_profile_picture_url', 'https://graph.facebook.com/...');
```

### `whatsapp_templates` Table (Example)

```sql
INSERT INTO whatsapp_templates (tenant_id, template_id, template_name, language, status, category, body_data) VALUES
(1, '123456789', 'hello_world', 'en_US', 'APPROVED', 'UTILITY', 'Hello {{1}}!'),
(1, '123456790', 'welcome_message', 'en_US', 'APPROVED', 'MARKETING', 'Welcome to {{1}}!');
```

---

## ⚠️ Important Notes

1. **Unique Constraint:** The `tenant_settings` table has a unique constraint on `(tenant_id, group, key)`, ensuring one value per key per tenant.

2. **UpdateOrCreate Pattern:** All saves use `updateOrCreate()`, so re-saving the same data is safe and won't create duplicates.

3. **Caching:** Settings are cached for 60 minutes. Cache is cleared when settings are updated.

4. **Template Sync:** Templates are synced from WhatsApp API and stored locally. Templates deleted from API are also deleted from local database.

5. **Optional Fields:** Some fields (phone numbers, health data) are only saved if empty, to avoid overwriting user preferences.

6. **Automatic vs Manual:** If admin webhook is configured, setup is automatic. Otherwise, user must complete webhook setup manually.

---

## 🔄 Data Flow Summary

1. **User Input** → Validation → Duplicate Check
2. **Save Credentials** → `tenant_settings` (2 records)
3. **Auto/Manual Setup** → `tenant_settings` (4 more records)
4. **Template Sync** → `whatsapp_templates` (N records)
5. **Dashboard Load** → `tenant_settings` (5 more records if empty)

**Total:** 
- **Minimum:** 6 records in `tenant_settings` + 0 in `whatsapp_templates`
- **Maximum:** 12 records in `tenant_settings` + 100+ in `whatsapp_templates`

---

## 🚀 Next Steps

This documentation covers the **manual connection setup**. Configuration documentation will be added separately.

