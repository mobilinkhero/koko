# Personal AI Assistant Implementation Guide

## 🎯 Overview

This implementation adds a **Personal AI Assistant Management System** to WhatsMark, allowing each tenant to have one customizable AI assistant with advanced document processing capabilities.

## ✨ Key Features

### 📚 **Use Cases Supported**
- **FAQs Automation**: Upload FAQ documents for automatic responses
- **Product Enquiries**: Train on brochures, descriptions, pricing sheets
- **Onboarding & Setup Help**: Use step-by-step manuals as learning material
- **CSV Lookups**: AI can search through structured data tables
- **Internal SOPs or Team Guides**: Upload HR policies, process docs

### 🔧 **Technical Features**
- **One Assistant Per Tenant**: Simplified management model
- **File Processing**: TXT, MD, CSV, JSON support (5MB max per file)
- **AI Model Selection**: GPT-3.5, GPT-4, GPT-4o Mini options
- **Temperature Control**: Adjustable creativity/focus settings
- **Knowledge Base**: Automatic content processing and context building
- **Flow Integration**: Works with existing bot flow system

## 📁 Files Created

### Backend Files
```
app/Models/PersonalAssistant.php
app/Services/PersonalAssistantFileService.php
app/Livewire/Tenant/AI/PersonalAssistantManager.php
database/migrations/2024_11_22_012400_create_personal_assistants_table.php
database/seeders/PersonalAssistantMenuSeeder.php
```

### Frontend Files
```
resources/views/livewire/tenant/ai/personal-assistant-manager.blade.php
```

### Enhanced Files
```
app/Traits/Ai.php (enhanced with personal assistant methods)
resources/js/components/nodes/AIAssistantNode.vue (updated for integration)
routes/tenant/tenant.php (added route)
```

## 🚀 Installation Steps

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Add to Navigation Menu

Add this entry to your tenant navigation system:
```php
// Menu Configuration
[
    'title' => 'AI Assistant',
    'route' => 'tenant.ai-assistant',
    'icon' => 'cpu-chip', // or 'lightbulb'
    'parent' => 'Marketing', // or create 'AI Tools' section
    'permission' => null, // Available to all tenant users
]
```

### 3. Create Storage Directory
```bash
mkdir -p storage/app/tenant-files
chmod 755 storage/app/tenant-files
```

### 4. Update Environment (if needed)
```env
# Ensure OpenAI integration is configured
OPENAI_API_KEY=your_key_here
```

## 🎨 Frontend Integration

### Navigation Menu Location
The AI Assistant should be added to the tenant navigation. Suggested locations:

1. **Under Marketing Section** (alongside existing AI features)
2. **New "AI Tools" Section** (if you want to group AI features)
3. **Under Settings** (if treated as configuration)

### Route Structure
```
/{subdomain}/ai-assistant
```

### Integration with Existing Features
- The enhanced `AI Assistant Node` in flow builder now supports:
  - **Personal Assistant Mode**: Uses the configured personal assistant
  - **Custom Mode**: Traditional custom AI settings

## 💾 Database Schema

### personal_assistants Table
```sql
id                  - Primary key
tenant_id          - Foreign key (unique constraint)
name               - Assistant name
description        - Optional description
system_instructions - AI behavior instructions
model              - AI model (gpt-4o-mini, gpt-4, etc.)
temperature        - Creativity setting (0-2)
max_tokens         - Response length limit
file_analysis_enabled - Enable/disable file processing
uploaded_files     - JSON array of file metadata
processed_content  - Combined processed file content
is_active          - Enable/disable assistant
use_case_tags      - JSON array of use case categories
created_at/updated_at - Timestamps
```

## 🔧 API Methods

### Enhanced AI Trait Methods
```php
// Send message to personal assistant with conversation context
personalAssistantResponse(string $message, array $conversationHistory = []): array

// Get personal assistant information
getPersonalAssistantInfo(): ?array

// Check if personal assistant is available
hasPersonalAssistant(): bool
```

### File Service Methods
```php
// Upload and process files
uploadFiles(PersonalAssistant $assistant, array $files): array

// Remove specific file
removeFile(PersonalAssistant $assistant, string $fileName): bool

// Clear all files
clearAllFiles(PersonalAssistant $assistant): bool
```

## 📋 Usage Examples

### 1. Basic Assistant Setup
```php
// Create assistant for current tenant
$assistant = PersonalAssistant::createOrUpdateForTenant([
    'name' => 'Customer Support AI',
    'description' => 'Helps with product questions and support',
    'system_instructions' => 'You are a helpful customer service assistant...',
    'model' => 'gpt-4o-mini',
    'use_case_tags' => ['faq', 'product'],
]);
```

### 2. File Upload Processing
```php
$fileService = new PersonalAssistantFileService();
$result = $fileService->uploadFiles($assistant, $uploadedFiles);

if ($result['success']) {
    echo "Processed {$result['files_processed']} files";
}
```

### 3. AI Response with Context
```php
use App\Traits\Ai;

class MyController {
    use Ai;
    
    public function getChatResponse($message) {
        $response = $this->personalAssistantResponse($message, $conversationHistory);
        
        if ($response['status']) {
            return $response['message'];
        }
    }
}
```

## 🛡️ Security & Validation

### File Upload Restrictions
- **Max File Size**: 5MB per file
- **Allowed Types**: TXT, MD, CSV, JSON
- **Content Processing**: Automatic sanitization and truncation
- **Storage**: Tenant-isolated directories

### Access Control
- **Tenant Isolation**: Each tenant can only access their own assistant
- **User Permissions**: Standard tenant user permissions apply
- **API Security**: All operations require valid tenant context

## 🔄 Integration Points

### Bot Flow Integration
The AI Assistant node in the flow builder now supports:
```javascript
// Node configuration
{
    assistantMode: 'personal', // or 'custom'
    // ... other settings
}
```

### Chat Integration
Can be integrated with the existing chat system:
```php
// In chat controller
if ($this->hasPersonalAssistant()) {
    $response = $this->personalAssistantResponse($userMessage, $chatHistory);
}
```

## 📊 File Processing Capabilities

### CSV Processing
- Automatically detects headers and structure
- Formats data for AI understanding
- Limits preview to prevent token overflow
- Supports data lookups and queries

### Text Processing
- Handles TXT and Markdown files
- Preserves formatting where relevant
- Automatic content truncation for large files
- Character encoding detection

### JSON Processing
- Recursive structure analysis
- Formatted for AI comprehension
- Handles nested objects and arrays
- Size optimization for large datasets

## 🚀 Future Enhancements

### Potential Additions
1. **PDF Support**: Add PDF text extraction
2. **Image Analysis**: OCR and image description
3. **Multiple Assistants**: Allow multiple assistants per tenant
4. **Advanced Analytics**: Usage tracking and performance metrics
5. **API Webhooks**: External system integration
6. **Voice Processing**: Audio file transcription and analysis

### Performance Optimizations
1. **Chunked Processing**: Handle very large documents
2. **Caching**: Cache processed content for faster responses
3. **Background Processing**: Queue file processing for large uploads
4. **CDN Integration**: Optimize file storage and delivery

## 🔍 Troubleshooting

### Common Issues

**1. Files Not Processing**
- Check file permissions on `storage/app/tenant-files`
- Verify file format is supported
- Check file size limits

**2. AI Responses Not Working**
- Verify OpenAI API key is configured
- Check tenant has active assistant
- Ensure assistant is enabled (`is_active = true`)

**3. Navigation Menu Missing**
- Manually add route to navigation configuration
- Check route permissions and middleware
- Verify tenant context is properly set

### Debug Commands
```bash
# Check migration status
php artisan migrate:status

# Clear cache if needed
php artisan cache:clear
php artisan route:clear

# Check file permissions
ls -la storage/app/tenant-files/
```

## 📞 Support

This implementation follows WhatsMark's existing patterns:
- **Multi-tenancy**: Spatie multitenancy integration
- **Livewire**: Component-based UI
- **File Processing**: Laravel storage system
- **AI Integration**: Existing LLPhant infrastructure

For questions or issues, refer to the existing codebase patterns or contact the development team.

---

**Implementation Status**: ✅ Complete and Ready for Use

**Next Steps**:
1. Run migration
2. Add to navigation menu
3. Test with sample files
4. Configure use cases as needed
