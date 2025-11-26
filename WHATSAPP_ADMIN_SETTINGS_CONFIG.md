# WhatsApp Admin Settings Configuration

## Required Settings for Embedded Signup

To enable WhatsApp Embedded Signup for your tenants, configure these settings in your admin panel or database.

---

## Settings Table Structure

### Table: `settings`

```sql
CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`group`, `key`)
);
```

---

## Required Settings

### 1. Facebook App ID

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'wm_fb_app_id', 'YOUR_APP_ID_HERE', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'YOUR_APP_ID_HERE', updated_at = NOW();
```

**Where to find:**
- https://developers.facebook.com/apps
- Select your app → Settings → Basic → App ID

**Example:** `1234567890123456`

---

### 2. Facebook App Secret

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'wm_fb_app_secret', 'YOUR_APP_SECRET_HERE', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'YOUR_APP_SECRET_HERE', updated_at = NOW();
```

**Where to find:**
- Same location as App ID
- Click "Show" button next to App Secret
- **IMPORTANT:** Keep this secret secure!

**Example:** `abc123def456ghi789jkl012mno345pq`

---

### 3. Configuration ID

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'wm_fb_config_id', 'YOUR_CONFIG_ID_HERE', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'YOUR_CONFIG_ID_HERE', updated_at = NOW();
```

**Where to find:**
- https://developers.facebook.com/apps/YOUR_APP_ID/fb-login/settings/
- Click "Create Configuration" under "Facebook Login for Business"
- Copy the Configuration ID

**Example:** `9876543210987654`

---

## Optional Settings

### 4. Webhook Verify Token (Required)

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'webhook_verify_token', 'YOUR_RANDOM_32_CHAR_TOKEN', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'YOUR_RANDOM_32_CHAR_TOKEN', updated_at = NOW();
```

**Purpose:** Used by Facebook to verify your webhook endpoint. Required for embedded signup.

**Generate random token:**
```php
// In tinker
save_setting('whatsapp', 'webhook_verify_token', \Illuminate\Support\Str::random(32));
```

**Important:** This token must match between embedded signup and your webhook controller.

---

### 5. Webhook Connection (Admin Level)

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'is_webhook_connected', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = '1', updated_at = NOW();
```

**Purpose:** If set to `1`, tenants skip webhook setup step in manual connection.

---

### 6. WhatsApp API Version

```sql
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'api_version', 'v23.0', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'v23.0', updated_at = NOW();
```

**Purpose:** Specify which WhatsApp API version to use.

---

## Complete SQL Script

```sql
-- WhatsApp Admin Settings Configuration
-- Replace placeholders with your actual values

-- Required Settings
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) VALUES
('whatsapp', 'wm_fb_app_id', 'REPLACE_WITH_YOUR_APP_ID', NOW(), NOW()),
('whatsapp', 'wm_fb_app_secret', 'REPLACE_WITH_YOUR_APP_SECRET', NOW(), NOW()),
('whatsapp', 'wm_fb_config_id', 'REPLACE_WITH_YOUR_CONFIG_ID', NOW(), NOW()),
('whatsapp', 'webhook_verify_token', UUID(), NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  value = VALUES(value),
  updated_at = NOW();

-- Optional Settings
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) VALUES
('whatsapp', 'is_webhook_connected', '0', NOW(), NOW()),
('whatsapp', 'api_version', 'v23.0', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  value = VALUES(value),
  updated_at = NOW();
```

**Note:** The `webhook_verify_token` uses `UUID()` to auto-generate a unique token. If you prefer a specific token, replace `UUID()` with your own value.

---

## Verification

### Check if settings are saved correctly

```sql
SELECT * FROM settings 
WHERE `group` = 'whatsapp' 
AND `key` IN ('wm_fb_app_id', 'wm_fb_app_secret', 'wm_fb_config_id', 'webhook_verify_token');
```

**Expected Output:**
```
| id | group     | key                   | value                          |
|----|-----------|-----------------------|--------------------------------|
| 1  | whatsapp  | wm_fb_app_id          | 1234567890123456              |
| 2  | whatsapp  | wm_fb_app_secret      | abc123def456ghi789jkl012mno345|
| 3  | whatsapp  | wm_fb_config_id       | 9876543210987654              |
| 4  | whatsapp  | webhook_verify_token  | a8f2c3e9d7b4f1a5c6e8d2b3a9f7c1|
```

---

## Using Helper Function

If you have the `save_setting()` helper function:

```php
// In tinker or migration
save_setting('whatsapp', 'wm_fb_app_id', 'YOUR_APP_ID');
save_setting('whatsapp', 'wm_fb_app_secret', 'YOUR_APP_SECRET');
save_setting('whatsapp', 'wm_fb_config_id', 'YOUR_CONFIG_ID');
save_setting('whatsapp', 'webhook_verify_token', \Illuminate\Support\Str::random(32));
```

---

## Admin Panel Configuration

If you have an admin settings page, add these fields:

```php
// Example form fields
<div class="form-group">
    <label>Facebook App ID</label>
    <input type="text" name="wm_fb_app_id" 
           value="{{ get_setting('whatsapp', 'wm_fb_app_id') }}" 
           class="form-control">
    <small>Get from Facebook Developers Console</small>
</div>

<div class="form-group">
    <label>Facebook App Secret</label>
    <input type="password" name="wm_fb_app_secret" 
           value="{{ get_setting('whatsapp', 'wm_fb_app_secret') }}" 
           class="form-control">
    <small>Keep this secret secure!</small>
</div>

<div class="form-group">
    <label>Configuration ID</label>
    <input type="text" name="wm_fb_config_id" 
           value="{{ get_setting('whatsapp', 'wm_fb_config_id') }}" 
           class="form-control">
    <small>From Facebook Login for Business</small>
</div>
```

---

## Environment Variables (Alternative)

You can also use `.env` file:

```env
# WhatsApp Embedded Signup Configuration
WHATSAPP_FB_APP_ID=your_app_id_here
WHATSAPP_FB_APP_SECRET=your_app_secret_here
WHATSAPP_FB_CONFIG_ID=your_config_id_here
```

Then in your config file:

```php
// config/whatsapp.php
return [
    'facebook' => [
        'app_id' => env('WHATSAPP_FB_APP_ID'),
        'app_secret' => env('WHATSAPP_FB_APP_SECRET'),
        'config_id' => env('WHATSAPP_FB_CONFIG_ID'),
    ],
];
```

---

## Security Best Practices

### 1. Encrypt App Secret

```php
use Illuminate\Support\Facades\Crypt;

// When saving
$encrypted = Crypt::encryptString($appSecret);
save_setting('whatsapp', 'wm_fb_app_secret', $encrypted);

// When retrieving
$decrypted = Crypt::decryptString(get_setting('whatsapp', 'wm_fb_app_secret'));
```

### 2. Restrict Access

```php
// Only super admin can view/edit
if (!auth()->user()->isSuperAdmin()) {
    abort(403, 'Unauthorized');
}
```

### 3. Audit Logging

```php
// Log when settings are changed
activity()
    ->causedBy(auth()->user())
    ->withProperties(['old' => $oldValue, 'new' => $newValue])
    ->log('WhatsApp settings updated');
```

---

## Testing Configuration

### Test if Embedded Signup is Available

```php
// In your controller or component
public function mount()
{
    $admin_fb_app_id = get_setting('whatsapp', 'wm_fb_app_id');
    $admin_fb_config_id = get_setting('whatsapp', 'wm_fb_config_id');
    
    $embedded_signup_configured = !empty($admin_fb_app_id) && !empty($admin_fb_config_id);
    
    // Pass to view
    return view('connect-waba', [
        'embedded_signup_configured' => $embedded_signup_configured
    ]);
}
```

### View Template

```blade
@if($embedded_signup_configured)
    <!-- Show embedded signup button -->
    <button onclick="launchWhatsAppSignup()">
        Connect with Facebook
    </button>
@else
    <!-- Show only manual connection -->
    <div class="alert alert-info">
        Embedded signup is not configured. Please use manual connection.
    </div>
@endif
```

---

## Troubleshooting

### Settings Not Showing in View

**Check:**
```php
// Verify settings exist
dd(get_settings_by_group('whatsapp'));

// Should return:
[
    'wm_fb_app_id' => 'xxx',
    'wm_fb_app_secret' => 'xxx',
    'wm_fb_config_id' => 'xxx',
]
```

### Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Migration File (Optional)

Create a migration to seed default settings:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedWhatsappAdminSettings extends Migration
{
    public function up()
    {
        DB::table('settings')->insert([
            [
                'group' => 'whatsapp',
                'key' => 'wm_fb_app_id',
                'value' => env('WHATSAPP_FB_APP_ID', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'whatsapp',
                'key' => 'wm_fb_app_secret',
                'value' => env('WHATSAPP_FB_APP_SECRET', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'whatsapp',
                'key' => 'wm_fb_config_id',
                'value' => env('WHATSAPP_FB_CONFIG_ID', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'whatsapp',
                'key' => 'webhook_verify_token',
                'value' => \Illuminate\Support\Str::random(32),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('settings')
            ->where('group', 'whatsapp')
            ->whereIn('key', ['wm_fb_app_id', 'wm_fb_app_secret', 'wm_fb_config_id', 'webhook_verify_token'])
            ->delete();
    }
}
```

---

## Summary

**Required for Embedded Signup:**
1. ✅ `wm_fb_app_id` - Facebook App ID
2. ✅ `wm_fb_app_secret` - Facebook App Secret
3. ✅ `wm_fb_config_id` - Configuration ID
4. ✅ `webhook_verify_token` - Webhook verification token (auto-generated if missing)

**Save to:**
- `settings` table with `group` = 'whatsapp'

**Verify:**
```sql
SELECT * FROM settings WHERE `group` = 'whatsapp';
```

**That's it!** Once these settings are configured, tenants will see the "Connect with Facebook" button.
