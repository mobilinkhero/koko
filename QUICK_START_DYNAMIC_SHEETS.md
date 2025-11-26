# ⚡ Quick Start: Universal Dynamic Google Sheets

## 🎯 What Changed?

Your e-commerce system now supports **ANY Google Sheets structure**. Each tenant can use their own custom columns without code changes!

---

## 🚀 Setup (3 Steps)

### **Step 1: Run Migration**

```bash
cd d:\Chatvo\dis
php artisan migrate
```

This creates the `tenant_sheet_configurations` table for storing column mappings.

### **Step 2: Sync Your Products**

Your existing sync process now auto-detects columns:

```php
// Via UI: Dashboard → Sync Products button
// Or programmatically:
$sheetsService = new GoogleSheetsService();
$result = $sheetsService->syncProductsFromSheets();
```

### **Step 3: Check Logs**

View the auto-detected mappings:

```bash
tail -f storage\logs\ecomorcelog.log
```

Look for:
```
🔍 DYNAMIC-MAPPER: Columns detected and mapped
{
  "total_detected": 8,
  "mapped_columns": {
    "Product Name": "name",
    "Price": "price",
    "Color": "custom_color",
    "Size": "custom_size"
  },
  "custom_fields": ["custom_color", "custom_size"]
}
```

---

## 📊 Example Scenarios

### **Scenario 1: Standard E-commerce**

**Your Sheet:**
```
| Name | Price | Stock | Category | SKU |
```

**Result:**
- All columns mapped to core fields
- Works exactly as before
- ✅ No custom fields needed

### **Scenario 2: Fashion Store with Custom Fields**

**Your Sheet:**
```
| Product Name | Price | Color | Size | Material | Brand |
```

**Result:**
```json
Core Fields:
{
  "name": "T-Shirt",
  "price": 29.99
}

Custom Fields (meta_data):
{
  "custom_color": "Blue",
  "custom_size": "Medium",
  "custom_material": "Cotton",
  "custom_brand": "Nike"
}
```

### **Scenario 3: Electronics with Specs**

**Your Sheet:**
```
| Name | Price | Warranty | RAM | Storage | Processor |
```

**Result:**
- Name, Price → Core fields
- Warranty, RAM, Storage, Processor → Custom fields
- AI can use custom fields in recommendations!

---

## 🎨 Using Custom Fields

### **In Code:**

```php
$product = Product::find(1);

// Get custom field
$color = $product->getCustomField('custom_color');
$size = $product->getCustomField('custom_size', 'N/A'); // with default

// Get all custom fields
$customFields = $product->custom_fields;

// Check if has custom fields
if ($product->hasCustomFields()) {
    foreach ($product->custom_fields as $field => $value) {
        echo "$field: $value\n";
    }
}
```

### **In AI Prompts:**

Custom fields are automatically included in product data sent to AI:

```json
{
  "name": "iPhone 15 Pro",
  "price": 999.99,
  "meta_data": {
    "custom_storage": "256GB",
    "custom_color": "Titanium Blue",
    "custom_warranty": "2 years"
  }
}
```

The AI can now say:
> "We have the iPhone 15 Pro in Titanium Blue with 256GB storage and 2-year warranty for $999.99"

---

## 🔧 Managing Mappings

### **View Current Mapping:**

```php
use App\Services\DynamicSheetMapperService;

$mapper = new DynamicSheetMapperService(tenant_id(), 'products');
$config = $mapper->getConfigurationSummary();

print_r($config);
```

### **Reset Detection:**

```php
$mapper->resetConfiguration();
// Then sync again to re-detect
```

### **Manual Mapping:**

```php
$mapper->updateMapping([
    'Producto' => 'name',        // Spanish
    'Precio' => 'price',
    'Color' => 'custom_color',
]);
```

---

## 📋 Column Recognition

The system automatically recognizes these core fields:

| Your Column Name | Maps To | Examples |
|-----------------|---------|----------|
| Name, Product Name, Title | `name` | ✅ |
| Price, Cost, Amount | `price` | ✅ |
| SKU, Code, Item Code | `sku` | ✅ |
| Stock, Quantity, Qty | `stock_quantity` | ✅ |
| Category, Type | `category` | ✅ |
| Description, Details | `description` | ✅ |
| Sale Price, Discount Price | `sale_price` | ✅ |
| Status, Active | `status` | ✅ |
| Featured, Highlight | `featured` | ✅ |
| Images, Photos | `images` | ✅ |

**Anything else** → Becomes a custom field automatically!

---

## 🌍 Multi-Language Support

### **Spanish Sheet:**
```
| Nombre | Precio | Color | Talla |
```
Result: Nombre→name, Precio→price (if you add Spanish mappings)

### **Arabic Sheet:**
```
| اسم المنتج | السعر | الكمية |
```
Result: All become custom fields (unless you add Arabic recognition)

**To add language support:**
```php
// In TenantSheetConfiguration::getDefaultProductFieldMappings()
'name' => ['Name', 'Product Name', 'Nombre', 'اسم المنتج'],
'price' => ['Price', 'Precio', 'السعر'],
```

---

## 🐛 Troubleshooting

### **Q: My custom columns aren't syncing?**
**A:** Check logs for detection results. Custom fields should appear in `meta_data`.

### **Q: Column mapped to wrong field?**
**A:** Manually update mapping:
```php
$mapper->updateMapping(['Your Column' => 'correct_field']);
```

### **Q: Want to reject unknown columns?**
**A:** Enable strict mode:
```php
$config = TenantSheetConfiguration::where('tenant_id', tenant_id())->first();
$config->update(['strict_mode' => true]);
```

### **Q: How to re-detect columns?**
**A:** Reset and sync:
```php
$mapper->resetConfiguration();
$sheetsService->syncProductsFromSheets();
```

---

## ✅ Benefits Summary

| Before | After |
|--------|-------|
| Fixed columns only | ✅ ANY columns |
| Same structure for all tenants | ✅ Tenant-specific |
| Add columns = code changes | ✅ Zero code changes |
| English only | ✅ Any language |
| No custom fields | ✅ Unlimited custom fields |
| AI limited to core fields | ✅ AI uses ALL fields |

---

## 📚 Full Documentation

For detailed information, see:
- **Full Guide:** `DYNAMIC_SHEETS_GUIDE.md`
- **System Overview:** Previous session checkpoint

---

## 🎉 You're Ready!

Just run the migration and sync your products. The system will handle the rest automatically!

```bash
php artisan migrate
```

Then sync via dashboard or code. Check logs to see your auto-detected structure! 🚀
