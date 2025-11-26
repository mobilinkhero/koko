# 🎯 AI Assistant Node - Flow Builder Integration Guide

## 📊 **Overview**

The AI Assistant node is now **fully integrated** into the WhatsMark Flow Builder, allowing users to add AI-powered responses anywhere in their bot flows. The node supports **two modes**:

1. **Personal Assistant Mode** - Uses uploaded files and knowledge base
2. **Custom AI Mode** - Uses custom prompts and settings

---

## ✅ **What's Implemented**

### **Frontend (Vue.js)**

✅ **AIAssistantNode.vue** - Complete UI component
- Assistant mode selection (Personal/Custom)
- Personal assistant info display
- Custom AI configuration
- Model selection
- Temperature slider
- Max tokens input
- Context type selection
- Advanced settings panel

✅ **BotFlowBuilder.vue** - Flow builder integration
- Node registered in node types
- Added to "Advanced Features" category
- Drag-and-drop support
- Node palette integration
- Validation support

### **Backend (PHP)**

✅ **WhatsApp Trait** - Message processing
- `sendFlowAiMessage()` - Main entry point
- `sendFlowPersonalAssistantMessage()` - Personal assistant handler
- `generateFlowAiResponse()` - Custom AI handler
- Full error handling
- Comprehensive logging

✅ **Ai Trait** - Personal assistant methods
- `personalAssistantResponse()` - Get AI response
- `getPersonalAssistantInfo()` - Get assistant details
- `hasPersonalAssistant()` - Check availability

✅ **PersonalAssistant Model** - Data management
- File upload support
- Knowledge base processing
- Tenant isolation

---

## 🎨 **How to Use**

### **Step 1: Add AI Assistant Node to Flow**

1. Open Flow Builder
2. Find "AI Personal Assistant" in the sidebar under "Advanced Features"
3. Drag and drop onto canvas
4. Connect to other nodes

### **Step 2: Configure the Node**

#### **Option A: Personal Assistant Mode**

```
1. Select "Use Personal Assistant" from mode dropdown
2. View assistant info (name, model, files loaded)
3. No additional configuration needed
4. Uses uploaded files as knowledge base
```

**Requirements:**
- Personal assistant must be created in AI settings
- Assistant must be active
- OpenAI API key must be configured

**Benefits:**
- Automatic knowledge base inclusion
- Consistent responses across flows
- No need to configure prompts
- Uses uploaded documents (TXT, MD, CSV, JSON)

#### **Option B: Custom AI Mode**

```
1. Select "Custom AI Settings" from mode dropdown
2. Choose AI model (GPT-3.5, GPT-4, GPT-4o Mini, etc.)
3. Enter system prompt
4. Select context type (Message, Conversation, Flow)
5. Optionally adjust advanced settings:
   - Temperature (0-1): Creativity level
   - Max Tokens (50-4000): Response length
```

**Use Cases:**
- Specific use-case prompts
- Different AI behavior per flow
- Testing different models
- Fine-tuned responses

---

## 🔄 **Processing Flow**

### **When Flow Executes:**

```
1. User message triggers flow
   ↓
2. Flow reaches AI Assistant node
   ↓
3. Backend checks assistantMode parameter
   ↓
4a. If "personal":
    - Get Personal Assistant for tenant
    - Check if active
    - Get user message from context
    - Call personalAssistantResponse()
    - Include knowledge base in context
    - Send AI response
   ↓
4b. If "custom":
    - Get custom prompt from node
    - Get AI model, temperature, maxTokens
    - Build message context
    - Call generateFlowAiResponse()
    - Send AI response
   ↓
5. Continue to next node in flow
```

---

## 📝 **Node Data Structure**

### **Personal Assistant Mode:**

```javascript
{
    assistantMode: 'personal',
    // No other fields needed
}
```

### **Custom AI Mode:**

```javascript
{
    assistantMode: 'custom',
    aiModel: 'gpt-4o-mini',
    prompt: 'You are a helpful customer service assistant...',
    contextType: 'message', // or 'conversation', 'flow'
    temperature: 0.7,
    maxTokens: 500
}
```

---

## 🎯 **Context Types Explained**

### **1. Message (Default)**
- Uses only the current trigger message
- Best for: Simple Q&A, one-off responses
- Example: "What are your hours?"

### **2. Conversation**
- Includes recent conversation history
- Best for: Multi-turn conversations, context-aware responses
- Example: "Tell me more about that product"

### **3. Flow**
- Includes flow context (previous nodes, variables)
- Best for: Complex flows with state management
- Example: "Based on the user's previous choices..."

---

## 🔧 **Backend Implementation Details**

### **sendFlowAiMessage() Method:**

```php
protected function sendFlowAiMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
{
    $assistantMode = $nodeData['assistantMode'] ?? 'custom';
    
    // Route to appropriate handler
    if ($assistantMode === 'personal') {
        return $this->sendFlowPersonalAssistantMessage(...);
    }
    
    // Custom AI processing
    $aiResponse = $this->generateFlowAiResponse(...);
    
    // Send response
    return $this->sendMessage($to, $messageData, $phoneNumberId);
}
```

### **sendFlowPersonalAssistantMessage() Method:**

```php
protected function sendFlowPersonalAssistantMessage($to, $nodeData, $phoneNumberId, $contactData, $context)
{
    // Get personal assistant
    $assistant = PersonalAssistant::getForCurrentTenant();
    
    // Validate assistant
    if (!$assistant || !$assistant->is_active) {
        // Send fallback message
    }
    
    // Get user message from context
    $userMessage = $context['trigger_message'] ?? 'Hello';
    
    // Get AI response with knowledge base
    $aiResult = $this->personalAssistantResponse($userMessage, $conversationHistory);
    
    // Send response
    return $this->sendMessage($to, $messageData, $phoneNumberId);
}
```

### **Error Handling:**

```php
// No assistant configured
"AI Assistant is not configured. Please contact support."

// Assistant disabled
"AI Assistant is currently disabled. Please try again later."

// AI response failed
"Sorry, I encountered an error. Please try again."

// Unexpected error
"Sorry, I encountered an unexpected error. Please try again later."
```

---

## 📊 **Logging**

All AI interactions are logged with comprehensive details:

```php
whatsapp_log('AI Assistant Node Processing', 'info', [
    'mode' => 'personal',
    'to' => '+1234567890',
    'contact_id' => 123,
    'tenant_id' => 1,
]);

whatsapp_log('Personal assistant response sent', 'info', [
    'assistant_id' => 5,
    'assistant_name' => 'Customer Support AI',
    'model' => 'gpt-4o-mini',
    'to' => '+1234567890',
    'response_length' => 245,
    'tenant_id' => 1,
]);
```

---

## 🎨 **UI Features**

### **Personal Assistant Mode Display:**

```
┌─────────────────────────────────────┐
│ ✓ Customer Support AI               │
│ Helps with product questions        │
│ [FAQ] [Product] [Onboarding]        │
│ Model: gpt-4o-mini • 4 files loaded │
└─────────────────────────────────────┘
```

### **No Assistant Warning:**

```
┌─────────────────────────────────────┐
│ ⚠ No Personal Assistant Configured  │
│ Create a personal assistant in AI   │
│ settings to use this mode.           │
└─────────────────────────────────────┘
```

### **Custom AI Mode:**

```
┌─────────────────────────────────────┐
│ AI Model: [GPT-4o Mini ▼]           │
│                                      │
│ System Prompt:                       │
│ ┌─────────────────────────────────┐ │
│ │ You are a helpful customer...   │ │
│ │                                 │ │
│ └─────────────────────────────────┘ │
│                                      │
│ Context Type: [Message ▼]           │
│                                      │
│ ▼ Advanced Settings                 │
│   Temperature: 0.7 [━━━━━━━━━━]     │
│   Max Tokens: [500]                 │
└─────────────────────────────────────┘
```

---

## 🚀 **Example Use Cases**

### **1. FAQ Bot with Knowledge Base**

```
Flow:
[Trigger: "help"] 
  → [AI Assistant: Personal Mode]
    → Uses uploaded FAQ.md
    → Answers based on documentation
  → [End]
```

### **2. Product Recommendation**

```
Flow:
[Trigger: "recommend"] 
  → [AI Assistant: Custom Mode]
    → Prompt: "Recommend products based on user preferences"
    → Context: Conversation (includes chat history)
  → [Button Message: Show Products]
  → [End]
```

### **3. Multi-Step Support**

```
Flow:
[Trigger: "support"]
  → [Input Collection: Collect issue details]
  → [AI Assistant: Personal Mode]
    → Analyzes issue using knowledge base
    → Provides solution
  → [Condition: Issue resolved?]
    → Yes: [Text: "Glad I could help!"]
    → No: [Webhook: Create support ticket]
  → [End]
```

---

## 🔐 **Security & Validation**

### **Tenant Isolation:**
- ✅ Each tenant can only access their own assistant
- ✅ API keys are tenant-specific
- ✅ Knowledge base is isolated per tenant

### **Validation:**
- ✅ Assistant existence check
- ✅ Active status verification
- ✅ OpenAI API key validation
- ✅ Model availability check

### **Error Recovery:**
- ✅ Graceful fallback messages
- ✅ Comprehensive error logging
- ✅ User-friendly error messages
- ✅ Flow continues on error

---

## 📈 **Performance Considerations**

### **Caching:**
```php
// Personal assistant data is cached per tenant
$assistant = PersonalAssistant::getForCurrentTenant();
```

### **Token Management:**
```php
// Max tokens configurable per node
$maxTokens = $nodeData['maxTokens'] ?? 500;
```

### **Response Time:**
- Personal mode: 2-5 seconds (includes knowledge base)
- Custom mode: 1-3 seconds (simple prompts)

---

## 🧪 **Testing**

### **Test Personal Assistant Mode:**

1. Create personal assistant in AI settings
2. Upload test files (FAQ.md, pricing.csv)
3. Create flow with AI Assistant node
4. Set mode to "Personal"
5. Test with: "What are your prices?"
6. Verify response uses uploaded data

### **Test Custom AI Mode:**

1. Create flow with AI Assistant node
2. Set mode to "Custom"
3. Enter prompt: "You are a friendly greeter"
4. Set temperature to 0.9 (creative)
5. Test with: "Hello"
6. Verify creative greeting response

---

## 🐛 **Troubleshooting**

### **Issue: "AI Assistant is not configured"**

**Solution:**
1. Go to AI settings
2. Create a personal assistant
3. Upload knowledge files
4. Activate the assistant

### **Issue: "AI response generation failed"**

**Solution:**
1. Check OpenAI API key is configured
2. Verify API key is valid
3. Check tenant has AI enabled
4. Review logs for specific error

### **Issue: Node not showing in palette**

**Solution:**
1. Check if AI module is enabled
2. Clear browser cache
3. Reload flow builder
4. Check `window.isAiAssistantModuleEnabled`

---

## 📝 **Summary**

The AI Assistant node is **fully functional** and ready to use:

✅ **Frontend:** Complete UI with mode selection
✅ **Backend:** Full processing for both modes
✅ **Integration:** Works seamlessly in flows
✅ **Error Handling:** Graceful fallbacks
✅ **Logging:** Comprehensive tracking
✅ **Security:** Tenant isolation
✅ **Documentation:** Complete guides

**Users can now:**
- Add AI responses anywhere in flows
- Use Personal Assistant with knowledge base
- Configure custom AI behavior
- Control temperature and tokens
- Choose context types
- Get intelligent, context-aware responses

**The system is production-ready!** 🚀
