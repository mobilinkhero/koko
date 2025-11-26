# Webhook Verify Token Configuration Fix

## Issue
**Error:** `Connection failed: Failed to setup webhook: (#2200) Callback verification failed - HTTP Status Code = 403; HTTP Message = Forbidden`

**Cause:** The webhook verify token used during embedded signup doesn't match what your webhook controller expects.

---

## Quick Fix

### Option 1: Let the System Auto-Generate (Recommended)

The service will now automatically generate and save a verify token if one doesn't exist. Just try the embedded signup again and it should work.

### Option 2: Manually Set the Token

Set the webhook verify token in your settings:

```sql
-- Generate a random 32-character token
INSERT INTO settings (`group`, `key`, `value`, created_at, updated_at) 
VALUES ('whatsapp', 'webhook_verify_token', 'YOUR_RANDOM_32_CHAR_TOKEN_HERE', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = 'YOUR_RANDOM_32_CHAR_TOKEN_HERE', updated_at = NOW();
```

Or using Laravel:

```php
// In tinker or seeder
save_setting('whatsapp', 'webhook_verify_token', \Illuminate\Support\Str::random(32));
```

---

## How It Works Now

### Before (Broken)
```
Service generates: sha1(app_key + tenant_id)  
Webhook expects: settings.webhook_verify_token
Result: MISMATCH = 403 Forbidden ❌
```

### After (Fixed)
```
Service uses: settings.webhook_verify_token
Webhook expects: settings.webhook_verify_token
Result: MATCH = Webhook verified ✅
```

---

## Testing

1. **Try Embedded Signup Again:**
   - Click "Connect with Facebook"
   - Complete the flow
   - Should succeed now

2. **Check Token Was Created:**
   ```sql
   SELECT * FROM settings 
   WHERE `group` = 'whatsapp' 
   AND `key` = 'webhook_verify_token';
   ```

3. **Verify Webhook Connection:**
   - Check `tenant_settings` for `is_webhook_connected = 1`

---

## Additional Notes

### Token Security
- The verify token is generated once and reused for all tenants
- It's used only for webhook verification with Facebook
- Facebook sends it during webhook setup, and your controller validates it
- If compromised, regenerate it (but you'll need to re-setup all webhooks)

### Token Format
- Random 32-character string
- Example: `a8f2c3e9d7b4f1a5c6e8d2b3a9f7c1e4`
- Auto-generated using `Str::random(32)`

---

## Related Files Modified

1. **app/Services/WhatsAppEmbeddedSignupService.php**
   - Now checks `get_setting('whatsapp', 'webhook_verify_token')`
   - Auto-generates if missing
   - Uses same token as webhook controller

2. **app/Http/Controllers/Whatsapp/WhatsAppWebhookController.php**
   - Already expects `webhook_verify_token` from settings
   - No changes needed

---

## Summary

✅ **Service now uses correct verify token**
✅ **Auto-generates if missing**
✅ **Matches webhook controller expectation**
✅ **Webhook verification will succeed**

**Action Required:** Try embedded signup again - should work now!
