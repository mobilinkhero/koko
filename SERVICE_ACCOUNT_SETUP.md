# 🛠️ Global Service Account Setup Guide

## One-Time Setup for All Tenants - Complete Step-by-Step

### **Step 1: Google Cloud Console Setup**

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click the project dropdown at the top
   - Click "New Project"
   - Name: `E-commerce Sheets Automation`
   - Click "Create"

### **Step 2: Enable Required APIs**

1. **Navigate to APIs & Services → Library**
2. **Enable Google Sheets API:**
   - Search "Google Sheets API"
   - Click on it → Click "Enable"
3. **Enable Google Drive API:**
   - Search "Google Drive API" 
   - Click on it → Click "Enable"

### **Step 3: Create Service Account**

1. **Go to IAM & Admin → Service Accounts**
2. **Click "+ Create Service Account"**
3. **Fill in details:**
   - **Service account name:** `ecommerce-sheets-service`
   - **Service account ID:** `ecommerce-sheets-service` (auto-filled)
   - **Description:** `Service account for e-commerce Google Sheets automation`
4. **Click "Create and Continue"**
5. **Skip role assignment** (click "Continue")
6. **Skip user access** (click "Done")

### **Step 4: Generate JSON Key**

1. **Click on your new service account email**
2. **Go to "Keys" tab**
3. **Click "Add Key" → "Create new key"**
4. **Select "JSON" format**
5. **Click "Create"**
6. **JSON file downloads automatically!** 📁

### **Step 5: Place JSON File in Project Root**

1. **Copy your downloaded JSON file**
2. **Rename it to:** `google-service-account.json`
3. **Place it in your project root directory:** `c:/wamp64/www/google-service-account.json`
4. **Make sure the file is readable by the web server**

### **Step 6: Configure Sheet Sharing (Per Tenant)**

**Each tenant needs to share their Google Sheet with the service account:**

1. **Tenant opens their Google Sheet**
2. **Click "Share" button**
3. **Add the service account email** (from the JSON file - looks like: `ecommerce-sheets-service@project-name.iam.gserviceaccount.com`)
4. **Set permission to "Editor"**
5. **Click "Send"**

**💡 Tip:** You can provide this email to tenants, or they can find it in their e-commerce settings page.

---

## 🎉 **Test It Out!**

1. **Click "⚡ One-Click Create Sheets"**
2. **Watch the magic happen!** ✨

The system will:
- ✅ **Create 3 sheets** (Products, Orders, Customers)
- ✅ **Add proper headers** with blue background
- ✅ **Add sample data** for testing
- ✅ **Format everything** beautifully
- ✅ **Delete default Sheet1** (if empty)

---

## 🔍 **Troubleshooting**

### **"Permission denied" error:**
- Make sure you shared the Google Sheet with the service account email
- Check that the service account has "Editor" permissions

### **"Invalid JSON file" error:**
- Make sure you downloaded the JSON key (not other credentials)
- File should contain `client_email` and `private_key` fields

### **"Service account not found" error:**
- Make sure both Google Sheets API and Google Drive API are enabled
- Wait a few minutes after creating the service account

---

## 🚀 **What You Get**

### **Fully Automatic:**
- **No manual work** - everything happens automatically
- **Perfect formatting** - blue headers, proper spacing
- **Sample data included** - ready for testing
- **Error handling** - detailed logging for debugging

### **Smart Fallback:**
- If Service Account fails → Shows import modal
- Copy-paste ready data with buttons
- CSV downloads for each sheet
- Step-by-step instructions

---

**Your e-commerce system now has enterprise-level Google Sheets integration!** 🎯

Need help? Check the logs at `~/ecomorcelog.log` for detailed information about what's happening.
