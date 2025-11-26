# Duplicate WhatsApp Message Fix - Work Summary

## 🐛 Problem
User was receiving **duplicate bot responses** when sending messages via WhatsApp.

**Example**:
```
User sends: "test"
Bot responds: "test is working"
Bot responds: "test is working" ← DUPLICATE!
```

This happened at the same timestamp, indicating the same bot was processing twice.

---

## 🔍 Investigation & Root Causes Found

### Issue #1: Race Condition in Webhook Processing
**Location**: `app/Http/Controllers/Whatsapp/WhatsAppWebhookController.php` (Lines 123-176)

**Problem**: 
- Facebook/WhatsApp sometimes sends the same webhook **twice** within milliseconds
- The duplicate check had a **race condition**:
  - Request 1 checks DB → message not found ✓
  - Request 2 checks DB → message not found ✓ (race!)
  - Both process the same message → duplicate responses

**Solution**: Added **cache-based locking mechanism**
```php
$lock = \Illuminate\Support\Facades\Cache::lock('whatsapp_msg_' . $message_id, 10);

if ($lock->block(2)) {
    // Only ONE request can be here at a time
    $found = $this->checkMessageProcessed($message_id);
    if ($found) {
        // Already processed - skip
        return;
    }
    
    // Process message
    $this->processPayloadData($payload);
    
    // Release lock
    $lock->release();
} else {
    // Another process is handling this - skip
    return;
}
```

**Result**: Only ONE webhook request can process each message.

---

### Issue #2: Multiple Bots Responding to Same Trigger
**Location**: `app/Http/Controllers/Whatsapp/WhatsAppWebhookController.php` (Lines 543-629)

**Problem**:
- TWO separate loops processing bots:
  1. **Template bots** loop (lines 547-589)
  2. **Message bots** loop (lines 592-629)
- If user had:
  - Message Bot #1 with trigger "test"
  - Message Bot #2 with trigger "test"
  - **BOTH would send responses!**

**Solution**: Added `$bot_responded` flag
```php
// Track if any bot has responded
$bot_responded = false;

// Process template bots
foreach ($template_bots as $template) {
    if ($bot_responded) {
        break; // Stop if already responded
    }
    
    if (/* bot matches */) {
        // Send response
        $bot_responded = true;
        break; // Exit loop
    }
}

// Only process message bots if template bots didn't respond
if (!$bot_responded) {
    foreach ($message_bots as $message) {
        if ($bot_responded) {
            break; // Stop if already responded
        }
        
        if (/* bot matches */) {
            // Send response
            $bot_responded = true;
            break; // Exit loop
        }
    }
}
```

**Result**: Only the FIRST matching bot sends a response.

---

## 📝 Changes Made

### File Modified: `app/Http/Controllers/Whatsapp/WhatsAppWebhookController.php`

#### Change #1: Cache Lock for Duplicate Prevention (Lines 123-176)
```php
// Added cache-based locking
$lock = \Illuminate\Support\Facades\Cache::lock('whatsapp_msg_' . $message_id, 10);

try {
    if ($lock->block(2)) {
        // Check if already processed
        $found = $this->checkMessageProcessed($message_id);
        if ($found) {
            whatsapp_log('Duplicate Message Detected - Already Processed', 'warning');
            $lock->release();
            return;
        }
        
        // Process
        $this->processPayloadData($payload);
        $this->forwardWebhookData($feedData, $payload);
        $lock->release();
    } else {
        // Another process handling
        whatsapp_log('Duplicate Message Detected - Currently Being Processed', 'warning');
        return;
    }
} catch (\Exception $e) {
    $lock->release();
    throw $e;
}
```
ABC
#### Change #2: Bot Response Deduplication (Lines 533-629)
```php
// Added logging to see matched bots
whatsapp_log('Found bots matching trigger', 'info', [
    'trigger_msg' => $trigger_msg,
    'template_bots_count' => count($template_bots),
    'message_bots_count' => count($message_bots),
    'template_bot_ids' => array_column($template_bots, 'id'),
    'message_bot_ids' => array_column($message_bots, 'id'),
]);

// Added bot_responded flag to prevent multiple responses
$bot_responded = false;

// Template bots loop with early exit
foreach ($template_bots as $template) {
    if ($bot_responded) break;
    
    if (/* match condition */) {
        whatsapp_log('Sending template bot response', 'info');
        // Send response
        $bot_responded = true;
        whatsapp_log('Template bot response sent - stopping further bot processing', 'info');
        break;
    }
}

// Message bots only if template didn't respond
if (!$bot_responded) {
    foreach ($message_bots as $message) {
        if ($bot_responded) break;
        
        if (/* match condition */) {
            whatsapp_log('Sending message bot response', 'info');
            // Send response
            $bot_responded = true;
            whatsapp_log('Message bot response sent - stopping further bot processing', 'info');
            break;
        }
    }
}
```

#### Change #3: Enhanced Logging (Lines 533-624)
Added detailed logs at key points:
- When bots are found matching trigger
- When template bot sends response
- When message bot sends response
- When stopping further processing

---

## 🧪 How to Test

### Test 1: Send Message from WhatsApp
```
1. Send "test" from WhatsApp
2. Should receive ONE response only
3. Check timestamp - should be single time
```

### Test 2: Check Logs
```bash
# View recent WhatsApp logs
tail -50 storage/logs/whatsapp.log

# Or follow logs in real-time
tail -f storage/logs/whatsapp.log
```

**Look for these log entries:**

1. **"Found bots matching trigger"** - Shows how many bots matched
   ```json
   {
     "trigger_msg": "test",
     "template_bots_count": 0,
     "message_bots_count": 2,  ← Multiple bots!
     "message_bot_ids": [123, 456]
   }
   ```

2. **"Sending message bot response"** - Shows which bot is responding
   ```json
   {
     "message_id": 123,
     "trigger": "test",
     "message_preview": "test is working"
   }
   ```

3. **"Message bot response sent - stopping further bot processing"** - Confirms early exit

4. **"Duplicate Message Detected"** - If webhook was sent twice
   ```json
   {
     "message": "Duplicate Message Detected - Already Processed",
     "message_id": "wamid.xxx"
   }
   ```

---

## 🔍 Debugging Steps

### If Still Getting Duplicates:

#### Step 1: Check How Many Bots Match
Look in logs for: `"Found bots matching trigger"`

**If you see multiple bots:**
```json
"template_bots_count": 1,
"message_bots_count": 2,  ← Problem: 2 message bots!
```

**Action**: Go to your bot management and **delete or disable duplicate bots** with the same trigger.

---

#### Step 2: Check If Webhook Sent Twice
Look in logs for: `"Duplicate Message Detected"`

**If you see this:** The webhook was sent twice by Facebook, but our lock should prevent duplicate processing.

**If still getting duplicates:** Check your cache driver configuration.

---

#### Step 3: Verify Cache Driver
Check `.env` file:
```env
CACHE_DRIVER=redis  # ✅ Recommended
# or
CACHE_DRIVER=database  # ✅ Also works
# NOT
CACHE_DRIVER=file  # ⚠️ May not work with multiple servers
```

**Test cache locking:**
```bash
php artisan tinker
>>> Cache::lock('test', 10)->get()
# Should return true
```

---

#### Step 4: Check for Multiple Bot Types
You might have:
- ❌ **1 Template Bot** with trigger "test"
- ❌ **1 Message Bot** with trigger "test"
- **Both responding!**

**Solution**: Keep only ONE bot type with each trigger.

---

## 📊 Expected Log Flow

### Normal Single Message Flow:
```
1. Webhook Payload Received
2. Found bots matching trigger (count: 1)
3. Sending message bot response (id: 123)
4. Message bot response sent - stopping further bot processing
5. ✅ DONE
```

### Duplicate Webhook (Prevented):
```
Request 1:
1. Webhook Payload Received
2. Lock acquired
3. Found bots matching trigger
4. Sending message bot response
5. Lock released

Request 2:
1. Webhook Payload Received
2. Lock wait (blocked by request 1)
3. Lock acquired
4. Duplicate Message Detected - Already Processed
5. ❌ SKIPPED
```

### Multiple Bots (Now Fixed):
```
1. Found bots matching trigger (count: 2)
   - template_bots: []
   - message_bots: [123, 456]
2. Sending message bot response (id: 123)
3. Message bot response sent - stopping further bot processing
4. ✅ Bot 456 skipped (bot_responded = true)
```

---

## ✅ What to Do Next

### 1. **Test the Fix**
- Send "test" from WhatsApp
- Verify you get only ONE response
- Check timestamps are single

### 2. **Review Your Logs**
```bash
tail -50 storage/logs/whatsapp.log | grep "Found bots matching"
```

**Share this output** so we can see:
- How many bots are matching
- Which bots are sending responses
- If duplicates are being detected

### 3. **Clean Up Duplicate Bots** (If Found)
If logs show multiple bots matching the same trigger:
- Go to Bot Management in your admin panel
- Search for bots with trigger "test"
- **Delete or disable duplicates**
- Keep only ONE bot per trigger

### 4. **Verify Cache Configuration**
Ensure you're using Redis or Database cache driver for production:
```env
CACHE_DRIVER=redis
```

---

## 🎯 Summary

### What We Fixed:
1. ✅ **Race condition** - Added cache locking to prevent duplicate webhook processing
2. ✅ **Multiple bot responses** - Added `$bot_responded` flag to stop after first match
3. ✅ **Added logging** - Can now see exactly which bots are responding

### What You Need to Do:
1. 📝 **Test** - Send messages and verify single response
2. 🔍 **Check logs** - Look for the new log entries
3. 🗑️ **Clean up** - Remove duplicate bots if found
4. 📤 **Share logs** - If still having issues, share log output

---

## 📋 Quick Checklist

- [ ] Code changes deployed
- [ ] Send test message from WhatsApp
- [ ] Receive only ONE response
- [ ] Check `storage/logs/whatsapp.log` for new logging
- [ ] Look for "Found bots matching trigger" entry
- [ ] Verify `message_bots_count` or `template_bots_count` is 1 (not 2+)
- [ ] No duplicate responses seen
- [ ] Cache driver is Redis or Database (not file/array)

---

## 🚨 Still Getting Duplicates?

**Run this and share output:**
```bash
# Check recent logs
tail -100 storage/logs/whatsapp.log | grep -A 5 "Found bots matching"

# Check for duplicate detections
grep "Duplicate Message" storage/logs/whatsapp.log | tail -20
```

**Then share**:
1. The log output above
2. How many Message/Template bots you have with trigger "test"
3. Your CACHE_DRIVER setting from .env

This will help identify the exact cause!

---

---

## 🆕 NEW FIX: Old Bots + Flows Duplicate Prevention

### Issue #3: Both Old Bots AND New Flows Responding
**Location**: `app/Http/Controllers/Whatsapp/WhatsAppWebhookController.php` (Lines 648-676)

**Problem**: 
- If tenant had NO active flows, old bots would process (lines 524-629)
- Then code would fall through to `process_flows:` label (line 648)
- Flow processing would STILL execute even though old bot already responded
- **Result**: BOTH old bot AND flow sent responses = DUPLICATE!

**Solution**: Check if old bots responded before processing flows
```php
// At process_flows label (line 648)
$oldBotResponded = isset($bot_responded) && $bot_responded === true;

if ($oldBotResponded) {
    // Skip flow - old bot already handled it
    whatsapp_log('Skipping flow processing - old bot already responded');
} elseif ($this->ecommerceHandledMessage) {
    // Skip flow - ecommerce already handled it
    whatsapp_log('Skipping flow processing - ecommerce handled');
} else {
    // Only NOW process flows if nothing else responded
    $this->processBotFlow($message_data);
}
```

**Result**: Flows only execute when NO other bot system has responded

---

## 📊 NEW: Comprehensive Duplicate Tracking Log

**Log File**: `storage/logs/whatsappmessageduplicate.log`

Added detailed logging at 11 key stages to track EVERY step of message processing:

### Logging Stages:
1. **WEBHOOK_RECEIVED** - Webhook arrives from WhatsApp
2. **ATTEMPTING_LOCK** - Before cache lock
3. **LOCK_ACQUIRED** / **LOCK_FAILED** - Lock status
4. **MESSAGE_IS_NEW** / **DUPLICATE_DETECTED_IN_DB** - Duplicate check
5. **CHECK_ACTIVE_FLOWS** - Flow system availability
6. **FOUND_OLD_BOTS** - Old bots matching trigger
7. **SENDING_TEMPLATE_BOT** / **SENDING_MESSAGE_BOT** - Bot execution
8. **TEMPLATE_BOT_SENT** / **MESSAGE_BOT_SENT** - Response sent
9. **FLOW_PROCESSING_DECISION** - Decision logic
10. **SKIPPING_FLOW_OLD_BOT** / **SKIPPING_FLOW_ECOMMERCE** / **PROCESSING_FLOW** - Action taken
11. **FLOW_PROCESSING_COMPLETE** - Flow finished

### What Gets Logged:
- Request ID (unique per webhook)
- Message ID (from WhatsApp)
- Sender phone number
- Message text
- Which bots matched (IDs and counts)
- Which bot/system responded
- Why other systems were skipped
- Duplicate prevention flags

### View Logs:
```bash
# View recent tracking
tail -100 storage/logs/whatsappmessageduplicate.log

# Check for prevented duplicates
grep "prevented_duplicate" storage/logs/whatsappmessageduplicate.log

# Find duplicates (should be empty)
grep -A 5 "MESSAGE_BOT_SENT" storage/logs/whatsappmessageduplicate.log | grep "PROCESSING_FLOW"
```

**Full Documentation**: See `DUPLICATE_MESSAGE_TRACKING_LOG.md`

---

**Status**: ✅ Fix Applied + Comprehensive Logging Added - Ready for Testing
