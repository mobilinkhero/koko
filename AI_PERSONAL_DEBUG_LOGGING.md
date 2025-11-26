# 📝 Personal AI Assistant - Debug Logging Guide

## 📊 **Overview**

A comprehensive logging system has been added to track all Personal AI Assistant interactions. All logs are written to:

```
storage/logs/aipersonaldebug.log
```

---

## 🎯 **What Gets Logged**

### **1. Request Start**
```
================================================================================
[2025-11-24 01:05:30] PERSONAL AI ASSISTANT - REQUEST START
================================================================================
USER MESSAGE: What are your pricing plans?
CONVERSATION HISTORY COUNT: 0
```

### **2. Assistant Information**
```
ASSISTANT FOUND:
  - ID: 1
  - Name: Customer Support AI
  - Model: gpt-4o-mini
  - Temperature: 0.7
  - Max Tokens: 1000
  - Is Active: Yes
  - Files Loaded: 4
```

### **3. System Context**
```
SYSTEM CONTEXT:
  - System Instructions Length: 250 chars
  - Processed Content Length: 15000 chars
  - Total Context Length: 15250 chars
```

### **4. Conversation History** (if provided)
```
CONVERSATION HISTORY:
  [0] USER: Hello
  [1] ASSISTANT: Hi! How can I help you today?
  [2] USER: Tell me about your services
```

### **5. OpenAI Request**
```
SENDING TO OPENAI:
  - Model: gpt-4o-mini
  - Temperature: 0.7
  - Max Tokens: 1000
  - Total Messages: 3
  - API Call Time: 01:05:30
```

### **6. OpenAI Response**
```
OPENAI RESPONSE RECEIVED:
  - Response Time: 1250.5 ms
  - Response Length: 450 chars
  - Response Preview: We offer three pricing plans: Starter ($29/month), Professional ($79/month), and Enterprise ($199/month). Each plan includes different features and limits...

FULL AI RESPONSE:
---
We offer three pricing plans:

1. **Starter Plan** - $29/month
   - 1,000 contacts
   - 5,000 messages/month
   - Basic AI features

2. **Professional Plan** - $79/month
   - 5,000 contacts
   - 25,000 messages/month
   - Advanced AI features
   - Priority support

3. **Enterprise Plan** - $199/month
   - Unlimited contacts
   - Unlimited messages
   - All features
   - Dedicated support

Which plan would you like to know more about?
---
```

### **7. Final Response**
```
FINAL RESPONSE TO USER:
  - Status: SUCCESS
  - Assistant: Customer Support AI
  - Model: gpt-4o-mini
  - Message: We offer three pricing plans: Starter ($29/month)...
[2025-11-24 01:05:31] PERSONAL AI ASSISTANT - REQUEST END (SUCCESS)
================================================================================
```

---

## ❌ **Error Logging**

### **No Assistant Configured:**
```
ERROR: No personal assistant configured for this tenant
RESPONSE: No personal assistant configured
[2025-11-24 01:05:30] PERSONAL AI ASSISTANT - REQUEST END (ERROR)
================================================================================
```

### **Assistant Disabled:**
```
ASSISTANT FOUND:
  - ID: 1
  - Name: Customer Support AI
  - Is Active: No

ERROR: Personal assistant is disabled
RESPONSE: Assistant currently disabled
[2025-11-24 01:05:30] PERSONAL AI ASSISTANT - REQUEST END (ERROR)
================================================================================
```

### **Exception/Crash:**
```
EXCEPTION OCCURRED:
  - Error: Invalid API key provided
  - File: /app/Traits/Ai.php
  - Line: 192
  - Trace:
    #0 /app/Traits/Ai.php(192): LLPhant\Chat\OpenAIChat->generateChat()
    #1 /app/Traits/WhatsApp.php(2105): personalAssistantResponse()
    ...

FINAL RESPONSE TO USER:
  - Status: ERROR
  - Message: Assistant temporarily unavailable: Invalid API key provided
[2025-11-24 01:05:30] PERSONAL AI ASSISTANT - REQUEST END (ERROR)
================================================================================
```

---

## 📂 **Log File Location**

```bash
# Full path
storage/logs/aipersonaldebug.log

# From project root
cd storage/logs
cat aipersonaldebug.log

# Tail live logs
tail -f storage/logs/aipersonaldebug.log

# View last 50 lines
tail -n 50 storage/logs/aipersonaldebug.log

# Search for specific user message
grep "USER MESSAGE:" storage/logs/aipersonaldebug.log

# Search for errors
grep "ERROR:" storage/logs/aipersonaldebug.log

# Search for specific assistant
grep "Assistant: Customer Support AI" storage/logs/aipersonaldebug.log
```

---

## 🔍 **How to Use the Logs**

### **1. Debug User Queries**

Find what the user asked:
```bash
grep "USER MESSAGE:" storage/logs/aipersonaldebug.log
```

Output:
```
USER MESSAGE: What are your pricing plans?
USER MESSAGE: How do I get started?
USER MESSAGE: Do you offer refunds?
```

### **2. Check AI Responses**

Find what AI responded:
```bash
grep -A 20 "FULL AI RESPONSE:" storage/logs/aipersonaldebug.log
```

### **3. Monitor Performance**

Check response times:
```bash
grep "Response Time:" storage/logs/aipersonaldebug.log
```

Output:
```
  - Response Time: 1250.5 ms
  - Response Time: 890.2 ms
  - Response Time: 2100.8 ms
```

### **4. Find Errors**

Check for issues:
```bash
grep -B 5 -A 10 "ERROR:" storage/logs/aipersonaldebug.log
```

### **5. Track Specific Session**

Find all logs for a specific timestamp:
```bash
grep "2025-11-24 01:05" storage/logs/aipersonaldebug.log
```

---

## 📊 **Log Entry Structure**

Each request creates a complete log entry:

```
================================================================================
[TIMESTAMP] PERSONAL AI ASSISTANT - REQUEST START
================================================================================
USER MESSAGE: [User's question]
CONVERSATION HISTORY COUNT: [Number]

ASSISTANT FOUND:
  - [Assistant details]

SYSTEM CONTEXT:
  - [Context information]

CONVERSATION HISTORY:
  - [Previous messages if any]

SENDING TO OPENAI:
  - [API request details]

OPENAI RESPONSE RECEIVED:
  - [Response metadata]

FULL AI RESPONSE:
---
[Complete AI response]
---

FINAL RESPONSE TO USER:
  - [Final output details]

[TIMESTAMP] PERSONAL AI ASSISTANT - REQUEST END (SUCCESS/ERROR)
================================================================================
```

---

## 🛠️ **Troubleshooting with Logs**

### **Issue: AI not responding**

**Check:**
```bash
# 1. Is assistant active?
grep "Is Active:" storage/logs/aipersonaldebug.log | tail -1

# 2. Is API key configured?
grep "EXCEPTION OCCURRED:" storage/logs/aipersonaldebug.log | tail -10

# 3. What's the error?
grep "Error:" storage/logs/aipersonaldebug.log | tail -5
```

### **Issue: Wrong responses**

**Check:**
```bash
# 1. What context is being sent?
grep -A 5 "SYSTEM CONTEXT:" storage/logs/aipersonaldebug.log | tail -10

# 2. What files are loaded?
grep "Files Loaded:" storage/logs/aipersonaldebug.log | tail -5

# 3. What's the full AI response?
grep -A 30 "FULL AI RESPONSE:" storage/logs/aipersonaldebug.log | tail -40
```

### **Issue: Slow responses**

**Check:**
```bash
# Find slow responses (> 2000ms)
grep "Response Time:" storage/logs/aipersonaldebug.log | awk -F': ' '{if ($2 > 2000) print}'

# Average response time
grep "Response Time:" storage/logs/aipersonaldebug.log | awk -F': ' '{sum+=$2; count++} END {print "Average:", sum/count, "ms"}'
```

---

## 📈 **Log Analysis Examples**

### **Count Total Requests**
```bash
grep "REQUEST START" storage/logs/aipersonaldebug.log | wc -l
```

### **Count Successful Requests**
```bash
grep "REQUEST END (SUCCESS)" storage/logs/aipersonaldebug.log | wc -l
```

### **Count Failed Requests**
```bash
grep "REQUEST END (ERROR)" storage/logs/aipersonaldebug.log | wc -l
```

### **Most Common User Questions**
```bash
grep "USER MESSAGE:" storage/logs/aipersonaldebug.log | sort | uniq -c | sort -rn | head -10
```

### **Most Used Models**
```bash
grep "Model:" storage/logs/aipersonaldebug.log | grep -v "SENDING TO OPENAI" | sort | uniq -c
```

---

## 🔄 **Log Rotation**

To prevent the log file from growing too large:

### **Manual Rotation**
```bash
# Backup current log
cp storage/logs/aipersonaldebug.log storage/logs/aipersonaldebug-$(date +%Y%m%d).log

# Clear current log
> storage/logs/aipersonaldebug.log
```

### **Automatic Rotation** (Laravel Logrotate)

Add to `config/logging.php`:
```php
'aipersonal' => [
    'driver' => 'daily',
    'path' => storage_path('logs/aipersonaldebug.log'),
    'level' => 'debug',
    'days' => 14,
],
```

---

## 📝 **Example Log Session**

```
================================================================================
[2025-11-24 01:05:30] PERSONAL AI ASSISTANT - REQUEST START
================================================================================
USER MESSAGE: What are your pricing plans?
CONVERSATION HISTORY COUNT: 0

ASSISTANT FOUND:
  - ID: 1
  - Name: Customer Support AI
  - Model: gpt-4o-mini
  - Temperature: 0.7
  - Max Tokens: 1000
  - Is Active: Yes
  - Files Loaded: 4

SYSTEM CONTEXT:
  - System Instructions Length: 250 chars
  - Processed Content Length: 15000 chars
  - Total Context Length: 15250 chars

SENDING TO OPENAI:
  - Model: gpt-4o-mini
  - Temperature: 0.7
  - Max Tokens: 1000
  - Total Messages: 2
  - API Call Time: 01:05:30

OPENAI RESPONSE RECEIVED:
  - Response Time: 1250.5 ms
  - Response Length: 450 chars
  - Response Preview: We offer three pricing plans: Starter ($29/month), Professional ($79/month), and Enterprise ($199/month)...

FULL AI RESPONSE:
---
We offer three pricing plans:

1. **Starter Plan** - $29/month
   - 1,000 contacts
   - 5,000 messages/month

2. **Professional Plan** - $79/month
   - 5,000 contacts
   - 25,000 messages/month

3. **Enterprise Plan** - $199/month
   - Unlimited contacts
   - Unlimited messages

Which plan interests you?
---

FINAL RESPONSE TO USER:
  - Status: SUCCESS
  - Assistant: Customer Support AI
  - Model: gpt-4o-mini
  - Message: We offer three pricing plans: Starter ($29/month)...
[2025-11-24 01:05:31] PERSONAL AI ASSISTANT - REQUEST END (SUCCESS)
================================================================================
```

---

## ✅ **Summary**

The logging system provides:

✅ **Complete request tracking** - Every AI interaction logged
✅ **Detailed context** - See exactly what's sent to OpenAI
✅ **Full responses** - Complete AI responses captured
✅ **Performance metrics** - Response times tracked
✅ **Error details** - Full exception traces
✅ **Easy debugging** - Grep-friendly format
✅ **Production ready** - Handles errors gracefully

**Log file location:** `storage/logs/aipersonaldebug.log`

**Use it to:**
- Debug AI responses
- Monitor performance
- Track user queries
- Find errors
- Analyze usage patterns
- Optimize prompts
