# WhatsApp Embedded Signup - Complete Documentation

## Overview
This document explains the WhatsApp Cloud API Embedded Signup implementation, how it works, and how it integrates seamlessly with the existing manual connection system.

---

## 🎯 Key Features

### Compatibility with Manual Connection
- **Same Data Structure**: Uses identical `tenant_settings` structure as manual connection
- **No Breaking Changes**: Existing manual connections continue to work
- **Unified Code Path**: Both methods save data to the same database fields
- **Template Sync**: Uses the same `loadTemplatesFromWhatsApp()` method

### Advantages Over Manual Connection
- **Simplified Setup**: No need to manually copy credentials
- **Faster Onboarding**: Reduces 10-15 steps to a single click
- **Automatic Webhook**: Webhooks configured automatically
- **Reduced Errors**: Less user input = fewer mistakes
- **Better UX**: Professional Facebook OAuth flow

---

## 🔄 Complete Flow Comparison

### Manual Connection Flow (Existing)
```
1. User gets WABA ID manually
2. User generates Access Token manually
3. User enters credentials
4. System saves credentials
5. [If no admin webhook] User enters FB App credentials
6. [If no admin webhook] User manually configures webhook
7. System syncs templates
8. Connection complete
```

### Embedded Signup Flow (New)
```
1. User clicks "Connect with Facebook"
2. Facebook OAuth popup appears
3. User authorizes app
4. Facebook returns: Auth Code + WABA ID + Phone ID
5. System exchanges code for Access Token
6. System automatically configures webhook
7. System syncs templates
8. Connection complete
```

---

## 📊 Data Structure Compatibility

### Both Methods Save IDENTICAL Data

| Setting Key | Manual Source | Embedded Source | Required |
|------------|---------------|-----------------|----------|
| `wm_business_account_id` | User input | Facebook API | ✅ |
| `wm_access_token` | User input | Token exchange | ✅ |
| `wm_fb_app_id` | User/Admin input | Admin config | ✅ |
| `wm_fb_app_secret` | User/Admin input | Admin config | ✅ |
| `is_webhook_connected` | Set to 1 | Set to 1 | ✅ |
| `is_whatsmark_connected` | Set to 1 | Set to 1 | ✅ |
| `wm_default_phone_number_id` | Auto (Phase 5) | From Facebook | ⚠️ |
| `wm_default_phone_number` | Auto (Phase 5) | From Facebook | ⚠️ |
| `embedded_signup_completed_at` | Not set | Timestamp | 📝 |
| `whatsapp_onboarding_method` | Not set | 'embedded_signup' | 📝 |
| `whatsapp_phone_numbers_data` | Auto (Phase 5) | From Facebook | 📝 |
| `whatsapp_onboarding_raw_data` | Not set | Full metadata | 📝 |

**Legend:**
- ✅ Required for both methods
- ⚠️ Optional/Auto-populated
- 📝 Additional tracking fields

---

## 🏗️ Implementation Architecture

### 1. Service Layer
**File:** `app/Services/WhatsAppEmbeddedSignupService.php`

**Responsibilities:**
- Exchange authorization code for access token
- Fetch phone numbers from WABA
- Register phone number if needed
- Configure webhooks automatically
- Return data in format compatible with manual connection

**Key Methods:**
```php
processEmbeddedSignup($data, $appId, $appSecret, $tenantId)
  ├── exchangeCodeForToken()      // Get access token
  ├── getPhoneNumbers()            // Get WABA phone numbers
  ├── registerPhoneNumber()        // Register if needed
  ├── setupWebhook()               // Auto-configure webhook
  └── Returns data array compatible with tenant_settings
```

### 2. Livewire Component
**File:** `app/Livewire/Tenant/Waba/ConnectWaba.php`

**New Method:** `processEmbeddedSignup()`
```php
public function processEmbeddedSignup($requestCode, $wabaId, $phoneNumberId, $isAppOnboarding)
{
    // 1. Validate inputs
    // 2. Check for duplicates (same as manual)
    // 3. Call service to process
    // 4. Save all settings to tenant_settings
    // 5. Sync templates (same as manual)
    // 6. Redirect to dashboard
}
```

### 3. Frontend JavaScript
**File:** `resources/views/livewire/tenant/waba/connect-waba.blade.php`

**Flow:**
```javascript
1. Initialize Facebook SDK
2. User clicks button → FB.login()
3. Receive authorization code
4. Listen for postMessage from Facebook
5. Capture WABA ID + Phone Number ID
6. Send all data to Livewire component
7. Component processes via service
```

---

## 🔧 Admin Configuration Requirements

### Settings Required (Group: `whatsapp`)

| Setting | Description | Where to Get |
|---------|-------------|--------------|
| `wm_fb_app_id` | Facebook App ID | https://developers.facebook.com/apps |
| `wm_fb_app_secret` | Facebook App Secret | App Settings > Basic |
| `wm_fb_config_id` | Configuration ID | Facebook Login for Business |

### How Admin Configures

1. **Create Facebook App**
   - Go to https://developers.facebook.com/apps
   - Create new app (Business type)
   - Add WhatsApp product

2. **Become Tech Provider**
   - Follow: https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers
   - Request permissions:
     - `whatsapp_business_management`
     - `whatsapp_business_messaging`
     - `public_profile`
     - `email`

3. **Create Configuration**
   - Go to Facebook Login for Business
   - Create configuration
   - Get Configuration ID
   - Save all three values to admin settings

---

## 💾 Complete Database Flow

### Phase 1: User Clicks Connect (Frontend)
```javascript
// JavaScript captures:
- authCode (from FB.login)
- wabaId (from postMessage event)
- phoneNumberId (from postMessage event)
- isAppOnboarding (YES/NO)

// Sends to Livewire:
@this.processEmbeddedSignup(authCode, wabaId, phoneNumberId, isAppOnboarding)
```

### Phase 2: Livewire Receives Data
```php
// ConnectWaba.php
public function processEmbeddedSignup($requestCode, $wabaId, $phoneNumberId, $isAppOnboarding)
{
    // Validate
    // Check duplicates
    // Call service
    $service = new WhatsAppEmbeddedSignupService();
    $result = $service->processEmbeddedSignup(...);
    
    // Save all settings
    foreach ($result['data'] as $key => $value) {
        save_tenant_setting('whatsapp', $key, $value);
    }
    
    // Sync templates
    $this->loadTemplatesFromWhatsApp();
}
```

### Phase 3: Service Processes
```php
// WhatsAppEmbeddedSignupService.php
public function processEmbeddedSignup($data, $appId, $appSecret, $tenantId)
{
    // 1. Exchange code for token
    $accessToken = $this->exchangeCodeForToken($data['request_code'], $appId, $appSecret);
    
    // 2. Get phone numbers
    $phoneNumbers = $this->getPhoneNumbers($data['waba_id']);
    
    // 3. Register phone if needed
    if ($this->needsRegistration($phoneNumber)) {
        $this->registerPhoneNumber($data['phone_number_id']);
    }
    
    // 4. Setup webhook
    $webhookSetup = $this->setupWebhook($data['waba_id']);
    
    // 5. Return data in manual-compatible format
    return [
        'status' => true,
        'data' => [
            'wm_business_account_id' => $wabaId,
            'wm_access_token' => $accessToken,
            'wm_fb_app_id' => $appId,
            'wm_fb_app_secret' => $appSecret,
            'is_webhook_connected' => 1,
            'is_whatsmark_connected' => 1,
            'wm_default_phone_number_id' => $phoneNumberId,
            'wm_default_phone_number' => $cleanedNumber,
            'embedded_signup_completed_at' => now(),
            'whatsapp_onboarding_method' => 'embedded_signup',
            'whatsapp_phone_numbers_data' => json_encode($phoneNumbers),
            'whatsapp_onboarding_raw_data' => json_encode([...])
        ]
    ];
}
```

### Phase 4: Data Saved to Database
```sql
-- All saved to tenant_settings table
INSERT INTO tenant_settings (tenant_id, `group`, `key`, value) VALUES
(1, 'whatsapp', 'wm_business_account_id', '123456789012345'),
(1, 'whatsapp', 'wm_access_token', 'EAAxxxxxxxxxxxxx'),
(1, 'whatsapp', 'wm_fb_app_id', '987654321'),
(1, 'whatsapp', 'wm_fb_app_secret', 'abc123def456'),
(1, 'whatsapp', 'is_webhook_connected', '1'),
(1, 'whatsapp', 'is_whatsmark_connected', '1'),
(1, 'whatsapp', 'wm_default_phone_number', '1234567890'),
(1, 'whatsapp', 'wm_default_phone_number_id', '987654321098765'),
(1, 'whatsapp', 'embedded_signup_completed_at', '2025-11-26 14:30:00'),
(1, 'whatsapp', 'whatsapp_onboarding_method', 'embedded_signup'),
(1, 'whatsapp', 'whatsapp_phone_numbers_data', '{"data":[...]}'),
(1, 'whatsapp', 'whatsapp_onboarding_raw_data', '{"waba_id":"..."}');
```

### Phase 5: Template Sync (Same as Manual)
```php
// Uses existing WhatsApp trait method
$this->loadTemplatesFromWhatsApp();

// Saves to whatsapp_templates table
WhatsappTemplate::updateOrCreate(
    ['template_id' => $id, 'tenant_id' => $tenantId],
    [...template data...]
);
```

---

## 🔐 Security & Validation

### Duplicate Prevention (Same as Manual)
```php
// Check if WABA ID already used by another tenant
$is_found_wm_business_account_id = TenantSetting::where('key', 'wm_business_account_id')
    ->where('value', 'like', "%$wabaId%")
    ->where('tenant_id', '!=', tenant_id())
    ->exists();

// Reject if found
if ($is_found_wm_business_account_id) {
    throw new Exception('This WhatsApp Business Account is already in use');
}
```

### Token Security
- Authorization code is single-use
- Access tokens stored encrypted in database
- App Secret never exposed to frontend
- All API calls use HTTPS

### Webhook Verification
- Unique verify token per tenant: `sha1(app_key + tenant_id)`
- Webhooks registered with Facebook automatically
- Callback URL validated by Facebook

---

## 🧪 Testing Guide

### Test Embedded Signup Flow

1. **Admin Setup:**
   ```php
   // Save to settings table (group: whatsapp)
   save_setting('whatsapp', 'wm_fb_app_id', 'YOUR_APP_ID');
   save_setting('whatsapp', 'wm_fb_app_secret', 'YOUR_APP_SECRET');
   save_setting('whatsapp', 'wm_fb_config_id', 'YOUR_CONFIG_ID');
   ```

2. **Tenant Attempts Connection:**
   - Visit: `https://yourdomain.com/tenant/connect`
   - Should see "Connect with Facebook" button
   - Click button → Facebook popup appears
   - Complete WhatsApp setup in Facebook
   - Should redirect to dashboard

3. **Verify Data Saved:**
   ```sql
   SELECT * FROM tenant_settings 
   WHERE tenant_id = 1 AND `group` = 'whatsapp';
   ```

   Should see all required fields:
   - `wm_business_account_id`
   - `wm_access_token`
   - `is_webhook_connected` = 1
   - `is_whatsmark_connected` = 1
   - `embedded_signup_completed_at`

4. **Verify Templates Synced:**
   ```sql
   SELECT COUNT(*) FROM whatsapp_templates 
   WHERE tenant_id = 1;
   ```

5. **Test Sending Message:**
   - Should work identically to manual connection
   - Uses same WhatsApp trait methods

### Test Manual Connection Still Works

1. **New Tenant Uses Manual:**
   - Enter WABA ID manually
   - Enter Access Token manually
   - Complete webhook setup if needed
   - Verify templates sync

2. **Verify No Conflicts:**
   - Both methods should work simultaneously
   - Different tenants can use different methods
   - Data structure is identical

---

## 🚨 Error Handling

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Embedded signup not configured" | Admin settings missing | Configure App ID, Secret, Config ID |
| "Failed to get authorization code" | User denied permission | User must authorize app |
| "WABA ID is required" | Facebook didn't return WABA | Check app permissions |
| "This account is already in use" | Duplicate WABA ID | Each WABA can only be used by one tenant |
| "Failed to setup webhook" | Invalid webhook URL | Check route exists: `whatsapp.webhook` |
| "Template sync failed" | Invalid access token | Token may have expired |

### Debugging Tips

1. **Check Console Logs:**
   ```javascript
   // Browser console shows:
   ✓ Facebook SDK initialized
   ✓ Auth code received
   ✓ WABA ID: 123456789012345
   ✓ Sending setup data to backend
   ```

2. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/whatsapp.log
   ```

3. **Check Database:**
   ```sql
   SELECT * FROM tenant_settings 
   WHERE tenant_id = ? AND `group` = 'whatsapp';
   ```

---

## 📝 Language Files

### Add These Translations

```php
// resources/lang/en/messages.php
return [
    // Embedded Signup
    'embedded_signup' => 'Embedded Signup',
    'emb_signup_info' => 'Connect your WhatsApp Business Account with just one click using Facebook',
    'connect_with_facebook' => 'Connect with Facebook',
    'embedded_signup_not_configured' => 'Embedded signup is not configured. Please contact administrator.',
    'invalid_data_received_from_facebook' => 'Invalid data received from Facebook. Please try again.',
    'failed_to_get_auth_code' => 'Failed to get authorization code from Facebook.',
    'setup_timeout_message' => 'Setup timeout: Please complete the WhatsApp setup flow within 2 minutes.',
    'user_cancelled_login' => 'You cancelled the login or did not fully authorize the app.',
    'whatsapp_setup_cancelled' => 'WhatsApp setup was cancelled.',
    'connection_successful_template_sync_failed' => 'Connection successful but template synchronization failed',
    'whatsapp_connected_successfully' => 'WhatsApp connected successfully!',
    
    // Existing (ensure these exist)
    'connection_failed' => 'Connection failed',
    'you_cant_use_this_details_already_used_by_other' => 'This WhatsApp Business Account is already in use by another user.',
];
```

---

## 🔄 Migration from Manual to Embedded

### Existing Manual Connections
- **No Action Needed**: Existing connections continue to work
- **Can Reconnect**: Disconnect and use embedded signup for faster setup
- **Data Preserved**: All templates and messages remain intact

### For New Tenants
- **Recommended**: Use embedded signup for simplicity
- **Fallback**: Manual connection available if Facebook issues occur
- **Choice**: System detects and shows both options

---

## 📋 Files Modified/Created

### New Files
1. `app/Services/WhatsAppEmbeddedSignupService.php` - Core service
2. `WHATSAPP_EMBEDDED_SIGNUP_SETUP.md` - This documentation

### Modified Files
1. `app/Livewire/Tenant/Waba/ConnectWaba.php` - Added `processEmbeddedSignup()`
2. `resources/views/livewire/tenant/waba/connect-waba.blade.php` - Updated JavaScript

### Unchanged Files (Uses Same Code)
1. `app/Traits/WhatsApp.php` - Template sync, API calls
2. `app/Models/Tenant/TenantSetting.php` - Data storage
3. `app/Models/Tenant/WhatsappTemplate.php` - Template storage
4. `app/Helpers/TenantHelper.php` - Helper functions

---

## ✅ Validation Checklist

### Before Going Live

- [ ] Admin has configured App ID, Secret, Config ID
- [ ] Facebook app is verified (Meta verification)
- [ ] App has tech provider status
- [ ] Required permissions granted
- [ ] Test embedded signup with test account
- [ ] Test manual connection still works
- [ ] Verify templates sync correctly
- [ ] Test duplicate prevention
- [ ] Check webhook configuration
- [ ] Test sending messages
- [ ] Review error handling
- [ ] Check language translations
- [ ] Monitor logs for errors

---

## 🆘 Support Resources

### Facebook Documentation
- [Embedded Signup Overview](https://developers.facebook.com/docs/whatsapp/embedded-signup/)
- [Tech Provider Guide](https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers)
- [WhatsApp Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api)

### Internal Documentation
- [WHATSAPP_MANUAL_CONNECTION_SETUP.md](./WHATSAPP_MANUAL_CONNECTION_SETUP.md)
- [WHATSAPP_MANUAL_CONNECTION_FLOW.md](./WHATSAPP_MANUAL_CONNECTION_FLOW.md)

---

## 🎉 Summary

### What We Achieved
✅ Implemented Facebook Embedded Signup for WhatsApp
✅ **100% Compatible** with existing manual connection
✅ **Same Database Structure** - no breaking changes
✅ **Unified Code Path** - uses same traits and helpers
✅ **Better UX** - one-click connection vs 10+ steps
✅ **Automatic Webhook** - no manual configuration
✅ **Error Handling** - comprehensive validation
✅ **Well Documented** - complete setup and debugging guide

### Key Benefits
- **For Tenants**: Faster, simpler WhatsApp connection
- **For Admins**: Less support tickets, happier users
- **For Developers**: Clean code, well documented, maintainable

### Next Steps
1. Configure admin settings
2. Test with sandbox account
3. Deploy to production
4. Monitor and optimize

---

**Last Updated:** November 26, 2025
**Version:** 1.0.0
**Compatibility:** Fully compatible with existing manual connection system
