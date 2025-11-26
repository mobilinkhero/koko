# Admin Quick Setup Guide - WhatsApp Embedded Signup

## 🎯 Overview

Enable your tenants to connect their WhatsApp Business Accounts with just a few clicks using Facebook's Embedded Signup feature.

---

## ⏱️ Time Required

- **First-time setup**: 30-45 minutes
- **Subsequent deployments**: 5 minutes

---

## 📋 Prerequisites

Before starting, ensure you have:

1. ✅ A verified Facebook Business Account
2. ✅ Admin access to Meta Business Suite
3. ✅ A valid domain with HTTPS enabled
4. ✅ WhatsApp Business Platform access

---

## 🚀 Step-by-Step Setup

### Step 1: Become a Tech Provider

**Why?** Tech Provider status allows you to use Embedded Signup for your customers.

1. Go to [Meta Tech Provider Guide](https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers)

2. Complete verification requirements:
   - ✅ Verified Meta Business Account
   - ✅ Business verification completed
   - ✅ Tech Provider agreement accepted

3. Request Advanced Permissions:
   - Navigate to **App Dashboard** > **Use Cases** > **Request Advanced Access**
   - Request these permissions:
     - `whatsapp_business_management`
     - `whatsapp_business_messaging`
     - `public_profile`
     - `email`

4. Verify all 3 checkmarks are green:
   ![Tech Provider Status](https://developers.facebook.com/docs/whatsapp/assets/tech-provider.png)

---

### Step 2: Create Facebook App

1. Go to [Facebook Developers](https://developers.facebook.com/apps/)

2. Click **Create App**

3. Select **Business** as app type

4. Fill in app details:
   - **App Name**: Your Application Name (e.g., "ChatVo WhatsApp")
   - **App Contact Email**: Your email
   - **Business Account**: Select your business

5. Click **Create App**

6. **Copy App ID and App Secret**:
   - Go to **App Settings** > **Basic**
   - Copy **App ID** (e.g., `123456789012345`)
   - Click **Show** next to **App Secret**
   - Copy **App Secret** (e.g., `abc123def456...`)

7. Add **WhatsApp Product**:
   - Scroll to **Add Product**
   - Find **WhatsApp** and click **Set Up**

---

### Step 3: Create Facebook Login Configuration

1. Go to [Facebook Login for Business](https://developers.facebook.com/docs/whatsapp/embedded-signup/embed-the-flow#step-2--create-facebook-login-for-business-configuration)

2. In your app, navigate to:
   - **Products** > **Facebook Login** > **Settings**

3. Enable **Embedded Browser OAuth Login**

4. Create Configuration:
   - Click **Create Configuration**
   - **Configuration Name**: "WhatsApp Embedded Signup"
   - **Redirect URI**: Your tenant connection page URL
     ```
     https://yourdomain.com/connect
     https://yourdomain.com/tenant/connect
     ```
   - Click **Save**

5. **Copy Config ID**:
   - After saving, you'll see a **Config ID** (e.g., `987654321098765`)
   - Copy this ID

---

### Step 4: Configure in Your Application

1. **Navigate to Admin Panel**:
   - Login as superadmin
   - Go to **Settings** > **WhatsApp Settings**

2. **Enter Configuration**:
   ```
   Facebook App ID: [Paste from Step 2]
   Facebook App Secret: [Paste from Step 2]
   Facebook Config ID: [Paste from Step 3]
   ```

3. **Optional - Configure Admin Webhook**:
   - If you want automatic setup for all tenants:
     - Enter your Facebook App ID and Secret
     - Click "Connect Webhook"
     - Mark "Is Webhook Connected" as Yes

4. **Save Settings**

---

### Step 5: Configure App Webhook (Facebook Side)

1. Go to **Facebook App Dashboard** > **WhatsApp** > **Configuration**

2. Set **Webhook URL**:
   ```
   https://yourdomain.com/whatsapp/webhook
   ```

3. Set **Verify Token**:
   ```
   [Generate a random secure token]
   ```

4. Subscribe to Fields:
   - ✅ `messages`
   - ✅ `message_template_status_update`
   - ✅ `message_template_quality_update`
   - ✅ `account_update`

5. Click **Verify and Save**

---

### Step 6: Publish Your App (When Ready)

For Development:
- App works with test users and admins
- Add test users in **Roles** > **Test Users**

For Production:
1. Complete **App Review**:
   - Submit for `whatsapp_business_management`
   - Submit for `whatsapp_business_messaging`

2. Make App **Public**:
   - Go to **App Settings** > **Basic**
   - Toggle **App Mode** to **Live**

---

## ✅ Verification Checklist

After setup, verify:

- [ ] App ID, App Secret, Config ID saved in system
- [ ] Facebook App has WhatsApp product
- [ ] Advanced permissions approved
- [ ] Webhook configured (optional but recommended)
- [ ] Test embedded signup with a test account
- [ ] Button appears for tenants on connect page
- [ ] Clicking button opens Facebook popup
- [ ] Test account can connect successfully

---

## 🧪 Testing

### Test with Development Account

1. Add yourself as a **Test User**:
   - Go to **Roles** > **Test Users**
   - Add test user

2. **Test Embedded Signup**:
   - Login as a tenant in your application
   - Navigate to **Connect WhatsApp**
   - Click **Connect with Facebook**
   - Select test WhatsApp Business Account
   - Verify data saves correctly

### Expected Behavior

✅ Facebook popup opens  
✅ User can select WhatsApp Business Account  
✅ Authorization completes  
✅ Access token generated  
✅ WABA ID captured  
✅ Data saved to database  
✅ Templates synced  
✅ User redirected to dashboard

---

## 🔧 Configuration Options

### Option 1: Admin Webhook Connected (Recommended)

**Setup**: Configure webhook at admin level  
**Tenant Experience**: One-click setup (no Step 2 required)  
**Pros**: Fastest for tenants, centralized management  
**Cons**: Single webhook for all tenants

### Option 2: Admin Webhook NOT Connected

**Setup**: Leave admin webhook unconfigured  
**Tenant Experience**: Two-step setup (credentials + webhook)  
**Pros**: Each tenant manages their own webhook  
**Cons**: Extra step for tenants

---

## 📊 Settings Summary

Store these values in **WhatsApp Settings**:

| Setting | Description | Example | Required |
|---------|-------------|---------|----------|
| `wm_fb_app_id` | Facebook App ID | `123456789012345` | ✅ Yes |
| `wm_fb_app_secret` | Facebook App Secret | `abc123def...` | ✅ Yes |
| `wm_fb_config_id` | Facebook Login Config ID | `987654321098765` | ✅ Yes |
| `is_webhook_connected` | Admin webhook configured | `1` or `0` | ⚠️ Optional |

---

## 🐛 Troubleshooting

### Issue: "App not found" error
**Solution**: Verify App ID is correct and app is not deleted

### Issue: "Invalid client secret"
**Solution**: Regenerate App Secret and update in settings

### Issue: "Redirect URI mismatch"
**Solution**: Add exact tenant URL to Facebook Login redirect URIs

### Issue: Embedded signup button not appearing
**Solution**: 
1. Check all 3 settings are configured
2. Clear cache
3. Verify tenant has permission

### Issue: "Advanced permissions not approved"
**Solution**: Submit app for review in Facebook App Dashboard

### Issue: Webhook verification failed
**Solution**: 
1. Ensure URL is publicly accessible
2. Check verify token matches
3. Enable HTTPS

---

## 🔒 Security Best Practices

1. **App Secret Protection**
   - Never commit to version control
   - Use environment variables
   - Rotate periodically

2. **Webhook Verification**
   - Always verify `hub.verify_token`
   - Validate webhook signatures
   - Use HTTPS only

3. **Access Token Handling**
   - Store encrypted in database
   - Never expose in frontend
   - Set appropriate expiration

4. **Tenant Isolation**
   - Each tenant uses separate WABA
   - Validate tenant ownership
   - Prevent cross-tenant access

---

## 📚 Additional Resources

### Official Documentation
- [Embedded Signup Docs](https://developers.facebook.com/docs/whatsapp/embedded-signup)
- [Tech Provider Guide](https://developers.facebook.com/docs/whatsapp/solution-providers/get-started-for-tech-providers)
- [WhatsApp Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api)

### Video Tutorials
- [How to become Tech Provider](https://www.youtube.com/results?search_query=whatsapp+tech+provider+setup)
- [Embedded Signup Setup](https://www.youtube.com/results?search_query=whatsapp+embedded+signup)

### Support Channels
- [Meta Developer Community](https://developers.facebook.com/community/)
- [WhatsApp Business Platform Support](https://developers.facebook.com/support/)

---

## ✨ Benefits for Your Tenants

With Embedded Signup enabled:

✅ **One-Click Setup** - No manual credential copying  
✅ **Faster Onboarding** - 2 minutes vs 15 minutes  
✅ **Fewer Errors** - No typos in credentials  
✅ **Better UX** - Modern, seamless experience  
✅ **Automatic Webhook** - If admin configured  
✅ **Immediate Templates** - Auto-synced on connection

---

## 🎉 You're Done!

Your system is now ready for embedded signup!

**Next Steps**:
1. Test with a few tenants
2. Monitor logs for any issues
3. Provide tenant documentation
4. Gather feedback and improve

**Need Help?**  
Check the troubleshooting section or review the implementation documentation.
