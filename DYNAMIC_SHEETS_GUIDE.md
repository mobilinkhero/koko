# 🔥 Universal Dynamic Google Sheets System

## Overview

The **Universal Dynamic Google Sheets System** allows each tenant to use their own custom Google Sheets structure without being limited to predefined columns. The system automatically detects, maps, and syncs ANY column structure!

---

## 🎯 Key Features

### ✅ **Auto-Detection**
- Automatically detects all columns in your Google Sheets
- No need to match exact column names
- Works with any language (English, Arabic, Urdu, etc.)

### ✅ **Intelligent Mapping**
- Auto-maps common columns (Name → name, Price → price, etc.)
- Supports multiple naming variations
- Creates custom fields for unrecognized columns

### ✅ **Custom Fields**
- Any extra columns automatically become custom fields
- Stored in product `meta_data` JSON field
- Accessible via AI for personalized recommendations

### ✅ **Tenant-Specific**
- Each tenant has their own column configuration
- Independent mappings per tenant
- No conflicts between different tenants

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────┐
│     GOOGLE SHEETS (Your Custom Structure)│
│  ┌────────┬────────┬──────────┬────────┐│
│  │ Nombre │ Precio │ Color    │ Talla  ││ ← Spanish columns
│  │ خط     │ سعر    │ صورة     │ كمية   ││ ← Arabic columns  
│  │ Name   │ Price  │ Size     │ Brand  ││ ← English columns
│  └────────┴────────┴──────────┴────────┘│
└─────────────────────────────────────────┘
                    ↓
        🔍 AUTO-DETECTION LAYER
                    ↓
┌─────────────────────────────────────────┐
│  tenant_sheet_configurations Table      │
│  ┌──────────────────────────────────┐  │
│  │ detected_columns: ["Nombre",     │  │
│  │                    "Precio",     │  │
│  │                    "Color"]      │  │
│  │                                  │  │
│  │ column_mapping: {                │  │
│  │   "Nombre": "name",              │  │
│  │   "Precio": "price",             │  │
│  │   "Color": "custom_color"        │  │
│  │ }                                │  │
│  └──────────────────────────────────┘  │
└─────────────────────────────────────────┘
                    ↓
         ⚙️ DYNAMIC MAPPER SERVICE
                    ↓
┌─────────────────────────────────────────┐
│         products Table                  │
│  ┌──────────────────────────────────┐  │
│  │ id, name, price, sku, ...        │  │
│  │ meta_data: {                     │  │
│  │   "custom_color": "Red",         │  │
│  │   "custom_size": "Large",        │  │
│  │   "custom_brand": "Nike"         │  │
│  │ }                                │  │
│  └──────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

---

## 📋 How It Works

### **Step 1: First Sync (Auto-Detection)**

When you sync for the first time, the system:

1. **Reads Sheet Headers**
   ```
   Your Sheet: ["Product Name", "Price USD", "Size", "Brand", "Custom Rating"]
   ```

2. **Auto-Detects Columns**
   ```php
   DynamicSheetMapperService::detectAndMapColumns($headers);
   ```

3. **Creates Intelligent Mappings**
   ```json
   {
     "Product Name": "name",         // Recognized as product name
     "Price USD": "price",            // Recognized as price
     "Size": "custom_size",           // Custom field (not standard)
     "Brand": "custom_brand",         // Custom field
     "Custom Rating": "custom_rating" // Custom field
   }
   ```

4. **Stores Configuration**
   - Saves to `tenant_sheet_configurations` table
   - One configuration per tenant
   - Reused for all future syncs

### **Step 2: Data Sync**

For each row in your sheet:

1. **Maps Row Data**
   ```php
   $productData = $mapper->mapRowToProduct($row, $headers);
   ```

2. **Extracts Core Fields**
   ```php
   [
     'name' => 'iPhone 15 Pro',
     'price' => 999.99,
     'sku' => 'IPHONE15-128',
     'stock_quantity' => 50,
   ]
   ```

3. **Collects Custom Fields**
   ```php
   'meta_data' => [
     'custom_size' => '128GB',
     'custom_brand' => 'Apple',
     'custom_rating' => '4.8',
   ]
   ```

4. **Upserts to Database**
   ```php
   Product::updateOrCreate(
       ['tenant_id' => $tenantId, 'sku' => $sku],
       $productData
   );
   ```

---

## 🔧 Column Mapping Logic

### **Recognized Core Fields**

The system automatically recognizes these variations:

| Database Field | Recognized Column Names |
|---------------|------------------------|
| `name` | Name, Product Name, Title, Product |
| `price` | Price, Product Price, Cost, Amount, Price USD |
| `sku` | SKU, Product Code, Code, Item Code |
| `description` | Description, Details, Product Description |
| `stock_quantity` | Stock, Stock Quantity, Qty, Quantity, Available |
| `category` | Category, Product Category, Type |
| `sale_price` | Sale Price, Discounted Price, Offer Price |
| `status` | Status, Product Status, Active |
| `featured` | Featured, Is Featured, Highlight |
| `images` | Images, Image URLs, Photos, Images (URLs) |
| `tags` | Tags, Keywords, Labels |

### **Custom Fields**

Any column NOT recognized as a core field becomes a **custom field**:

```
Sheet Column: "Warranty Period"
   ↓
Database: custom_warranty_period (in meta_data JSON)
```

---

## 💻 Usage Examples

### **1. Accessing Custom Fields in Code**

```php
// Get a product
$product = Product::find(1);

// Access custom fields
$color = $product->getCustomField('custom_color', 'N/A');
$size = $product->getCustomField('custom_size');
$brand = $product->getCustomField('custom_brand');

// Get all custom fields
$allCustomFields = $product->custom_fields;
// Returns: ['custom_color' => 'Red', 'custom_size' => 'L', ...]

// Check if has custom fields
if ($product->hasCustomFields()) {
    // Do something
}
```

### **2. Using in AI Recommendations**

Custom fields are automatically available to the AI:

```php
// In AiEcommerceService
$productData = [
    'name' => 'T-Shirt',
    'price' => 29.99,
    'meta_data' => [
        'custom_color' => 'Blue',
        'custom_size' => 'Medium',
        'custom_material' => 'Cotton',
    ]
];

// AI can use these for personalized recommendations:
"We have this T-Shirt in Blue, size Medium, made of 100% Cotton for $29.99"
```

### **3. Managing Mappings Programmatically**

```php
use App\Services\DynamicSheetMapperService;

// Initialize mapper
$mapper = new DynamicSheetMapperService($tenantId, 'products');

// Get current configuration
$config = $mapper->getConfigurationSummary();

// Manually update mapping
$mapper->updateMapping([
    'Product Name' => 'name',
    'Price USD' => 'price',
    'Color' => 'custom_color',
]);

// Reset detection (forces re-detection on next sync)
$mapper->resetConfiguration();
```

---

## 🎨 Google Sheets Examples

### **Example 1: Basic E-commerce (English)**

| ID | Name | Price | Stock | Category |
|----|------|-------|-------|----------|
| 1 | iPhone 15 | 999 | 50 | Electronics |
| 2 | Samsung TV | 799 | 30 | Electronics |

**Auto-Mapped To:**
```json
{
  "ID": "google_sheet_row_id",
  "Name": "name",
  "Price": "price",
  "Stock": "stock_quantity",
  "Category": "category"
}
```

### **Example 2: Custom Fields (Fashion)**

| Product Name | Price | Color | Size | Material | Brand |
|-------------|-------|-------|------|----------|-------|
| T-Shirt | 29.99 | Blue | M | Cotton | Nike |
| Jeans | 59.99 | Black | 32 | Denim | Levi's |

**Auto-Mapped To:**
```json
{
  "Product Name": "name",
  "Price": "price",
  "Color": "custom_color",
  "Size": "custom_size",
  "Material": "custom_material",
  "Brand": "custom_brand"
}
```

### **Example 3: Arabic Columns**

| اسم المنتج | السعر | الكمية | الفئة |
|-----------|-------|--------|-------|
| آيفون 15 | 999 | 50 | إلكترونيات |

**Auto-Mapped To:**
```json
{
  "اسم المنتج": "custom_اسم_المنتج",
  "السعر": "custom_السعر",
  "الكمية": "custom_الكمية",
  "الفئة": "custom_الفئة"
}
```
*(These would be custom fields unless you add Arabic column name recognition)*

---

## 🚀 Migration & Setup

### **Run Migration**

```bash
php artisan migrate
```

This creates the `tenant_sheet_configurations` table.

### **First Sync**

Simply sync your products as normal:

```php
$sheetsService = new GoogleSheetsService();
$result = $sheetsService->syncProductsFromSheets();
```

The system will:
1. Auto-detect your columns
2. Create the mapping configuration
3. Sync all products with custom fields

### **View Configuration**

Check the logs to see your detected structure:

```
storage/logs/ecomorcelog.log
```

Look for entries like:
```
🔍 DYNAMIC-MAPPER: Columns detected and mapped
{
  "total_detected": 8,
  "mappings": {"Product Name": "name", ...}
}
```

---

## 🎛️ Configuration Options

Each tenant configuration supports:

| Field | Description | Default |
|-------|-------------|---------|
| `auto_detect_columns` | Auto-detect columns on sync | `true` |
| `allow_custom_fields` | Allow custom fields creation | `true` |
| `strict_mode` | Reject unknown columns | `false` |

### **Strict Mode Example**

```php
// Enable strict mode (only allow known columns)
$config = TenantSheetConfiguration::where('tenant_id', $tenantId)->first();
$config->update(['strict_mode' => true]);

// Now sync will fail if unknown columns are found
```

---

## 📊 Database Schema

### **tenant_sheet_configurations**

```sql
CREATE TABLE tenant_sheet_configurations (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    sheet_type VARCHAR(255) DEFAULT 'products',
    
    -- Column mapping
    detected_columns JSON,           -- ["Name", "Price", "Color"]
    column_mapping JSON,             -- {"Name": "name", "Color": "custom_color"}
    custom_fields_config JSON,       -- Custom field metadata
    
    -- Settings
    auto_detect_columns BOOLEAN DEFAULT TRUE,
    allow_custom_fields BOOLEAN DEFAULT TRUE,
    strict_mode BOOLEAN DEFAULT FALSE,
    
    -- Tracking
    detection_status VARCHAR(255) DEFAULT 'pending',
    total_columns_detected INT DEFAULT 0,
    mapped_columns_count INT DEFAULT 0,
    last_detection_at TIMESTAMP,
    
    UNIQUE(tenant_id, sheet_type)
);
```

### **products.meta_data**

```json
{
  "custom_color": "Red",
  "custom_size": "Large",
  "custom_brand": "Nike",
  "custom_warranty": "2 years",
  "custom_origin": "USA"
}
```

---

## 🔍 Troubleshooting

### **Columns Not Detected?**

Check the sync logs:
```bash
tail -f storage/logs/ecomorcelog.log | grep "DYNAMIC-MAPPER"
```

### **Wrong Mapping?**

Manually update:
```php
$mapper = new DynamicSheetMapperService($tenantId);
$mapper->updateMapping([
    'Your Column' => 'desired_field'
]);
```

### **Reset Detection?**

```php
$mapper->resetConfiguration();
// Then sync again
```

### **Custom Field Not Showing?**

Check product meta_data:
```php
$product = Product::find(1);
dd($product->meta_data);
```

---

## ✨ Benefits

1. **🌍 Universal Support** - Works with ANY column structure
2. **🔄 Zero Configuration** - Auto-detects everything
3. **🎯 Tenant-Specific** - Each tenant has unique mapping
4. **📈 Scalable** - Add columns anytime without code changes
5. **🤖 AI-Ready** - Custom fields available to AI
6. **🌐 Multi-Language** - Supports any language columns
7. **💾 No Schema Changes** - Uses existing JSON fields

---

## 📝 Summary

The **Universal Dynamic Google Sheets System** eliminates the need for fixed column structures. Each tenant can:

- Use their own column names
- Add custom fields without code changes
- Support multiple languages
- Maintain tenant-specific configurations
- Access all data via AI

**It's truly universal! 🚀**
