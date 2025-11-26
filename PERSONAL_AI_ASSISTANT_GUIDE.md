# 🤖 Personal AI Assistant System - Complete Guide

## 📊 **System Overview**

Your WhatsMark platform has a **Personal AI Assistant** system that's completely separate from the e-commerce AI. This is a **document-based knowledge AI** that allows tenants to upload files and create custom AI assistants trained on their own data.

---

## 🎯 **Key Differences: E-commerce AI vs Personal AI Assistant**

| Feature | E-commerce AI | Personal AI Assistant |
|---------|--------------|---------------------|
| **Purpose** | Product sales, order processing | General knowledge, FAQs, support |
| **Data Source** | Products database, Google Sheets | Uploaded files (TXT, MD, CSV, JSON) |
| **Configuration** | Per-tenant e-commerce config | Per-assistant configuration |
| **Use Cases** | Shopping, checkout, payments | FAQs, documentation, data lookup |
| **Integration** | WhatsApp webhook (automatic) | Manual/API integration |
| **File Support** | No file uploads | ✅ TXT, MD, CSV, JSON (5MB max) |
| **Knowledge Base** | Product catalog | Custom uploaded documents |

---

## 🏗️ **Architecture Overview**

### **Core Components:**

```
┌─────────────────────────────────────────────────────────────┐
│ 1. PersonalAssistant Model                                  │
│    - Stores assistant configuration                         │
│    - Manages uploaded files metadata                        │
│    - Holds processed content (knowledge base)               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. PersonalAssistantFileService                             │
│    - Handles file uploads                                   │
│    - Processes different file types                         │
│    - Extracts and formats content for AI                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. Ai Trait (personalAssistantResponse method)              │
│    - Sends messages to OpenAI                               │
│    - Includes knowledge base in context                     │
│    - Manages conversation history                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. OpenAI API (via LLPhant)                                 │
│    - Processes queries with context                         │
│    - Returns AI-generated responses                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 **Database Schema**

### **personal_assistants Table:**

```sql
CREATE TABLE personal_assistants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    system_instructions TEXT NOT NULL,
    model VARCHAR(50) DEFAULT 'gpt-4o-mini',
    temperature DECIMAL(3,2) DEFAULT 0.70,
    max_tokens INT DEFAULT 1000,
    file_analysis_enabled BOOLEAN DEFAULT TRUE,
    uploaded_files JSON NULL,
    processed_content LONGTEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    use_case_tags JSON NULL,
    last_synced_at TIMESTAMP NULL,
    openai_assistant_id VARCHAR(255) NULL,
    openai_vector_store_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tenant_active (tenant_id, is_active)
);
```

### **Field Descriptions:**

- **`tenant_id`**: Links assistant to specific tenant
- **`name`**: Assistant display name (e.g., "Customer Support AI")
- **`description`**: Optional description of assistant's purpose
- **`system_instructions`**: Base AI behavior instructions
- **`model`**: OpenAI model to use (gpt-3.5-turbo, gpt-4, gpt-4o-mini, etc.)
- **`temperature`**: Creativity level (0.0 = focused, 2.0 = creative)
- **`max_tokens`**: Maximum response length
- **`file_analysis_enabled`**: Whether to include uploaded files in context
- **`uploaded_files`**: JSON array of file metadata
- **`processed_content`**: Combined text from all uploaded files
- **`is_active`**: Whether assistant is enabled
- **`use_case_tags`**: Categories (faq, product, onboarding, csv, sop, general)

---

## 🔄 **Complete Workflow**

### **Phase 1: Assistant Creation**

```php
// User creates assistant via UI
PersonalAssistant::create([
    'tenant_id' => $tenantId,
    'name' => 'Customer Support AI',
    'description' => 'Helps with product questions',
    'system_instructions' => 'You are a helpful customer service assistant...',
    'model' => 'gpt-4o-mini',
    'temperature' => 0.7,
    'max_tokens' => 1000,
    'file_analysis_enabled' => true,
    'is_active' => true,
    'use_case_tags' => ['faq', 'product']
]);
```

### **Phase 2: File Upload & Processing**

```php
// User uploads files
$fileService = new PersonalAssistantFileService();
$result = $fileService->uploadFiles($assistant, $uploadedFiles);

// For each file:
1. Validate file (size < 5MB, allowed extension)
2. Store file in: storage/app/tenant-files/{tenant_id}/{random_name}.{ext}
3. Extract content based on file type:
   - TXT/MD: Read as plain text
   - CSV: Parse headers and rows, format as structured data
   - JSON: Parse and format hierarchically
4. Add to uploaded_files JSON array
5. Append content to processed_content field
```

### **File Processing Examples:**

#### **TXT/Markdown Files:**
```
Input: README.md
Output: Plain text content (truncated if > 50,000 chars)
```

#### **CSV Files:**
```
Input: pricing.csv
Output:
CSV Data Structure:
Headers: Plan, Price, Features

Sample Data (showing 100 of 150 records):
Row 1:
  Plan: Starter
  Price: $29
  Features: 1000 contacts, 5000 messages
Row 2:
  Plan: Professional
  Price: $79
  Features: 5000 contacts, 25000 messages
...
```

#### **JSON Files:**
```
Input: config.json
Output:
JSON Data Structure:
  api_version: v1
  features:
    Array with 5 items:
      [0]: whatsapp_integration
      [1]: ai_assistant
      [2]: bot_flows
      ...
  pricing:
    starter: $29
    professional: $79
    ...
```

### **Phase 3: Query Processing**

```php
// User sends message to assistant
use App\Traits\Ai;

class MyController {
    use Ai;
    
    public function chat(Request $request) {
        $message = $request->input('message');
        $conversationHistory = $request->input('history', []);
        
        $response = $this->personalAssistantResponse($message, $conversationHistory);
        
        return response()->json($response);
    }
}
```

**Internal Flow:**

```php
personalAssistantResponse($message, $conversationHistory) {
    1. Get active assistant for current tenant
    2. Check if assistant is active
    3. Build message array:
       - System message: system_instructions + processed_content
       - Conversation history (if provided)
       - Current user message
    4. Configure OpenAI:
       - Model: assistant->model
       - Temperature: assistant->temperature
       - Max tokens: assistant->max_tokens
    5. Send to OpenAI via LLPhant
    6. Return response
}
```

**Message Structure Sent to OpenAI:**

```json
[
    {
        "role": "system",
        "content": "You are a helpful customer service assistant...\n\n=== KNOWLEDGE BASE ===\n\n=== FILE: pricing.csv ===\nCSV Data Structure:\nHeaders: Plan, Price, Features\n...\n=== END FILE ===\n\n=== FILE: faq.md ===\n# Frequently Asked Questions\n...\n=== END FILE ===\n\n=== END KNOWLEDGE BASE ==="
    },
    {
        "role": "user",
        "content": "Previous user message"
    },
    {
        "role": "assistant",
        "content": "Previous AI response"
    },
    {
        "role": "user",
        "content": "What are your pricing plans?"
    }
]
```

---

## 🎯 **Use Cases**

### **1. FAQ Automation**

**Setup:**
```
Upload: faq.md
Content: Common questions and answers
Use Case Tag: 'faq'
```

**Example Queries:**
- "What are your business hours?"
- "How do I reset my password?"
- "Do you offer refunds?"

**AI Response:** Pulls answers directly from FAQ document

---

### **2. Product Enquiries**

**Setup:**
```
Upload: products.csv, product_specs.json
Content: Product catalog with detailed specs
Use Case Tag: 'product'
```

**Example Queries:**
- "What's the difference between Pro and Enterprise plans?"
- "Which plan includes AI features?"
- "How many contacts can I have on the Starter plan?"

**AI Response:** Extracts data from CSV/JSON and provides accurate information

---

### **3. Onboarding & Setup Help**

**Setup:**
```
Upload: setup_guide.md, installation.txt
Content: Step-by-step setup instructions
Use Case Tag: 'onboarding'
```

**Example Queries:**
- "How do I get started?"
- "What's the setup process?"
- "How long does implementation take?"

**AI Response:** Provides guided instructions from documentation

---

### **4. CSV Data Lookups**

**Setup:**
```
Upload: customer_data.csv, pricing_table.csv
Content: Structured data tables
Use Case Tag: 'csv'
```

**Example Queries:**
- "What's the price for 10,000 contacts?"
- "Which customers are on the Enterprise plan?"
- "Show me all features included in Professional"

**AI Response:** Queries CSV data and returns specific information

---

### **5. Internal SOPs/Team Guides**

**Setup:**
```
Upload: hr_policies.md, team_handbook.txt
Content: Internal documentation
Use Case Tag: 'sop'
```

**Example Queries:**
- "What's the vacation policy?"
- "How do I submit an expense report?"
- "What are the working hours?"

**AI Response:** References internal documentation

---

## 🔧 **Configuration Options**

### **Available AI Models:**

```php
const AVAILABLE_MODELS = [
    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
    'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo (16K Context)',
    'gpt-4' => 'GPT-4',
    'gpt-4-turbo' => 'GPT-4 Turbo',
    'gpt-4o-mini' => 'GPT-4o Mini (Fast & Cost-effective)', // Recommended
];
```

**Model Selection Guide:**
- **gpt-4o-mini**: Best for most use cases (fast, cheap, good quality)
- **gpt-3.5-turbo**: Budget option, faster but less accurate
- **gpt-4**: Most accurate, slower, expensive
- **gpt-4-turbo**: Balanced (speed + accuracy)

### **Temperature Settings:**

```
0.0 - 0.3: Very focused, factual responses (good for FAQs, data lookup)
0.4 - 0.7: Balanced (recommended for general use)
0.8 - 1.2: Creative, varied responses (good for marketing copy)
1.3 - 2.0: Very creative (experimental, may be inconsistent)
```

### **Max Tokens:**

```
500: Short, concise answers
1000: Standard responses (recommended)
2000: Detailed explanations
4000: Very comprehensive responses
```

---

## 📝 **API Methods**

### **1. Check if Assistant Exists:**

```php
use App\Traits\Ai;

if ($this->hasPersonalAssistant()) {
    // Assistant is configured and active
}
```

### **2. Get Assistant Info:**

```php
$info = $this->getPersonalAssistantInfo();

// Returns:
[
    'id' => 1,
    'name' => 'Customer Support AI',
    'description' => 'Helps with product questions',
    'model' => 'gpt-4o-mini',
    'is_active' => true,
    'has_files' => true,
    'file_count' => 4,
    'use_cases' => ['FAQs Automation', 'Product Enquiries']
]
```

### **3. Send Message to Assistant:**

```php
$response = $this->personalAssistantResponse(
    $message, 
    $conversationHistory
);

// Returns:
[
    'status' => true,
    'message' => 'AI response text...',
    'assistant_name' => 'Customer Support AI',
    'model_used' => 'gpt-4o-mini',
    'tokens_used' => 1000
]
```

### **4. Upload Files:**

```php
$fileService = new PersonalAssistantFileService();
$result = $fileService->uploadFiles($assistant, $uploadedFiles);

// Returns:
[
    'success' => true,
    'files_processed' => 3,
    'total_files' => 7,
    'content_size' => 45000
]
```

### **5. Remove File:**

```php
$fileService->removeFile($assistant, 'pricing.csv');
```

### **6. Clear All Files:**

```php
$fileService->clearAllFiles($assistant);
```

---

## 🔐 **Security & Limitations**

### **File Upload Security:**

```php
// Validation rules
MAX_FILE_SIZE = 5MB
ALLOWED_EXTENSIONS = ['txt', 'md', 'csv', 'json']
MAX_CONTENT_LENGTH = 50,000 characters per file
MAX_TOTAL_CONTENT = 250,000 characters (all files combined)
```

### **Tenant Isolation:**

```php
// Files stored per tenant
storage/app/tenant-files/{tenant_id}/{random_filename}.ext

// Only current tenant can access their assistant
PersonalAssistant::getForCurrentTenant()
```

### **API Key Security:**

```php
// Uses tenant's OpenAI API key
$apiKey = get_tenant_setting_from_db('whats-mark', 'openai_secret_key');

// Stored encrypted in database
```

---

## 🎨 **Frontend Integration**

### **Route:**

```
/{subdomain}/ai-assistant
```

### **Livewire Component:**

```php
PersonalAssistantManager.php
- Create/edit assistants
- Upload files
- Test chat interface
- View file list
- Manage settings
```

### **UI Features:**

- ✅ Assistant creation wizard
- ✅ File upload with drag-and-drop
- ✅ Real-time chat testing
- ✅ File management (view, delete)
- ✅ Model selection dropdown
- ✅ Temperature slider
- ✅ Use case tags
- ✅ Active/inactive toggle

---

## 🔄 **Integration with Bot Flows**

The Personal AI Assistant can be integrated into visual bot flows:

```javascript
// AI Assistant Node in Flow Builder
{
    type: 'ai_assistant',
    assistantMode: 'personal', // or 'custom'
    // When 'personal' mode:
    // - Uses configured Personal Assistant
    // - Includes uploaded files in context
    // - Uses assistant's model and settings
}
```

**Flow Example:**

```
Trigger: User says "help"
  ↓
AI Assistant Node (Personal mode)
  ↓
Response: Uses Personal Assistant with knowledge base
  ↓
Send Message Node
```

---

## 📊 **Performance Considerations**

### **Content Size Management:**

```php
// Per file limit
if (strlen($content) > 50000) {
    $content = substr($content, 0, 50000) . "\n[Content truncated...]";
}

// Total content limit
if (strlen($allContent) > 250000) {
    $allContent = substr($allContent, -250000); // Keep most recent
}
```

### **CSV Optimization:**

```php
// Limit CSV rows to prevent token overflow
$maxRecords = 100;
$recordCount = min(count($records), $maxRecords);

// Show sample + indicate total
"Sample Data (showing 100 of 500 records)"
```

### **JSON Optimization:**

```php
// Limit nesting depth
formatJsonForAI($data, $level = 0, $maxLevel = 3)

// Truncate large arrays
if (count($data) > 5) {
    // Show first 5 items
    "[... 45 more items]"
}
```

---

## 🧪 **Testing Guide**

### **Basic Test:**

```php
// 1. Create assistant
$assistant = PersonalAssistant::create([...]);

// 2. Upload test file
$fileService->uploadFiles($assistant, [$testFile]);

// 3. Send test query
$response = $this->personalAssistantResponse("What is WhatsMark?");

// 4. Verify response
assert($response['status'] === true);
assert(strlen($response['message']) > 0);
```

### **Test Files Provided:**

```
WHATSMARK_SERVICES_DEMO.md - Service documentation
WHATSMARK_PRICING_DEMO.csv - Pricing data
WHATSMARK_TECHNICAL_SPECS.json - Technical specs
AI_ASSISTANT_TESTING_GUIDE.txt - Testing scenarios
```

### **Test Queries:**

```
1. "What is WhatsMark?" - General info
2. "What are your pricing plans?" - CSV lookup
3. "What AI models do you support?" - JSON lookup
4. "How long does setup take?" - Document reference
5. "I have 500 customers, which plan?" - Reasoning
```

---

## 🚀 **Best Practices**

### **1. System Instructions:**

```
✅ Good:
"You are a helpful WhatsMark customer support assistant. 
Use the uploaded documents to provide accurate information. 
Always be professional and concise."

❌ Bad:
"Answer questions."
```

### **2. File Organization:**

```
✅ Good:
- faq.md (general questions)
- pricing.csv (structured pricing data)
- features.json (technical specifications)

❌ Bad:
- everything.txt (mixed content)
```

### **3. Use Case Tags:**

```
✅ Good:
['faq', 'product'] - Specific categories

❌ Bad:
['general'] - Too vague
```

### **4. Model Selection:**

```
✅ For most use cases: gpt-4o-mini
✅ For complex reasoning: gpt-4
✅ For budget: gpt-3.5-turbo
❌ Don't use gpt-4 for simple FAQs (overkill)
```

---

## 🔍 **Troubleshooting**

### **Issue: "No personal assistant configured"**

**Solution:**
```php
// Check if assistant exists
$assistant = PersonalAssistant::getForCurrentTenant();
if (!$assistant) {
    // Create one via UI or code
}
```

### **Issue: "Assistant responses are generic"**

**Solution:**
- Upload more specific documentation
- Improve system instructions
- Add relevant use case tags
- Increase max_tokens for detailed responses

### **Issue: "Can't find information in uploaded files"**

**Solution:**
- Verify file was processed successfully
- Check processed_content field has data
- Ensure file_analysis_enabled = true
- Try rephrasing the question

### **Issue: "File upload fails"**

**Solution:**
- Check file size (must be < 5MB)
- Verify file extension (txt, md, csv, json only)
- Check storage permissions
- Verify tenant_id is set correctly

---

## 📈 **Future Enhancements**

### **Planned Features:**

1. **PDF Support** - Extract text from PDF files
2. **Image Analysis** - OCR and image description
3. **Multiple Assistants** - Different assistants for different purposes
4. **Advanced Analytics** - Usage tracking, popular queries
5. **API Webhooks** - External system integration
6. **Voice Processing** - Audio transcription
7. **Conversation Memory** - Persistent chat history
8. **Fine-tuning** - Custom model training

---

## 📝 **Summary**

The Personal AI Assistant system provides:

✅ **Document-based knowledge AI** - Upload files, get answers
✅ **Multi-format support** - TXT, MD, CSV, JSON
✅ **Tenant isolation** - Each tenant has own assistants
✅ **Flexible configuration** - Model, temperature, tokens
✅ **Use case categorization** - FAQ, product, onboarding, etc.
✅ **Conversation context** - Maintains chat history
✅ **Easy integration** - Simple API via Ai trait
✅ **File management** - Upload, view, delete files
✅ **Cost optimization** - Content truncation, caching

**This is a powerful knowledge management system that turns your documents into an intelligent AI assistant!** 🚀
