# E-commerce WhatsApp Bot Setup Guide

## Quick Start Testing

### 1. Test Product Sync
```bash
# Check products in database
php artisan tinker
\App\Models\Tenant\Product::count();
\App\Models\Tenant\Product::take(5)->get(['name', 'price', 'sku', 'stock_quantity']);
exit
```

### 2. Test Bot Triggers

Send these messages to your WhatsApp Business number:

#### **Browse Products:**
- `shop`
- `products`
- `catalog`
- `show products`

#### **Search Products:**
- `search [product name]`
- `find [product name]`

#### **View Cart:**
- `cart`
- `my cart`
- `view cart`

#### **Place Order:**
- `order`
- `checkout`
- `buy`

### 3. Expected Bot Responses

#### **Product Catalog Response:**
```
🛍️ *Our Products*

1️⃣ Product Name 1
   💰 Price: $XX.XX
   📦 Stock: XX units
   🔗 SKU: XXX

2️⃣ Product Name 2
   💰 Price: $XX.XX
   📦 Stock: XX units
   🔗 SKU: XXX

Reply with product number to view details
```

#### **Product Details Response:**
```
📦 *Product Name*

📝 Description: [Product description]
💰 Price: $XX.XX
📦 Stock: XX units available
🏷️ Category: [Category]

Reply:
• *ADD* - Add to cart
• *BACK* - Return to catalog
```

### 4. Create Bot Flow (If Not Exists)

Go to your admin panel:
1. Navigate to **Bot Flows**
2. Create new flow: **"E-commerce Shop"**
3. Set triggers: `shop`, `products`, `catalog`, `buy`
4. Add actions:
   - Send product catalog
   - Handle product selection
   - Manage cart
   - Process orders

### 5. Testing Checklist

- [ ] Products synced from Google Sheets
- [ ] Bot responds to "shop" keyword
- [ ] Product list displays correctly
- [ ] Can view product details
- [ ] Can add products to cart
- [ ] Can view cart
- [ ] Can place order
- [ ] Order saved to database
- [ ] Order synced to Google Sheets

### 6. Test Order Flow

**Complete Order Test:**
1. Send: `shop`
2. Bot shows products
3. Reply with product number (e.g., `1`)
4. Bot shows product details
5. Reply: `add`
6. Bot confirms added to cart
7. Send: `cart`
8. Bot shows cart contents
9. Send: `checkout`
10. Bot asks for shipping details
11. Provide details
12. Bot confirms order

### 7. Verify Order in System

```bash
# Check orders in database
php artisan tinker
\App\Models\Tenant\Order::latest()->first();
exit
```

### 8. Check Google Sheets

1. Open your Google Sheets
2. Go to **Orders** sheet
3. Verify new order appears

### 9. Common Issues & Solutions

#### **Bot Not Responding:**
- Check WhatsApp webhook is connected
- Verify bot flow is active
- Check logs: `php artisan ecommerce:logs`

#### **Products Not Showing:**
- Verify sync: `php artisan tinker` → `\App\Models\Tenant\Product::count()`
- Re-sync: Go to Settings → Click "Sync Data with Sheets"

#### **Orders Not Saving:**
- Check database connection
- Verify EcommerceConfiguration exists
- Check logs for errors

### 10. Advanced Testing

#### **Test with Multiple Users:**
```bash
# Check concurrent orders
\App\Models\Tenant\Order::where('created_at', '>', now()->subHour())->count();
```

#### **Test Stock Management:**
- Place order for product
- Check if stock decreased
- Try ordering out-of-stock product

#### **Test Payment Methods:**
- Test each payment method configured
- Verify payment confirmation messages

### 11. Production Checklist

Before going live:
- [ ] All products have images
- [ ] Prices are correct
- [ ] Stock quantities are accurate
- [ ] Payment methods configured
- [ ] Shipping costs set up
- [ ] Order confirmation messages customized
- [ ] Customer support contact added
- [ ] Test orders completed successfully
- [ ] Google Sheets sync working
- [ ] Backup strategy in place

### 12. Monitoring

**Daily Checks:**
```bash
# Check today's orders
php artisan tinker
\App\Models\Tenant\Order::whereDate('created_at', today())->count();

# Check low stock products
\App\Models\Tenant\Product::where('stock_quantity', '<', 5)->get(['name', 'stock_quantity']);
```

### 13. Customer Support Responses

**Common Customer Questions:**

**Q: How do I order?**
A: Send "shop" to browse products, select items, and checkout!

**Q: What payment methods do you accept?**
A: [List your configured payment methods]

**Q: How long is delivery?**
A: [Your delivery timeframe]

**Q: Can I cancel my order?**
A: Yes, contact us immediately at [your contact]

### 14. Emergency Commands

```bash
# Clear all carts (if needed)
php artisan tinker
\App\Models\Tenant\Cart::truncate();

# Reset failed orders
\App\Models\Tenant\Order::where('status', 'failed')->delete();

# Re-sync all products
# Go to: Settings → Sync Data with Sheets
```

## Need Help?

Check logs:
```bash
php artisan ecommerce:logs
tail -f storage/logs/laravel.log
```

Contact support with:
- Error message
- What you were trying to do
- Screenshots if possible
