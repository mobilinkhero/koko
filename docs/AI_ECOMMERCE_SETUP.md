# 🤖 AI-Powered E-commerce Bot Setup Guide

## Overview

Transform your e-commerce bot into a fully intelligent AI assistant that:
- ✅ **Uses your own OpenAI API key** - Full control over AI costs and models
- ✅ **Works directly with Google Sheets** - No database sync needed
- ✅ **Handles everything automatically** - AI understands customer intent
- ✅ **Real-time product data** - Always current with your sheet
- ✅ **Custom AI behavior** - Personalize how the assistant responds

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Enable AI Mode
1. Go to **E-commerce Settings** in your dashboard
2. Find **🤖 AI-Powered E-commerce Bot** section
3. Toggle **ON** the AI-powered mode
4. The configuration panel will expand

### Step 2: Add OpenAI API Key
1. Visit [OpenAI Platform](https://platform.openai.com/api-keys)
2. Create a new API key (starts with `sk-...`)
3. Paste it in the **OpenAI API Key** field
4. Choose your preferred AI model:
   - **GPT-3.5 Turbo** (Recommended) - Fast and cost-effective
   - **GPT-4** - More advanced reasoning
   - **GPT-4 Turbo** - Latest and most capable

### Step 3: Configure AI Settings
- **Temperature**: `0.7` (Balanced creativity)
- **Max Tokens**: `500` (Response length)
- **Custom Prompt**: Leave empty for default behavior

### Step 4: Enable Direct Integration
✅ Check **"Read products directly from Google Sheets"**
✅ Check **"Bypass local database completely"**

### Step 5: Save Settings
Click **💾 Save All Settings**

---

## 📋 Google Sheets Format

Your products sheet should have these columns:

| ID | Name | Description | Price | Sale Price | Stock Quantity | Category | Status | Featured |
|----|------|-------------|-------|------------|----------------|----------|---------|----------|
| 1 | Wireless Mouse | Gaming mouse with RGB | 29.99 | 24.99 | 50 | Electronics | active | yes |
| 2 | Bluetooth Speaker | Portable speaker | 49.99 | | 25 | Audio | active | no |

**Required Columns:**
- `ID` - Unique identifier
- `Name` - Product name  
- `Price` - Regular price
- `Stock Quantity` - Available stock
- `Status` - Must be "active" for AI to show

**Optional Columns:**
- `Description` - Product details
- `Sale Price` - Discounted price
- `Category` - Product category
- `Featured` - "yes" for featured products

---

## 🔧 How It Works

### Traditional Bot (OLD)
```
User Message → Intent Detection → Database Query → Response Template
```

### AI-Powered Bot (NEW)
```
User Message → Google Sheets Data → AI Processing → Intelligent Response
```

### Example Conversation

**User:** "I need a good wireless mouse for gaming"

**AI Bot:** "🎮 Perfect! I found our **Gaming Wireless Mouse** with RGB lighting - originally $29.99, now on sale for **$24.99**! 

It's specifically designed for gaming with:
- High precision sensor
- RGB customizable lighting  
- 50 hours battery life
- 50 units in stock

Would you like to order it?"

[Buy Now] [More Details] [See Other Mice]

---

## ⚙️ Advanced Configuration

### Custom System Prompt
Customize how your AI assistant behaves:

```
You are a helpful sales assistant for {store_name}. 
You specialize in electronics and always mention:
- Technical specifications
- Warranty information  
- Free shipping on orders over $50

Be enthusiastic but professional. Always ask if they need accessories.
```

**Available Variables:**
- `{store_name}` - Your store name
- `{currency}` - Currency (USD, EUR, etc.)
- `{customer_name}` - Customer's name
- `{products_data}` - Live product data
- `{payment_methods}` - Available payment options

### AI Model Comparison

| Model | Speed | Cost | Best For |
|-------|-------|------|----------|
| GPT-3.5 Turbo | ⚡⚡⚡ | 💰 | Standard conversations |
| GPT-4 | ⚡⚡ | 💰💰💰 | Complex product queries |
| GPT-4 Turbo | ⚡⚡ | 💰💰💰💰 | Advanced reasoning |

### Temperature Settings
- **0.0-0.3**: Very focused, consistent responses
- **0.4-0.7**: Balanced (recommended)
- **0.8-1.0**: More creative and varied
- **1.1-2.0**: Very creative (may be inconsistent)

---

## 🧪 Testing Your AI Bot

### Command Line Test
```bash
php artisan ecommerce:test-ai "I want to buy a wireless mouse" --tenant=1
```

### Test Messages
Try these sample messages:

**Product Search:**
- "Show me your wireless mice"
- "I need a bluetooth speaker under $50"
- "What gaming accessories do you have?"

**Purchase Intent:**
- "I want to buy the wireless mouse"
- "Add the bluetooth speaker to my cart"
- "I'll take 2 of the gaming keyboards"

**Complex Queries:**
- "I'm looking for a gift for a gamer, budget around $100"
- "Do you have any electronics on sale?"
- "What's the best selling product in audio category?"

---

## 🔍 Troubleshooting

### ❌ "AI is not configured"
- Check OpenAI API key is entered correctly
- Verify Google Sheets URL is set
- Ensure AI mode toggle is ON

### ❌ "OpenAI API request failed"
- Check API key has sufficient credits
- Verify API key permissions
- Try a different model (GPT-3.5 if using GPT-4)

### ❌ "No products found"
- Check Google Sheets is publicly accessible OR
- Configure Service Account for private sheets
- Verify sheet has "Products" tab with correct columns
- Ensure products have Status = "active"

### ❌ "Responses are too slow"
- Lower Max Tokens (try 300 instead of 500)
- Switch to GPT-3.5 Turbo
- Check internet connection speed

### ❌ "AI responses are inconsistent"
- Lower Temperature (try 0.3 instead of 0.7)  
- Add more specific instructions in Custom Prompt
- Increase Max Tokens for more detailed responses

---

## 💰 Cost Estimation

**GPT-3.5 Turbo Pricing (Example):**
- Input: $0.0015 per 1K tokens
- Output: $0.002 per 1K tokens

**Typical Cost Per Conversation:**
- Simple query: ~$0.003
- Complex interaction: ~$0.01
- 1000 customer messages: ~$3-10

**Tips to Reduce Costs:**
- Use GPT-3.5 instead of GPT-4 
- Lower Max Tokens setting
- Optimize Custom Prompt to be concise

---

## 🔐 Security & Best Practices

### API Key Security
- ✅ Store API key securely (encrypted in database)
- ✅ Use environment variables for sensitive data
- ✅ Monitor API usage regularly
- ❌ Never share API keys publicly
- ❌ Don't hardcode keys in code

### Data Privacy
- Customer messages are sent to OpenAI for processing
- Product data from Google Sheets is included in AI context
- Consider data privacy laws in your region
- Add privacy notice for AI-powered features

### Performance Optimization
- Cache Google Sheets data when possible
- Set reasonable token limits
- Monitor response times
- Have fallback for AI failures

---

## 🔄 Migration from Traditional Bot

### Gradual Migration
1. **Test Phase**: Enable AI mode for specific tenants
2. **Parallel Running**: Keep both systems active
3. **Full Migration**: Disable traditional bot once AI is proven

### Backup Plan
- Traditional bot remains as fallback
- Can disable AI mode anytime
- All settings are preserved

---

## 📊 Monitoring & Analytics

### Built-in Logging
```php
// Check AI processing logs
tail -f storage/logs/laravel.log | grep "AI processed"

// Monitor API costs
tail -f storage/logs/laravel.log | grep "OpenAI"
```

### Key Metrics to Track
- AI response success rate
- Average response time
- OpenAI API costs
- Customer satisfaction
- Conversion rates

---

## 🆘 Support

### Getting Help
1. Check logs: `storage/logs/laravel.log`
2. Test with command: `php artisan ecommerce:test-ai`
3. Verify Google Sheets access
4. Check OpenAI API status

### Common Issues
- **High API costs**: Lower temperature, reduce max tokens
- **Slow responses**: Use GPT-3.5, optimize prompts
- **Inconsistent behavior**: More specific system prompt
- **Missing products**: Check sheet format and status column

---

## 🎯 Success Tips

### Optimize Your Product Data
- Write clear, descriptive product names
- Include detailed descriptions
- Use consistent categories
- Keep stock quantities updated
- Mark popular items as "featured"

### Craft Better Prompts
- Be specific about your store's personality
- Include your unique selling points
- Mention policies (shipping, returns, etc.)
- Add conversation examples

### Monitor and Improve
- Track which queries work best
- Adjust temperature based on results
- Update system prompt regularly
- Gather customer feedback

---

**🎉 Your AI-powered e-commerce bot is now ready to handle customer conversations intelligently and automatically!**
