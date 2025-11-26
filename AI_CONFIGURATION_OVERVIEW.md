# 🤖 AI Configuration Overview - WhatsMark System

## 📊 **Current AI Setup Status**

Your WhatsMark platform has a **comprehensive AI-powered e-commerce system** integrated with OpenAI's GPT models.

---

## 🎯 **AI Features Available**

### **1. Two-Tier AI Service Architecture**

#### **Standard AI Service** (`AiEcommerceService.php`)
- ✅ Natural language processing for customer messages
- ✅ Intent detection (browse, purchase, support, tracking)
- ✅ Conversation thread management with context
- ✅ Product recommendations from local database
- ✅ Automated order creation via AI
- ✅ Multi-language support (English, Arabic, Urdu)
- ✅ Interactive button generation
- ✅ Payment method selection automation

#### **Advanced AI Service** (`AdvancedAiEcommerceService.php`)
- ✅ Multi-intent detection (handle multiple requests simultaneously)
- ✅ Sentiment analysis (positive, negative, neutral)
- ✅ Customer profiling and behavior analytics
- ✅ Personalized product recommendations
- ✅ Dynamic pricing based on customer tier
- ✅ Urgency level detection
- ✅ Price sensitivity analysis
- ✅ Support ticket creation
- ✅ Multi-modal content processing (text, images, voice)
- ✅ Advanced analytics and business intelligence

---

## ⚙️ **AI Configuration Fields**

### **Database Schema** (`ecommerce_configurations` table)

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `ai_powered_mode` | Boolean | `false` | Enable/disable AI features |
| `openai_api_key` | Text | `null` | Your OpenAI API key (required) |
| `openai_model` | String | `gpt-3.5-turbo` | AI model to use |
| `ai_temperature` | Decimal | `0.7` | Response creativity (0-1) |
| `ai_max_tokens` | Integer | `500` | Max response length |
| `ai_system_prompt` | Text | `null` | Custom AI instructions |
| `ai_product_context` | Text | `null` | Additional product context |
| `ai_response_templates` | JSON | `null` | Predefined response templates |
| `direct_sheets_integration` | Boolean | `false` | Bypass local DB, use Sheets directly |
| `bypass_local_database` | Boolean | `false` | Skip local product storage |

---

## 🔧 **Available AI Models**

From `config/aimodel.php`:

1. **gpt-3.5-turbo** (Default) - Fast, cost-effective
2. **gpt-3.5-turbo-16k** - Extended context window
3. **gpt-4** - Most capable, slower, expensive
4. **gpt-4-turbo** - Faster GPT-4 variant
5. **gpt-4-turbo-preview** - Latest preview version
6. **gpt-4-0125-preview** - Specific snapshot
7. **gpt-4o-mini** - Optimized mini version

---

## 💡 **How AI Works in Your System**

### **Customer Message Flow:**

```
1. Customer sends WhatsApp message
   ↓
2. System checks if AI is enabled & configured
   ↓
3. Fetches active products from database
   ↓
4. Gets or creates conversation thread (30-min session)
   ↓
5. Builds context-aware system prompt with:
   - Store name & currency
   - Customer info (name, phone)
   - All available products (JSON)
   - Payment methods
   - Customer detail requirements
   ↓
6. Sends to OpenAI API with conversation history
   ↓
7. Receives AI response (text/JSON with buttons)
   ↓
8. Parses response for:
   - Message text
   - Interactive buttons
   - Actions (create_order, update_stock, etc.)
   ↓
9. Executes actions (if any)
   ↓
10. Sends formatted response to customer
```

---

## 🎨 **AI Response Formats**

### **1. Product Showcase (JSON)**
```json
{
  "message": "Here are our products:\n\n1. *iPhone Case*\n💰 $25\n📋 Premium quality\n📦 In Stock",
  "buttons": [
    {"id": "select_1", "text": "🛒 Select This"},
    {"id": "info_1", "text": "ℹ️ More Info"}
  ],
  "type": "interactive"
}
```

### **2. Payment Selection**
```json
{
  "message": "Great choice! Please select your payment method:",
  "buttons": [
    {"id": "pay_cod", "text": "💵 Cash on Delivery"},
    {"id": "pay_bank", "text": "🏦 Bank Transfer"},
    {"id": "pay_card", "text": "💳 Credit/Debit Card"}
  ],
  "type": "interactive"
}
```

### **3. Order Creation**
```json
{
  "message": "Thank you! Your order has been confirmed...",
  "actions": [
    {
      "type": "create_order",
      "data": {
        "product_id": "123",
        "quantity": 2,
        "contact_id": "456",
        "payment_method": "cod",
        "customer_details": {
          "name": "John Doe",
          "phone": "+1234567890",
          "address": "123 Main St"
        }
      }
    }
  ]
}
```

---

## 🧠 **Conversation Thread Management**

### **Features:**
- ✅ **Session-based**: 30-minute active sessions
- ✅ **Context retention**: Remembers previous messages
- ✅ **Token tracking**: Monitors API usage
- ✅ **Auto-expiry**: Cleans up old conversations
- ✅ **Cost-efficient**: Reuses system prompt across messages

### **Database Table:** `ai_conversations`
```sql
- tenant_id
- contact_id
- contact_phone
- thread_id (unique identifier)
- system_prompt (stored once)
- conversation_data (JSON array of messages)
- last_activity_at
- expires_at (2 hours from creation)
- is_active
- message_count
- total_tokens_used
```

---

## 📝 **Default System Prompt**

The AI is instructed to:

1. **Act as shopping assistant** for your store
2. **Detect customer language** (English/Arabic/Urdu)
3. **Show products** with JSON format + buttons
4. **Skip payment questions** - directly show payment buttons
5. **Collect required details** (name, address, phone)
6. **Create orders** with proper action format
7. **Track interactions** for analytics

**Key Rules:**
- Always personalize based on customer info
- Never ask "how would you like to pay?" - show buttons
- Collect ALL required details before order creation
- Use JSON for products and payment selection
- Be conversational and helpful

---

## 🔍 **AI Configuration Check**

### **Requirements for AI to Work:**

1. ✅ `ai_powered_mode` = `true`
2. ✅ `openai_api_key` must be set (not empty)
3. ✅ At least one product in database
4. ✅ E-commerce configuration exists for tenant

### **Current Status Check:**
```php
// Check if AI is configured
$config = EcommerceConfiguration::where('tenant_id', tenant_id())->first();

if ($config && $config->ai_powered_mode && !empty($config->openai_api_key)) {
    echo "✅ AI is configured and ready!";
} else {
    echo "❌ AI needs configuration";
}
```

---

## 🛠️ **How to Configure AI**

### **Via Admin Panel:**

1. Navigate to: `/subdomain/{tenant}/ecommerce/settings`
2. Scroll to **AI Configuration** section
3. Enable **AI-Powered Mode**
4. Enter your **OpenAI API Key**
5. Select **AI Model** (default: gpt-3.5-turbo)
6. Adjust **Temperature** (0.7 recommended)
7. Set **Max Tokens** (500 recommended)
8. (Optional) Customize **System Prompt**
9. Click **Save Settings**

### **Via Database:**

```sql
UPDATE ecommerce_configurations 
SET 
  ai_powered_mode = 1,
  openai_api_key = 'sk-your-api-key-here',
  openai_model = 'gpt-3.5-turbo',
  ai_temperature = 0.7,
  ai_max_tokens = 500
WHERE tenant_id = YOUR_TENANT_ID;
```

---

## 📊 **Advanced Features**

### **Customer Profiling** (`CustomerProfile` model)
- Purchase history tracking
- Customer tier (Standard, VIP, Premium)
- Preferences and behavior patterns
- Total spent and order count
- Last purchase date

### **Sentiment Analysis** (`SentimentAnalysisService`)
- Detects positive/negative/neutral sentiment
- Adapts response tone accordingly
- Urgency level detection
- Price sensitivity analysis

### **Recommendation Engine** (`RecommendationEngineService`)
- Personalized product suggestions
- Based on purchase history
- Category preferences
- Collaborative filtering

### **Multi-Intent Detection**
Handles multiple requests in one message:
- Browse products
- Purchase intent
- Customer support
- Order tracking
- Return/exchange
- Price inquiry
- Product comparison
- Recommendation requests

---

## 💰 **Cost Management**

### **Token Usage Tracking:**
- Every conversation tracks `total_tokens_used`
- Logged in `ai_conversations` table
- Helps monitor API costs
- Can set limits per tenant

### **Cost Optimization:**
- Conversation threads reuse system prompt
- 30-minute session timeout
- Configurable `max_tokens` limit
- Option to use cheaper models (gpt-3.5-turbo)

---

## 🔐 **Security & Privacy**

- ✅ API keys stored encrypted in database
- ✅ Tenant isolation (each tenant has own config)
- ✅ No cross-tenant data sharing
- ✅ Conversation data stored locally
- ✅ Automatic cleanup of expired conversations
- ✅ GDPR-compliant data handling

---

## 📈 **Analytics & Logging**

### **Comprehensive Logging** (`EcommerceLogger`)

All AI interactions logged with:
- Tenant ID
- Contact information
- Message content
- AI response
- Token usage
- Processing time
- Errors and exceptions

**Log File:** `storage/logs/ecomorcelog.log`

**Log Prefixes:**
- `🤖 AI-SERVICE:` - Main service operations
- `🤖 AI-CONFIG:` - Configuration checks
- `🤖 AI-DATABASE:` - Database queries
- `🤖 AI-THREAD:` - Conversation management
- `🤖 AI-OPENAI:` - OpenAI API calls
- `🤖 AI-PARSE:` - Response parsing
- `🤖 AI-JSON:` - JSON handling
- `🧠 ADVANCED-AI:` - Advanced features

---

## 🧪 **Testing AI Configuration**

### **Artisan Commands:**

```bash
# Check e-commerce configuration
php artisan ecommerce:check

# Test AI e-commerce (simple)
php artisan test:ai-ecommerce-simple

# Test AI e-commerce (full)
php artisan test:ai-ecommerce
```

### **Manual Test:**

```php
use App\Services\AiEcommerceService;
use App\Models\Tenant\Contact;

$contact = Contact::first();
$service = new AiEcommerceService(tenant_id());

$result = $service->processMessage("Show me your products", $contact);

dd($result);
// Expected: ['handled' => true, 'response' => '...', 'buttons' => [...]]
```

---

## 🚀 **Next Steps**

### **To Enable AI:**

1. **Get OpenAI API Key**
   - Visit: https://platform.openai.com/api-keys
   - Create new secret key
   - Copy the key (starts with `sk-`)

2. **Configure in Admin Panel**
   - Go to E-commerce Settings
   - Enable AI-Powered Mode
   - Paste API key
   - Save settings

3. **Test with WhatsApp**
   - Send message: "Show me products"
   - AI should respond with product list + buttons

### **Recommended Settings:**

| Setting | Recommended Value | Reason |
|---------|------------------|--------|
| Model | `gpt-3.5-turbo` | Cost-effective, fast |
| Temperature | `0.7` | Balanced creativity |
| Max Tokens | `500` | Sufficient for responses |
| Session Timeout | `30 minutes` | Good UX, cost-efficient |

---

## 📚 **Related Files**

### **Services:**
- `app/Services/AiEcommerceService.php` - Main AI service
- `app/Services/AdvancedAiEcommerceService.php` - Advanced features
- `app/Services/CustomerProfileService.php` - Customer profiling
- `app/Services/RecommendationEngineService.php` - Recommendations
- `app/Services/SentimentAnalysisService.php` - Sentiment analysis
- `app/Services/EcommerceLogger.php` - Logging utility

### **Models:**
- `app/Models/Tenant/EcommerceConfiguration.php` - Config storage
- `app/Models/Tenant/AiConversation.php` - Thread management
- `app/Models/Tenant/CustomerProfile.php` - Customer data
- `app/Models/Tenant/Product.php` - Product catalog
- `app/Models/Tenant/Order.php` - Order management

### **Configuration:**
- `config/aimodel.php` - Available AI models
- `database/migrations/2024_11_19_000001_add_ai_configuration_to_ecommerce_configurations.php`

### **Documentation:**
- `ECOMMERCE_SYSTEM_OVERVIEW.md` - E-commerce features
- `IMPLEMENTATION_SUMMARY.md` - Dynamic sheets system
- `AI_ASSISTANT_TESTING_GUIDE.txt` - Testing guide

---

## ✅ **Summary**

Your WhatsMark system has a **production-ready AI e-commerce assistant** with:

✅ **OpenAI Integration** - GPT-3.5/GPT-4 support
✅ **Conversation Management** - Context-aware threads
✅ **Multi-language Support** - English, Arabic, Urdu
✅ **Advanced Features** - Sentiment analysis, profiling, recommendations
✅ **Cost Optimization** - Token tracking, session management
✅ **Comprehensive Logging** - Full audit trail
✅ **Tenant Isolation** - Secure multi-tenant architecture

**Status:** ⚠️ **Requires OpenAI API Key to activate**

Once you add your API key, the AI will automatically handle:
- Product browsing
- Order taking
- Payment selection
- Customer support
- Order tracking
- Personalized recommendations

**All through natural WhatsApp conversations!** 🚀
