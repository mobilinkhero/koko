# WhatsApp Embedded Signup - Quick Start Guide

## 🚀 Get Up and Running in 5 Minutes

This guide gets you from zero to working embedded signup as quickly as possible.

---

## Step 1: Admin Configuration (One Time)

### Save These Settings to Database

```sql
-- Replace with your actual values
INSERT INTO settings (`group`, `key`, `value`) VALUES
('whatsapp', 'wm_fb_app_id', 'YOUR_FACEBOOK_APP_ID'),
('whatsapp', 'wm_fb_app_secret', 'YOUR_FACEBOOK_APP_SECRET'),
('whatsapp', 'wm_fb_config_id', 'YOUR_CONFIGURATION_ID');
```

### Where to Get These Values

1. **wm_fb_app_id** & **wm_fb_app_secret**
   - Go to: https://developers.facebook.com/apps
   - Select your app → Settings → Basic
   - Copy "App ID" and "App Secret"

2. **wm_fb_config_id**
   - Go to: https://developers.facebook.com/apps/YOUR_APP_ID/fb-login/settings/
   - Create a new Facebook Login for Business configuration
   - Copy the Configuration ID

---

## Step 2: Facebook App Setup

### Requirements

✅ **Meta Business Verification**
- Your Meta business account must be verified
- Guide: https://www.facebook.com/business/help/2058515294227817

✅ **Tech Provider Status**
- Follow: https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers
- All 3 items must show green checkmarks

✅ **Required Permissions**
- `whatsapp_business_management` (Advanced)
- `whatsapp_business_messaging` (Advanced)
- `public_profile` (Standard)
- `email` (Standard)

### Quick Setup Checklist

```
□ Create Facebook App (Business type)
□ Add WhatsApp product to app
□ Get app verified by Meta
□ Become Tech Provider
□ Request and get permissions approved
□ Create Facebook Login for Business config
□ Save App ID, Secret, Config ID to settings
```

---

## Step 3: Test the Connection

### As a Tenant

1. **Navigate to Connection Page**
   ```
   https://yourdomain.com/tenant/connect
   ```

2. **You Should See:**
   - "Connect with Facebook" button (blue, with Facebook icon)
   - "OR" divider
   - Manual connection form below

3. **Click "Connect with Facebook"**
   - Facebook popup opens
   - Complete WhatsApp setup
   - Popup closes automatically
   - You're redirected to dashboard

4. **Verify Connection**
   ```sql
   SELECT * FROM tenant_settings 
   WHERE tenant_id = YOUR_TENANT_ID 
   AND `group` = 'whatsapp';
   ```

   You should see:
   - `wm_business_account_id`: Your WABA ID
   - `wm_access_token`: Access token
   - `is_webhook_connected`: 1
   - `is_whatsmark_connected`: 1
   - `embedded_signup_completed_at`: Timestamp

---

## Step 4: Verify Templates Synced

```sql
SELECT COUNT(*) FROM whatsapp_templates 
WHERE tenant_id = YOUR_TENANT_ID;
```

Should return the number of templates from your WhatsApp account.

---

## Troubleshooting

### "Embedded signup not configured"
**Solution:** Check admin settings are saved correctly
```sql
SELECT * FROM settings WHERE `group` = 'whatsapp';
```

### "Connection failed: Failed to obtain access token"
**Solutions:**
1. Verify App Secret is correct (no extra spaces)
2. Check app is in Live mode (not Development)
3. Ensure configuration ID is correct

### "This WhatsApp Business Account is already in use"
**Solution:** This WABA is already connected to another tenant. Each WABA can only be used once.

### Facebook popup doesn't open
**Solutions:**
1. Check browser isn't blocking popups
2. Open browser console (F12) and look for errors
3. Verify Facebook SDK loaded: Type `FB` in console

### Button not showing
**Solutions:**
1. Check admin settings exist
2. Verify `embedded_signup_configured` variable in view
3. Clear cache: `php artisan cache:clear`

---

## Manual Connection Fallback

If embedded signup doesn't work, users can always use manual connection:

1. Enter WhatsApp Business Account ID
2. Enter Access Token
3. (If needed) Enter Facebook App credentials
4. Connect webhook manually

Both methods result in identical database structure.

---

## Comparison: Embedded vs Manual

| Feature | Embedded Signup | Manual Connection |
|---------|----------------|-------------------|
| Setup Time | ~2 minutes | ~10-15 minutes |
| User Steps | 1 click | 10+ steps |
| Error Prone | Low | Medium |
| Webhook Setup | Automatic | Manual (sometimes) |
| Prerequisites | Admin config | User knowledge |
| Data Structure | Identical | Identical |
| Template Sync | Automatic | Automatic |

---

## Security Notes

✅ **Authorization Code:** Single-use, exchanged server-side
✅ **Access Token:** Never exposed to frontend
✅ **App Secret:** Stored server-side only
✅ **Webhook Token:** Unique per tenant
✅ **Duplicate Prevention:** Checked before saving

---

## Next Steps

1. ✅ Configure admin settings
2. ✅ Test with your account
3. ✅ Verify data saved correctly
4. ✅ Test sending a message
5. ✅ Enable for production

---

## Support

- **Full Documentation:** [WHATSAPP_EMBEDDED_SIGNUP_SETUP.md](./WHATSAPP_EMBEDDED_SIGNUP_SETUP.md)
- **Manual Connection:** [WHATSAPP_MANUAL_CONNECTION_SETUP.md](./WHATSAPP_MANUAL_CONNECTION_SETUP.md)
- **Facebook Docs:** https://developers.facebook.com/docs/whatsapp/embedded-signup

---

**Ready to Go?**
1. Configure admin settings ✓
2. Click "Connect with Facebook" ✓
3. You're connected! 🎉
