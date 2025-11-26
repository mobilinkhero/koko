<?php

namespace App\Livewire;

use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $file;

    public $type; // default

    public $fileUrl;

    public $showTypeSelector = true;

    public $maxWidth; // Add this missing public property

    public $isUploading = false;

    protected $listeners = [
        'upload-started' => 'setUploading',
        'upload-finished' => 'setUploadingComplete',
    ];

    public function mount($type = null, $maxWidth = '2xl')
    {
        if ($type) {
            $this->type = $type;
            $this->showTypeSelector = false;
        } else {
            $this->type = 'image';
        }
        $this->maxWidth = $maxWidth;
    }

    public function setUploading()
    {
        $this->isUploading = true;
    }

    public function setUploadingComplete()
    {
        $this->isUploading = false;
    }

    public function removeFile()
    {
        if ($this->fileUrl) {

            // Get the file record from the database based on the URL
            $uploadedFile = UploadedFile::where('url', $this->fileUrl)->first();

            if ($uploadedFile) {
                // Delete the file from storage
                Storage::disk('public')->delete($uploadedFile->path);

                // Delete the record from the database
                $uploadedFile->delete();
            }

            // Reset component properties
            $this->reset('file', 'fileUrl');

            // Notify the user
            session()->flash('message', t('file_removed_successfully'));
        }
    }

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|file|max:51200', // 50MB
        ]);

        $path = $this->file->store('uploads', 'public');
        $this->fileUrl = Storage::disk('public')->url($path);

        UploadedFile::create([
            'type' => $this->type,
            'path' => $path,
            'url' => $this->fileUrl,
        ]);
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
