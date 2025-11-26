# WhatsApp Duplicate Message Tracking Log

## 📍 Log File Location
```
storage/logs/whatsappmessageduplicate.log
```

---

## 🎯 Purpose
This dedicated log file tracks EVERY stage of WhatsApp message processing to identify exactly where duplicate messages occur. Each stage is logged with detailed context to help debug duplicate response issues.

---

## 📊 Log Stages Overview

### **STAGE 1: WEBHOOK_RECEIVED**
**When:** Webhook first received from WhatsApp  
**What it logs:**
- Request ID (unique identifier for this request)
- Message ID from WhatsApp
- Sender's phone number
- Message text content
- Tenant ID
- Timestamp

**Use:** Verify webhook was received and identify the message

---

### **STAGE 2: ATTEMPTING_LOCK**
**When:** Before attempting to acquire cache lock  
**What it logs:**
- Lock key name
- Lock TTL (time to live)

**Use:** Verify lock mechanism is being used

---

### **STAGE 3: LOCK_ACQUIRED**
**When:** Successfully acquired the cache lock  
**What it logs:**
- Request ID
- Message ID

**Use:** Confirm this request got the lock

---

### **STAGE 3A: LOCK_FAILED** ⚠️
**When:** Failed to acquire lock (another process has it)  
**What it logs:**
- Request ID
- Message ID
- Action: SKIPPED
- `prevented_duplicate: true`

**Use:** Verify duplicate webhooks are being blocked by lock

---

### **STAGE 4A: DUPLICATE_DETECTED_IN_DB** ⚠️
**When:** Message ID already exists in database  
**What it logs:**
- Request ID
- Message ID
- Action: SKIPPED
- `prevented_duplicate: true`

**Use:** Confirm message was already processed before

---

### **STAGE 4B: MESSAGE_IS_NEW** ✅
**When:** Message ID not in database (new message)  
**What it logs:**
- Request ID
- Message ID
- Action: PROCESSING

**Use:** Confirm message is new and will be processed

---

### **STAGE 5: CHECK_ACTIVE_FLOWS**
**When:** Checking if tenant has active bot flows  
**What it logs:**
- Has active flows (true/false)
- Will skip old bots (true/false)

**Use:** Determine if old bot system will be skipped

---

### **STAGE 6: FOUND_OLD_BOTS**
**When:** Old message/template bots matching trigger found  
**What it logs:**
- Trigger message text
- Template bots count
- Message bots count
- Bot IDs for both types
- Total matching bots

**Use:** Identify how many old bots match this trigger (should be 0 or 1)

---

### **STAGE 7A: SENDING_TEMPLATE_BOT** 📤
**When:** About to send template bot response  
**What it logs:**
- Bot type: TEMPLATE_BOT
- Template ID
- Template name
- Trigger keyword
- Reply type (1=exact, 2=contains, 3=first-time, 4=fallback)

**Use:** Confirm which template bot is responding

---

### **STAGE 8A: TEMPLATE_BOT_SENT** ✅
**When:** Template bot response sent successfully  
**What it logs:**
- Response sent: true
- Will skip message bots: true
- Will skip flows: true
- `prevented_duplicate: true`

**Use:** Verify template bot sent and other systems will be skipped

---

### **STAGE 7B: SENDING_MESSAGE_BOT** 📤
**When:** About to send message bot response  
**What it logs:**
- Bot type: MESSAGE_BOT
- Bot ID
- Trigger keyword
- Message preview (first 50 chars)
- Reply type

**Use:** Confirm which message bot is responding

---

### **STAGE 8B: MESSAGE_BOT_SENT** ✅
**When:** Message bot response sent successfully  
**What it logs:**
- Response sent: true
- Will skip other message bots: true
- Will skip flows: true
- `prevented_duplicate: true`

**Use:** Verify message bot sent and flow system will be skipped

---

### **STAGE 9: FLOW_PROCESSING_DECISION**
**When:** Deciding whether to process flow system  
**What it logs:**
- Old bot responded (true/false)
- Ecommerce handled (true/false)
- Will process flow (true/false)
- Skip reason (if any)

**Use:** Understand the decision logic for flow processing

---

### **STAGE 10A: SKIPPING_FLOW_OLD_BOT** ⚠️
**When:** Skipping flow because old bot already responded  
**What it logs:**
- Prevented duplicate: true
- Reason: "Old message/template bot already sent response"

**Use:** Verify flows are NOT executing when old bots respond

---

### **STAGE 10B: SKIPPING_FLOW_ECOMMERCE** ⚠️
**When:** Skipping flow because ecommerce already responded  
**What it logs:**
- Prevented duplicate: true
- Reason: "E-commerce already sent response"

**Use:** Verify flows are NOT executing when ecommerce responds

---

### **STAGE 10C: PROCESSING_FLOW** ✅
**When:** Processing flow system (no previous responses)  
**What it logs:**
- Action: PROCESSING_FLOW_SYSTEM
- No previous responses: true

**Use:** Confirm flows only execute when nothing else responded

---

### **STAGE 11: FLOW_PROCESSING_COMPLETE** ✅
**When:** Flow system finished executing  
**What it logs:**
- Flow executed: true

**Use:** Confirm flow execution completed

---

## 🔍 How to Debug Duplicates

### Step 1: Send a Test Message
Send "test" from WhatsApp

### Step 2: Check the Log
```bash
tail -100 storage/logs/whatsappmessageduplicate.log
```

### Step 3: Analyze the Flow

#### ✅ **Normal Single Response (Good)**
```
STAGE 1: WEBHOOK_RECEIVED
STAGE 2: ATTEMPTING_LOCK
STAGE 3: LOCK_ACQUIRED
STAGE 4B: MESSAGE_IS_NEW
STAGE 5: CHECK_ACTIVE_FLOWS (has_active_flows: false)
STAGE 6: FOUND_OLD_BOTS (message_bots_count: 1)
STAGE 7B: SENDING_MESSAGE_BOT
STAGE 8B: MESSAGE_BOT_SENT
STAGE 9: FLOW_PROCESSING_DECISION
STAGE 10A: SKIPPING_FLOW_OLD_BOT (prevented_duplicate: true)
```
**Result:** ✅ Only message bot sent, flow was skipped = NO DUPLICATE

---

#### ❌ **Duplicate Response (Bad - Old Issue)**
```
STAGE 1: WEBHOOK_RECEIVED
...
STAGE 7B: SENDING_MESSAGE_BOT
STAGE 8B: MESSAGE_BOT_SENT (will_skip_flows: true)
STAGE 9: FLOW_PROCESSING_DECISION (old_bot_responded: false) ← BUG!
STAGE 10C: PROCESSING_FLOW ← SHOULD NOT HAPPEN!
```
**Problem:** Flow processed even though old bot responded = DUPLICATE

---

#### ⚠️ **Race Condition (Prevented)**
```
Request 1:
STAGE 1: WEBHOOK_RECEIVED (message_id: wamid.xxx)
STAGE 2: ATTEMPTING_LOCK
STAGE 3: LOCK_ACQUIRED
STAGE 4B: MESSAGE_IS_NEW
... processing ...

Request 2:
STAGE 1: WEBHOOK_RECEIVED (message_id: wamid.xxx) ← SAME ID!
STAGE 2: ATTEMPTING_LOCK
STAGE 3A: LOCK_FAILED (prevented_duplicate: true) ← BLOCKED!
```
**Result:** ✅ Second request blocked by lock = NO DUPLICATE

---

#### ⚠️ **Already Processed (Prevented)**
```
Request 1:
... completed processing ...

Request 2 (later):
STAGE 1: WEBHOOK_RECEIVED (message_id: wamid.xxx)
STAGE 2: ATTEMPTING_LOCK
STAGE 3: LOCK_ACQUIRED
STAGE 4A: DUPLICATE_DETECTED_IN_DB (prevented_duplicate: true) ← BLOCKED!
```
**Result:** ✅ Already in database = NO DUPLICATE

---

## 📈 Quick Analysis Commands

### View Last 50 Requests
```bash
tail -200 storage/logs/whatsappmessageduplicate.log | grep "STAGE 1"
```

### Check for Duplicate Webhooks
```bash
grep "LOCK_FAILED\|DUPLICATE_DETECTED_IN_DB" storage/logs/whatsappmessageduplicate.log | tail -20
```

### Count Responses Per Message ID
```bash
grep "message_id" storage/logs/whatsappmessageduplicate.log | grep "wamid" | sort | uniq -c
```

### Check if Old Bots AND Flows Both Executed
```bash
grep -A 5 "MESSAGE_BOT_SENT" storage/logs/whatsappmessageduplicate.log | grep "PROCESSING_FLOW"
```
**Should return nothing!** If you see results, that's a duplicate.

### Find Messages with Multiple Bot Responses
```bash
# This script counts bot sends per message_id
awk '/message_id.*wamid/ {id=$0} /SENDING_/ {print id}' storage/logs/whatsappmessageduplicate.log | sort | uniq -c | sort -nr
```

---

## 🎯 Expected Patterns

### Pattern 1: New Flow Handles Message
```
1_WEBHOOK_RECEIVED
2_ATTEMPTING_LOCK
3_LOCK_ACQUIRED
4B_MESSAGE_IS_NEW
5_CHECK_ACTIVE_FLOWS (has_active_flows: true)
9_FLOW_PROCESSING_DECISION
10C_PROCESSING_FLOW
11_FLOW_PROCESSING_COMPLETE
```

### Pattern 2: Old Bot Handles Message
```
1_WEBHOOK_RECEIVED
2_ATTEMPTING_LOCK
3_LOCK_ACQUIRED
4B_MESSAGE_IS_NEW
5_CHECK_ACTIVE_FLOWS (has_active_flows: false)
6_FOUND_OLD_BOTS
7B_SENDING_MESSAGE_BOT
8B_MESSAGE_BOT_SENT
9_FLOW_PROCESSING_DECISION
10A_SKIPPING_FLOW_OLD_BOT
```

### Pattern 3: Duplicate Webhook Blocked
```
Request 1: Full processing...
Request 2: 1_WEBHOOK_RECEIVED → 2_ATTEMPTING_LOCK → 3A_LOCK_FAILED
```

---

## 🚨 Red Flags (What to Look For)

### 🔴 Same message_id appears twice with different request_ids
**Indicates:** Duplicate webhook received

### 🔴 Both SENDING_MESSAGE_BOT and PROCESSING_FLOW for same request_id
**Indicates:** Old bot AND flow both executed (duplicate!)

### 🔴 Multiple MESSAGE_BOT_SENT or TEMPLATE_BOT_SENT for same message_id
**Indicates:** Multiple old bots responded (duplicate!)

### 🔴 STAGE 9 shows old_bot_responded: false but STAGE 8B exists
**Indicates:** $bot_responded flag not being set correctly

---

## 💡 Troubleshooting Tips

### If You See Duplicates:

1. **Check request_id pattern**
   - Same request_id = one webhook processed twice (BUG in code)
   - Different request_ids = multiple webhooks received (expected, locks should handle)

2. **Check prevented_duplicate flags**
   - If you see `prevented_duplicate: true` = mechanism working ✅
   - If missing on duplicates = mechanism not triggered ❌

3. **Check bot_responded flag**
   - STAGE 8A/8B should show `will_skip_flows: true`
   - STAGE 9 should show `old_bot_responded: true`
   - If mismatch = variable scope issue

4. **Check timing**
   - Look at timestamps
   - <100ms apart = race condition (lock should handle)
   - >1 second apart = separate webhooks (database check should handle)

---

## 📝 Log Format

Each entry includes:
```
[Timestamp] [Request ID] [STAGE: <name>] [TENANT: <id>]
{
  "request_id": "req_xxx",
  "message_id": "wamid.xxx",
  "stage": "X_STAGE_NAME",
  ... stage-specific data ...
}
--------------------------------------------------------------------------------
```

---

## ✅ Success Criteria

After implementing the fix, you should see:

1. ✅ Only ONE of these per message:
   - 7A_SENDING_TEMPLATE_BOT **OR**
   - 7B_SENDING_MESSAGE_BOT **OR**
   - 10C_PROCESSING_FLOW

2. ✅ If 7A or 7B appears, you should see:
   - 10A_SKIPPING_FLOW_OLD_BOT

3. ✅ For duplicate webhooks:
   - 3A_LOCK_FAILED **OR**
   - 4A_DUPLICATE_DETECTED_IN_DB

4. ✅ `prevented_duplicate: true` appears whenever duplicates are avoided

---

**Status:** ✅ Comprehensive logging system implemented  
**Next Step:** Test by sending messages and analyzing the log
