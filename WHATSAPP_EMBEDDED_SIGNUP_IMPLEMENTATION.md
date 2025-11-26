# WhatsApp Embedded Signup Implementation

## Overview
This document explains the WhatsApp Embedded Signup implementation for the tenant-based system, adapted from the Chatvvoold system to match the manual connection data flow exactly.

---

## 🎯 Key Design Principles

1. **Data Flow Compatibility**: Embedded signup saves data in EXACTLY the same way as manual connection
2. **No Duplicate Logic**: Both methods use the same 5-phase process
3. **Error Prevention**: Same validation, duplicate checking, and data structure
4. **Tenant Isolation**: Each tenant can have their own WhatsApp Business Account

---

## 📋 Prerequisites

### Admin Configuration Required

The admin must configure these settings in `WhatsappSettings`:

| Setting | Description | Example |
|---------|-------------|---------|
| `wm_fb_app_id` | Facebook App ID | `123456789012345` |
| `wm_fb_app_secret` | Facebook App Secret | `abc123def456...` |
| `wm_fb_config_id` | Facebook Login Configuration ID | `987654321098765` |
| `is_webhook_connected` | Whether admin webhook is configured | `1` or `0` |

### How Admin Configures Settings

Admin should navigate to **WhatsApp Settings** and enter:
1. Facebook App ID (from Meta Developer Console)
2. Facebook App Secret (from Meta Developer Console)
3. Config ID (from Facebook Login for Business Configuration)
4. Optionally configure webhook globally

---

## 🔄 Connection Flow Comparison

### Manual Connection Flow
```
User enters credentials manually
  ↓
Phase 1: Save wm_business_account_id, wm_access_token
  ↓
Phase 2/4: Webhook setup (auto or manual)
  ↓
Phase 3: Sync templates
  ↓
Phase 5: Dashboard initialization
```

### Embedded Signup Flow
```
User clicks "Connect with Facebook"
  ↓
Facebook SDK opens popup
  ↓
User authorizes in Facebook
  ↓
JavaScript captures: code, waba_id, phone_number_id
  ↓
Backend exchanges code for access_token
  ↓
Phase 1: Save wm_business_account_id, wm_access_token (SAME AS MANUAL)
  ↓
Phase 2/4: Webhook setup (auto or manual) (SAME AS MANUAL)
  ↓
Phase 3: Sync templates (SAME AS MANUAL)
  ↓
Phase 5: Dashboard initialization (SAME AS MANUAL)
```

**Result**: Both methods save data identically to `tenant_settings` table.

---

## 💾 Data Storage - Identical for Both Methods

Both manual and embedded signup save to the **same tables** with the **same structure**:

### `tenant_settings` Table Records

| Key | Value Type | Phase | Source |
|-----|------------|-------|--------|
| `wm_business_account_id` | String | 1 | Manual input OR Facebook response |
| `wm_access_token` | String | 1 | Manual input OR Token exchange |
| `is_webhook_connected` | Integer (0/1) | 2/4 | Auto OR Manual setup |
| `is_whatsmark_connected` | Integer (0/1) | 2/4 | Verification result |
| `wm_fb_app_id` | String | 2/4 | Admin settings |
| `wm_fb_app_secret` | String | 2/4 | Admin settings |
| `wm_default_phone_number` | String | 5 | Auto-detected |
| `wm_default_phone_number_id` | String | 5 | Auto-detected |
| `wm_health_check_time` | String | 5 | Health check |
| `wm_health_data` | JSON | 5 | Health check |
| `wm_profile_picture_url` | String | 5 | Profile data |

### `whatsapp_templates` Table Records

Both methods sync templates in Phase 3 using `loadTemplatesFromWhatsApp()`.

---

## 🛠️ Technical Implementation

### 1. Frontend: Facebook SDK Integration

**Location**: `resources/views/livewire/tenant/waba/connect-waba.blade.php`

```javascript
// Initialize Facebook SDK
window.fbAsyncInit = function() {
    FB.init({
        appId: '{{ $admin_fb_app_id }}',
        autoLogAppEvents: true,
        xfbml: true,
        version: 'v18.0'
    });
};
```

### 2. Embedded Signup Trigger

**Button**:
```html
<button type="button" onclick="launchWhatsAppSignup()">
    <i class="fab fa-facebook mr-2"></i>
    Connect with Facebook
</button>
```

**Function**:
```javascript
window.launchWhatsAppSignup = function() {
    FB.login(function(response) {
        if (response.authResponse) {
            tempAccessCode = response.authResponse.code;
            // Listen for WABA ID from postMessage
        }
    }, {
        config_id: '{{ $admin_fb_config_id }}',
        response_type: 'code',
        override_default_response_type: true,
        extras: {
            setup: {},
            sessionInfoVersion: '3'
        }
    });
};
```

### 3. PostMessage Listener

Captures WABA ID and Phone Number ID from Facebook:

```javascript
window.addEventListener('message', function(event) {
    if (event.origin === "https://www.facebook.com") {
        const data = JSON.parse(event.data);
        if (data.type === 'WA_EMBEDDED_SIGNUP' && data.event === 'FINISH') {
            const { phone_number_id, waba_id } = data.data;
            // Send to backend via Livewire
            @this.handleEmbeddedSignup(tempAccessCode, waba_id, phone_number_id);
        }
    }
});
```

### 4. Backend: Livewire Component

**Location**: `app/Livewire/Tenant/Waba/ConnectWaba.php`

**Method**: `handleEmbeddedSignup($requestCode, $wabaId, $phoneNumberId)`

**Process**:

1. **Validate Input**
   - Check if `$requestCode` and `$wabaId` are present
   - Check if admin settings are configured

2. **Exchange Code for Access Token**
   ```php
   $tokenResponse = Http::post('https://graph.facebook.com/v18.0/oauth/access_token', [
       'client_id' => $this->admin_fb_app_id,
       'client_secret' => $this->admin_fb_app_secret,
       'code' => $requestCode,
   ]);
   $accessToken = $tokenResponse->json()['access_token'];
   ```

3. **Check for Duplicates** (SAME AS MANUAL)
   ```php
   $is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
       ->where('value', 'like', "%$wabaId%")
       ->where('tenant_id', '!=', tenant_id())
       ->exists();
   ```

4. **Phase 1: Save Credentials** (SAME AS MANUAL)
   ```php
   save_tenant_setting('whatsapp', 'wm_business_account_id', $wabaId);
   save_tenant_setting('whatsapp', 'wm_access_token', $accessToken);
   ```

5. **Phase 2/4: Webhook Setup** (SAME AS MANUAL)
   - If admin webhook connected: auto-setup
   - Otherwise: move to step 2 for manual setup

6. **Phase 3: Sync Templates** (SAME AS MANUAL)
   ```php
   $response = $this->loadTemplatesFromWhatsApp();
   save_tenant_setting('whatsapp', 'is_whatsmark_connected', $response['status'] ? 1 : 0);
   ```

---

## 🔐 Security & Validation

### 1. Duplicate Prevention
Both methods prevent account sharing:
```php
// Check if WABA ID already used by another tenant
$is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
    ->where('value', 'like', "%$businessAccountId%")
    ->where('tenant_id', '!=', tenant_id())
    ->exists();
```

### 2. Permission Check
```php
if (!checkPermission('tenant.connect_account.connect')) {
    redirect(tenant_route('tenant.dashboard'));
}
```

### 3. Logging
All embedded signup steps are logged:
```php
whatsapp_log('Embedded Signup Started', 'info', [
    'has_code' => !empty($requestCode),
    'waba_id' => $wabaId,
]);
```

---

## 📊 Comparison: Old vs New System

### Chatvvoold (Old System)
- **Storage**: `vendor_settings` table
- **Scope**: Vendor-based
- **Helper**: `getVendorSettings()`
- **Route**: `vendor.whatsapp_setup.embedded_signup.write`

### Dis (New System)
- **Storage**: `tenant_settings` table
- **Scope**: Tenant-based
- **Helper**: `tenant_settings_by_group()`
- **Method**: Livewire `handleEmbeddedSignup()`

### Data Mapping

| Chatvvoold | Dis | Description |
|------------|-----|-------------|
| `embedded_setup_done_at` | `is_whatsmark_connected` | Connection timestamp/flag |
| `facebook_app_id` | `wm_fb_app_id` | Facebook App ID |
| `whatsapp_access_token` | `wm_access_token` | Access Token |
| `whatsapp_business_account_id` | `wm_business_account_id` | WABA ID |
| `current_phone_number_id` | `wm_default_phone_number_id` | Phone Number ID |

---

## 🚀 Setup Instructions

### For Admin

1. **Create Facebook App**
   - Go to https://developers.facebook.com/apps/
   - Create a new app (Business type)
   - Add WhatsApp product

2. **Get Config ID**
   - Go to Facebook Login for Business
   - Create configuration: https://developers.facebook.com/docs/whatsapp/embedded-signup/embed-the-flow#step-2--create-facebook-login-for-business-configuration
   - Copy Config ID

3. **Configure Settings**
   - Navigate to WhatsApp Settings in admin panel
   - Enter:
     - App ID
     - App Secret
     - Config ID
   - Optionally configure webhook

4. **Become Tech Provider** (Required)
   - Follow: https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers
   - Request advanced permissions:
     - `whatsapp_business_management`
     - `whatsapp_business_messaging`
     - `public_profile`
     - `email`

### For Tenant

1. **Navigate to Connect WABA Page**
   - Click "Connect WhatsApp Business Account"

2. **Choose Connection Method**
   - **Option A**: Click "Connect with Facebook" (Embedded Signup)
   - **Option B**: Enter credentials manually

3. **If Embedded Signup**:
   - Login to Facebook
   - Select WhatsApp Business Account
   - Authorize permissions
   - Click "Finish"
   - Wait for automatic setup

4. **If Admin Webhook NOT Configured**:
   - Complete Step 2: Webhook Setup
   - Enter Facebook App ID and Secret
   - Click "Connect Webhook"

5. **Done!**
   - Templates synced automatically
   - WhatsApp account ready to use

---

## 🐛 Troubleshooting

### Issue: "Embedded signup not configured"
**Cause**: Admin hasn't set up App ID, Secret, or Config ID  
**Solution**: Admin must configure settings in WhatsApp Settings

### Issue: "Access token not received"
**Cause**: Facebook authorization failed  
**Solution**: Check Facebook App permissions and try again

### Issue: "You can't use these details, already used by another tenant"
**Cause**: Another tenant already connected this WABA  
**Solution**: Each WABA can only be used by one tenant

### Issue: Templates not syncing
**Cause**: Access token invalid or API error  
**Solution**: Check logs in `storage/logs/whatsapp.log`

### Issue: Webhook not connecting
**Cause**: Admin webhook not configured, or Facebook App setup incomplete  
**Solution**: Complete Step 2 manual webhook setup

---

## 📝 Testing Checklist

### Before Testing
- [ ] Admin has configured `wm_fb_app_id`
- [ ] Admin has configured `wm_fb_app_secret`
- [ ] Admin has configured `wm_fb_config_id`
- [ ] Facebook App has WhatsApp product added
- [ ] Facebook App is published (or in development with test users)

### Test Embedded Signup
- [ ] Button appears when settings configured
- [ ] Clicking button opens Facebook popup
- [ ] Can authorize successfully
- [ ] WABA ID captured correctly
- [ ] Access token exchanged
- [ ] Data saved to `tenant_settings`
- [ ] Templates synced
- [ ] Redirect to dashboard works

### Test Manual Connection (Compare)
- [ ] Manual connection works
- [ ] Data structure identical to embedded
- [ ] Templates sync same way
- [ ] Webhook setup same

### Edge Cases
- [ ] User cancels Facebook login
- [ ] Network error during token exchange
- [ ] Duplicate WABA ID
- [ ] Admin webhook connected vs not connected
- [ ] Multiple tenants connecting different WABAs

---

## 📚 References

### Documentation
- [WhatsApp Embedded Signup](https://developers.facebook.com/docs/whatsapp/embedded-signup)
- [Facebook Login for Business](https://developers.facebook.com/docs/whatsapp/embedded-signup/embed-the-flow#step-2--create-facebook-login-for-business-configuration)
- [Tech Provider Setup](https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers)

### Related Files
- **Component**: `app/Livewire/Tenant/Waba/ConnectWaba.php`
- **View**: `resources/views/livewire/tenant/waba/connect-waba.blade.php`
- **Settings**: `app/Settings/WhatsappSettings.php`
- **Trait**: `app/Traits/WhatsApp.php`
- **Manual Flow Doc**: `WHATSAPP_MANUAL_CONNECTION_SETUP.md`

---

## ✅ Success Criteria

Both manual and embedded signup should result in:

1. **Same Data Structure**
   ```sql
   SELECT * FROM tenant_settings WHERE `group` = 'whatsapp' AND tenant_id = X;
   ```
   Should return identical keys regardless of connection method

2. **Same Template Count**
   ```sql
   SELECT COUNT(*) FROM whatsapp_templates WHERE tenant_id = X;
   ```
   Should be same for both methods

3. **Same Webhook Status**
   Both should have `is_webhook_connected = 1` (if admin configured)

4. **Same Final State**
   User lands on WABA dashboard with working WhatsApp connection

---

## 🎉 Conclusion

This implementation provides:
- ✅ Seamless embedded signup matching Chatvvoold
- ✅ Identical data flow to manual connection
- ✅ No duplicate logic or errors
- ✅ Tenant isolation and security
- ✅ Full backward compatibility

Both connection methods save data the **exact same way**, preventing any errors or inconsistencies.
