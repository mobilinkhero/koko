# 🗑️ How to Clear Old Products When Switching Google Sheets

## The Problem

When you disconnect your old Google Sheet and connect a new one, the **old products remain in the database**. This is by design - the system doesn't automatically delete data to prevent accidental data loss.

**Symptoms:**
- Dashboard shows old product count (e.g., 51 products)
- Sync status shows "Never synced" 
- Old products still visible in product catalog
- Can't see new sheet's products until you clear the old ones

---

## ✅ Solution: Clear Products Button

### **Quick Fix (Using UI)**

1. **Go to E-commerce Dashboard:**
   ```
   https://soft.chatvoo.com/public/abc/abc/ecommerce
   ```

2. **Click "Clear Products" button** (red button next to "Sync Now")
   - Only visible if you have products in the database
   - Requires confirmation before deleting

3. **Confirm the action:**
   - Will delete all products for your tenant
   - Will reset dynamic column mappings
   - Shows confirmation: "Successfully cleared {count} products"

4. **Sync with your new sheet:**
   - Click "Sync Now" button
   - System will auto-detect new sheet's structure
   - Products from new sheet will be synced

---

## 🔄 Complete Workflow: Switch to New Sheet

### **Step 1: Update Sheet URL (Settings)**
```
1. Go to Settings → E-commerce Settings
2. Update "Google Sheets URL" field
3. Save settings
```

### **Step 2: Clear Old Products (Dashboard)**
```
1. Go to E-commerce Dashboard
2. Click "Clear Products" button (red)
3. Confirm deletion
```

### **Step 3: Sync New Products**
```
1. Click "Sync Now" button (blue)
2. System auto-detects new sheet columns
3. Products synced successfully
```

### **Result:**
- ✅ Old products cleared
- ✅ New sheet structure detected
- ✅ New products synced
- ✅ Dashboard shows correct count

---

## 📊 What Gets Cleared?

When you click "Clear Products":

| Item | Action |
|------|--------|
| **Products** | ✅ Deleted (all products for your tenant) |
| **Column Mappings** | ✅ Reset (dynamic mapper configuration) |
| **Orders** | ❌ **NOT affected** (historical data preserved) |
| **Configuration** | ❌ **NOT affected** (settings remain) |
| **Google Sheet** | ❌ **NOT affected** (sheet data unchanged) |

---

## 💻 Alternative: Using Code

If you prefer to clear products programmatically:

```php
use App\Models\Tenant\Product;
use App\Models\Tenant\TenantSheetConfiguration;

// Clear all products
$tenantId = tenant_id();
$deletedCount = Product::where('tenant_id', $tenantId)->delete();

// Clear dynamic mapper config
TenantSheetConfiguration::where('tenant_id', $tenantId)
    ->where('sheet_type', 'products')
    ->delete();

echo "Cleared {$deletedCount} products";
```

Or via Artisan command (if you create one):
```bash
php artisan ecommerce:clear-products --tenant={id}
```

---

## 🛡️ Safety Features

### **Confirmation Required:**
The clear button shows a confirmation dialog:
```
"Are you sure you want to clear all 51 products? 
This action cannot be undone. 
You'll need to sync again to restore products from your sheet."
```

### **Tenant Isolation:**
- Only clears products for YOUR tenant
- Other tenants' data unaffected
- Uses `tenant_id` filter on all queries

### **Logging:**
All actions are logged:
```
storage/logs/ecomorcelog.log

[INFO] Product clear initiated from dashboard
[INFO] Products cleared successfully (deleted_count: 51)
```

---

## 🐛 Troubleshooting

### **Clear Button Not Visible?**
- Button only shows if you have products (`total_products > 0`)
- Check if you're on the dashboard page
- Refresh the page

### **Clear Failed?**
Check logs:
```bash
tail -f storage/logs/ecomorcelog.log | findstr "clear"
```

Common issues:
- Database connection error
- Permission issues
- Tenant ID mismatch

### **Products Still Showing?**
After clearing:
1. Refresh the dashboard
2. Check "Total Products" stat
3. If still showing, clear browser cache
4. Check database directly:
   ```sql
   SELECT COUNT(*) FROM products WHERE tenant_id = {YOUR_TENANT_ID};
   ```

---

## 📝 Best Practices

### **When to Clear Products:**

✅ **DO clear products when:**
- Switching to a completely new Google Sheet
- Sheet has different structure/columns
- Starting fresh with new product catalog
- Testing/development

❌ **DON'T clear products when:**
- Just adding columns to existing sheet (dynamic mapper will handle it)
- Temporarily disconnecting sheet
- Backing up data first

### **Recommended Workflow:**

1. **Before Clearing:**
   - Export current products (if needed)
   - Backup Google Sheet
   - Note any custom field mappings

2. **After Clearing:**
   - Sync immediately with new sheet
   - Verify product count
   - Check column mappings
   - Test with AI chat

---

## 🎯 Summary

**The Issue:**
Disconnecting Google Sheet doesn't auto-delete products

**The Solution:**
Use "Clear Products" button on dashboard

**The Process:**
```
1. Dashboard → Clear Products (red button)
2. Confirm deletion
3. Sync Now (blue button)
4. Done! ✅
```

---

## 🔗 Related Features

- **Dynamic Column Detection:** See `DYNAMIC_SHEETS_GUIDE.md`
- **Column Mapping Management:** See `QUICK_START_DYNAMIC_SHEETS.md`
- **System Architecture:** See `IMPLEMENTATION_SUMMARY.md`

---

## ✅ Implementation Details

**Code Added:**
- `EcommerceDashboard.php::clearAllProducts()` method
- Dashboard view: "Clear Products" button with confirmation
- Logging for all clear operations
- Tenant isolation checks

**Files Modified:**
1. `app/Livewire/Tenant/Ecommerce/EcommerceDashboard.php`
   - Added `clearAllProducts()` method
   - Clears products + dynamic config
   - Updates stats after clearing

2. `resources/views/livewire/tenant/ecommerce/dashboard.blade.php`
   - Added red "Clear Products" button
   - Shows only when products exist
   - Confirmation dialog included

**Safety Measures:**
- Requires explicit user confirmation
- Only deletes for current tenant
- Logs all actions
- Shows success/error messages

---

**Your issue is now fixed! Just click the "Clear Products" button and sync again.** 🚀
