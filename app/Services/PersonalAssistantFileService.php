<?php

namespace App\Services;

use App\Models\PersonalAssistant;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;

class PersonalAssistantFileService
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const MAX_CONTENT_LENGTH = 50000; // Max characters per file
    
    private const ALLOWED_EXTENSIONS = [
        'txt', 'md', 'csv', 'json', 
        'pdf', 'doc', 'docx'  // Future support
    ];

    /**
     * Upload and process files for personal assistant
     */
    public function uploadFiles(PersonalAssistant $assistant, array $files): array
    {
        $processedFiles = [];
        $totalContent = '';

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $result = $this->processFile($assistant, $file);
            if ($result['success']) {
                $processedFiles[] = $result['file_info'];
                $totalContent .= "\n\n=== FILE: {$result['file_info']['original_name']} ===\n";
                $totalContent .= $result['content'];
                $totalContent .= "\n=== END FILE ===\n";
            }
        }

        // Update assistant with new files and content
        $existingFiles = $assistant->uploaded_files ?? [];
        $allFiles = array_merge($existingFiles, $processedFiles);
        
        $existingContent = $assistant->processed_content ?? '';
        $allContent = $existingContent . $totalContent;

        // Truncate if too long
        if (strlen($allContent) > self::MAX_CONTENT_LENGTH * 5) {
            $allContent = substr($allContent, -self::MAX_CONTENT_LENGTH * 5);
        }

        $assistant->update([
            'uploaded_files' => $allFiles,
            'processed_content' => $allContent,
        ]);

        return [
            'success' => true,
            'files_processed' => count($processedFiles),
            'total_files' => count($allFiles),
            'content_size' => strlen($allContent),
        ];
    }

    /**
     * Process individual file
     */
    private function processFile(PersonalAssistant $assistant, UploadedFile $file): array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error'],
                ];
            }

            // Generate file path
            $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $filePath = "tenant-files/{$assistant->tenant_id}/{$fileName}";

            // Store file
            $stored = Storage::put($filePath, $file->getContent());
            if (!$stored) {
                return [
                    'success' => false,
                    'error' => 'Failed to store file',
                ];
            }

            // Extract content
            $content = $this->extractFileContent($file);
            if (!$content) {
                Storage::delete($filePath);
                return [
                    'success' => false,
                    'error' => 'Failed to extract file content',
                ];
            }

            // Prepare file info
            $fileInfo = [
                'original_name' => $file->getClientOriginalName(),
                'path' => $filePath,
                'filename' => $fileName,
                'size' => $file->getSize(),
                'type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toISOString(),
                'exists' => true,
            ];

            return [
                'success' => true,
                'file_info' => $fileInfo,
                'content' => $content,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): array
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => 'File size exceeds 5MB limit',
            ];
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return [
                'valid' => false,
                'error' => 'File type not supported. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS),
            ];
        }

        return ['valid' => true];
    }

    /**
     * Extract content from different file types
     */
    private function extractFileContent(UploadedFile $file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        switch ($extension) {
            case 'txt':
            case 'md':
                return $this->extractTextContent($file);
            
            case 'csv':
                return $this->extractCsvContent($file);
            
            case 'json':
                return $this->extractJsonContent($file);
            
            default:
                return null;
        }
    }

    /**
     * Extract text content
     */
    private function extractTextContent(UploadedFile $file): string
    {
        $content = $file->getContent();
        
        // Basic cleanup
        $content = preg_replace('/\r\n|\r|\n/', "\n", $content);
        $content = trim($content);
        
        // Truncate if too long
        if (strlen($content) > self::MAX_CONTENT_LENGTH) {
            $content = substr($content, 0, self::MAX_CONTENT_LENGTH) . "\n[Content truncated...]";
        }
        
        return $content;
    }

    /**
     * Extract CSV content and format for AI
     */
    private function extractCsvContent(UploadedFile $file): string
    {
        try {
            $csv = Reader::createFromString($file->getContent());
            $csv->setHeaderOffset(0);
            
            $headers = $csv->getHeader();
            $records = iterator_to_array($csv->getRecords());
            
            $content = "CSV Data Structure:\n";
            $content .= "Headers: " . implode(", ", $headers) . "\n\n";
            
            // Limit records to prevent overflow
            $maxRecords = 100;
            $recordCount = min(count($records), $maxRecords);
            
            $content .= "Sample Data (showing $recordCount of " . count($records) . " records):\n";
            
            foreach (array_slice($records, 0, $maxRecords) as $index => $record) {
                $content .= "Row " . ($index + 1) . ":\n";
                foreach ($record as $header => $value) {
                    $content .= "  $header: $value\n";
                }
                $content .= "\n";
            }
            
            if (count($records) > $maxRecords) {
                $content .= "[Additional " . (count($records) - $maxRecords) . " records available in full dataset]\n";
            }
            
            return $content;
            
        } catch (\Exception $e) {
            return "CSV parsing error: " . $e->getMessage();
        }
    }

    /**
     * Extract JSON content and format for AI
     */
    private function extractJsonContent(UploadedFile $file): string
    {
        try {
            $jsonData = json_decode($file->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return "JSON parsing error: " . json_last_error_msg();
            }
            
            // Format JSON for better AI understanding
            $content = "JSON Data Structure:\n";
            $content .= $this->formatJsonForAI($jsonData, 0, 3); // Max 3 levels deep
            
            return $content;
            
        } catch (\Exception $e) {
            return "JSON processing error: " . $e->getMessage();
        }
    }

    /**
     * Format JSON data for AI understanding
     */
    private function formatJsonForAI($data, $level = 0, $maxLevel = 3): string
    {
        if ($level > $maxLevel) {
            return "[Deep nested structure...]\n";
        }
        
        $indent = str_repeat("  ", $level);
        $content = "";
        
        if (is_array($data)) {
            if (array_is_list($data)) {
                $content .= $indent . "Array with " . count($data) . " items:\n";
                foreach (array_slice($data, 0, 5) as $index => $item) {
                    $content .= $indent . "  [$index]: ";
                    if (is_scalar($item)) {
                        $content .= $item . "\n";
                    } else {
                        $content .= "\n" . $this->formatJsonForAI($item, $level + 2, $maxLevel);
                    }
                }
                if (count($data) > 5) {
                    $content .= $indent . "  [... " . (count($data) - 5) . " more items]\n";
                }
            } else {
                foreach ($data as $key => $value) {
                    $content .= $indent . "$key: ";
                    if (is_scalar($value)) {
                        $content .= $value . "\n";
                    } else {
                        $content .= "\n" . $this->formatJsonForAI($value, $level + 1, $maxLevel);
                    }
                }
            }
        } else {
            $content .= $indent . $data . "\n";
        }
        
        return $content;
    }

    /**
     * Remove file from assistant
     */
    public function removeFile(PersonalAssistant $assistant, string $fileName): bool
    {
        $files = $assistant->uploaded_files ?? [];
        $updatedFiles = collect($files)->reject(function ($file) use ($fileName) {
            return $file['original_name'] === $fileName || $file['path'] === $fileName;
        })->values()->toArray();

        // Delete physical file
        $filePath = "tenant-files/{$assistant->tenant_id}/$fileName";
        Storage::delete($filePath);

        // Reprocess all remaining files to rebuild content
        $this->rebuildProcessedContent($assistant, $updatedFiles);

        return true;
    }

    /**
     * Rebuild processed content from remaining files
     */
    private function rebuildProcessedContent(PersonalAssistant $assistant, array $files): void
    {
        $content = '';
        
        foreach ($files as $fileInfo) {
            $filePath = "tenant-files/{$assistant->tenant_id}/{$fileInfo['path']}";
            if (Storage::exists($filePath)) {
                $fileContent = Storage::get($filePath);
                
                // Re-extract content based on file type
                $extractedContent = $this->extractContentByType($fileContent, $fileInfo['type']);
                
                if ($extractedContent) {
                    $content .= "\n\n=== FILE: {$fileInfo['original_name']} ===\n";
                    $content .= $extractedContent;
                    $content .= "\n=== END FILE ===\n";
                }
            }
        }

        $assistant->update([
            'uploaded_files' => $files,
            'processed_content' => $content,
        ]);
    }

    /**
     * Extract content by file type from stored file
     */
    private function extractContentByType(string $fileContent, string $type): ?string
    {
        switch (strtolower($type)) {
            case 'txt':
            case 'md':
                return trim($fileContent);
            
            case 'csv':
                try {
                    $csv = Reader::createFromString($fileContent);
                    $csv->setHeaderOffset(0);
                    
                    $headers = $csv->getHeader();
                    $records = iterator_to_array($csv->getRecords());
                    
                    $content = "CSV Headers: " . implode(", ", $headers) . "\n";
                    foreach (array_slice($records, 0, 50) as $record) {
                        $content .= implode(" | ", $record) . "\n";
                    }
                    
                    return $content;
                } catch (\Exception $e) {
                    return "CSV Error: " . $e->getMessage();
                }
            
            case 'json':
                return $fileContent;
            
            default:
                return null;
        }
    }

    /**
     * Clear all files for assistant
     */
    public function clearAllFiles(PersonalAssistant $assistant): bool
    {
        $files = $assistant->uploaded_files ?? [];
        
        // Delete all physical files
        foreach ($files as $file) {
            $filePath = "tenant-files/{$assistant->tenant_id}/{$file['path']}";
            Storage::delete($filePath);
        }

        // Clear assistant data
        $assistant->update([
            'uploaded_files' => [],
            'processed_content' => null,
        ]);

        return true;
    }
}
