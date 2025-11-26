# 🔍 How to Check Sync Errors & Logs

## 📊 Your Issue
**Message:** "Synced 0 products successfully using Service Account. 21 errors."

This means all 21 rows in your sheet failed to sync. Let's find out why!

---

## 🚀 Quick Fix Steps

### **Step 1: Check the Logs**

#### **Option A: Using File Manager**
```
Navigate to: d:\Chatvo\dis\storage\logs\

Open these files:
1. ecomorcelog.log  ← Main e-commerce log
2. laravel.log       ← Laravel application log
```

#### **Option B: Using Command Line**
```bash
# View e-commerce log (last 100 lines)
cd d:\Chatvo\dis
type storage\logs\ecomorcelog.log | more

# Search for errors
findstr /i "error" storage\logs\ecomorcelog.log

# View in real-time (use PowerShell)
Get-Content storage\logs\ecomorcelog.log -Wait -Tail 50
```

---

## 🔎 What to Look For in Logs

### **1. Column Mapping Errors**
```
❌ mapRowToProduct failed
Error: No column mapping found

Solution: Your sheet structure changed. Need to reset detection.
```

### **2. Missing Required Fields**
```
❌ Product sync error
Error: Missing required fields: name or sku

Solution: Check if your sheet has "Name" and "SKU" columns.
```

### **3. Invalid Data Types**
```
❌ Product sync error
Error: SQLSTATE[22007]: Invalid datetime format

Solution: Check date fields format in your sheet.
```

### **4. Empty Values**
```
⚠️ Header/Row count mismatch
headers_count: 10
row_count: 8

Solution: Some rows have missing cells.
```

---

## 🛠️ Common Issues & Solutions

### **Issue 1: No Column Mapping**
**Log Shows:**
```
No column mapping found. Please run sync to detect columns first.
```

**Solution:**
```
1. Delete tenant_sheet_configurations entry
2. Run sync again to auto-detect

OR manually via console:
DELETE FROM tenant_sheet_configurations WHERE tenant_id = 1;
```

### **Issue 2: Sheet Structure Changed**
**Log Shows:**
```
Column not found in mapping
```

**Solution:**
```
Dashboard → Clear Products → Sync Again
(This will re-detect your column structure)
```

### **Issue 3: Missing Name or SKU**
**Log Shows:**
```
Missing required fields: name or sku
```

**Solution:**
```
Check your Google Sheet:
- Must have "Name" column
- Must have "SKU" or "Product Code" column
- Values cannot be empty
```

### **Issue 4: Service Account Issues**
**Log Shows:**
```
Failed to get service account access token
```

**Solution:**
```
The system will fallback to CSV export method.
Make sure sheet is shared: "Anyone with link can view"
```

---

## 📋 Detailed Error Log Format

After sync, you'll see logs like this:

```
[2025-11-21 07:30:00] 🔍 DYNAMIC-MAPPER: Detecting columns
{
  "headers": ["Name", "Price", "Stock"],
  "total_columns": 3
}

[2025-11-21 07:30:01] 🔄 Processing row
{
  "row_index": 0,
  "row_data": ["Product 1", "29.99", "100"]
}

[2025-11-21 07:30:01] ✅ Mapped product data
{
  "name": "Product 1",
  "price": 29.99,
  "stock_quantity": 100
}

[2025-11-21 07:30:01] 💾 Product saved
{
  "product_id": 1,
  "sku": "AUTO-A1B2C3D4",
  "name": "Product 1"
}
```

Or if error:

```
[2025-11-21 07:30:01] ❌ Product sync error
{
  "row_index": 0,
  "error": "Missing required fields: name",
  "row_data": ["", "29.99", "100"]
}
```

---

## 🎯 Step-by-Step Debugging

### **1. View Your Current Mapping**
```sql
SELECT 
    detected_columns,
    column_mapping,
    detection_status
FROM tenant_sheet_configurations 
WHERE tenant_id = 1 
  AND sheet_type = 'products';
```

**Should show something like:**
```json
{
  "detected_columns": ["Name", "Price", "Stock"],
  "column_mapping": {
    "Name": "name",
    "Price": "price",
    "Stock": "stock_quantity"
  }
}
```

### **2. Check Your Sheet Structure**
Required columns (must have at least these):
- ✅ Name / Product Name / Title
- ✅ SKU / Code / Product Code
- ✅ Price (optional but recommended)

### **3. Test with Sample Data**
Create a simple test sheet:
```
| Name      | SKU      | Price | Stock |
|-----------|----------|-------|-------|
| Product 1 | TEST-001 | 10.00 | 50    |
| Product 2 | TEST-002 | 20.00 | 30    |
```

Sync this first to see if it works.

---

## 💻 Enable Debug Mode

### **In .env file:**
```bash
APP_DEBUG=true
LOG_LEVEL=debug
```

This will give you MORE detailed error messages.

---

## 🔥 Reset Everything & Start Fresh

If nothing works:

```bash
# 1. Clear all products
DELETE FROM products WHERE tenant_id = 1;

# 2. Clear mapping configuration
DELETE FROM tenant_sheet_configurations WHERE tenant_id = 1;

# 3. Clear e-commerce config (optional - this resets ALL settings)
DELETE FROM ecommerce_configurations WHERE tenant_id = 1;

# 4. Go to setup page and start over
```

---

## 📞 Get Detailed Error Report

Run this SQL to see recent errors:

```sql
-- Check recent products (if any synced)
SELECT id, name, sku, sync_status, last_synced_at 
FROM products 
WHERE tenant_id = 1 
ORDER BY last_synced_at DESC 
LIMIT 10;

-- Check configuration
SELECT * FROM ecommerce_configurations 
WHERE tenant_id = 1;

-- Check column mapping
SELECT * FROM tenant_sheet_configurations 
WHERE tenant_id = 1;
```

---

## 🎯 Most Likely Issues (Based on 21 Errors)

Since ALL 21 rows failed:

1. **Column mapping not created/found** (most likely)
   - Solution: Reset and sync again

2. **Sheet columns don't match expected format**
   - Solution: Check column names

3. **All rows missing required data**
   - Solution: Check if Name/SKU columns have data

4. **Sheet not accessible**
   - Solution: Check sharing settings

---

## ✅ What I Added

### **1. Detailed Row-by-Row Logging**
Every row now logs:
- ✅ What it's processing
- ✅ How it mapped the data
- ✅ Whether it saved successfully
- ❌ Exact error if it failed

### **2. Console Summary**
After sync completes, you'll see:
```
╔══════════════════════════════════════════
║ SYNC COMPLETED
║ ✅ Synced: 0 products
║ ❌ Errors: 21
║ Check logs for details
╚══════════════════════════════════════════
```

### **3. Better Error Messages**
Now shows EXACTLY what went wrong:
- Missing column names
- Empty values
- Data type mismatches
- Mapping issues

---

## 🚀 Next Steps

1. **Check logs:**
   ```bash
   type storage\logs\ecomorcelog.log | findstr "❌"
   ```

2. **Look for the pattern:**
   - Same error 21 times? = Configuration issue
   - Different errors? = Data quality issue

3. **Fix and retry:**
   - Reset mapping if needed
   - Fix sheet data
   - Sync again

4. **Share logs with me:**
   - Copy the error messages
   - I'll tell you exactly what's wrong!

---

## 📝 Quick Command Reference

```bash
# View errors only
type storage\logs\ecomorcelog.log | findstr "error"

# View last 50 lines
type storage\logs\ecomorcelog.log | more

# Count total errors
type storage\logs\ecomorcelog.log | findstr /c:"❌" | find /c /v ""

# View mapping issues
type storage\logs\ecomorcelog.log | findstr "mapping"

# Real-time log viewing (PowerShell)
Get-Content storage\logs\ecomorcelog.log -Wait -Tail 50
```

---

**Now check your logs and let me know what error you see! I'll help you fix it.** 🚀
