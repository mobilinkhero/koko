<?php

namespace App\Livewire\Admin\Settings\Website;

use App\Rules\PurifiedInput;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class TestimonialSettings extends Component
{
    use WithFileUploads;

    public $testimonials = [];

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $settings = get_batch_settings(['theme.testimonials']);
        $this->testimonials = json_decode($settings['theme.testimonials'], true) ?? [];
    }

    public function messages()
    {
        return [
            'testimonials.*.name.required' => 'The name field is required.',
            'testimonials.*.name.string' => 'The name must be a valid string.',
            'testimonials.*.name.max' => 'The name must not exceed 255 characters.',

            'testimonials.*.position.required' => 'The position field is required.',
            'testimonials.*.position.string' => 'The position must be a valid string.',
            'testimonials.*.position.max' => 'The position must not exceed 255 characters.',

            'testimonials.*.company.required' => 'The company field is required.',
            'testimonials.*.company.string' => 'The company must be a valid string.',
            'testimonials.*.company.max' => 'The company name must not exceed 255 characters.',

            'testimonials.*.testimonial.required' => 'The testimonial field is required.',
            'testimonials.*.testimonial.string' => 'The testimonial must be a valid string.',

            'testimonials.*.profile_image.max' => 'The profile image size must not exceed 1MB.',
        ];
    }

    public function addTestimonial()
    {
        $this->testimonials[] = [
            'name' => '',
            'position' => '',
            'company' => '',
            'testimonial' => '',
            'profile_image' => null,
        ];
    }

    public function removeTestimonial($index)
    {

        try {
            // Check if the index exists in the testimonials array
            if (isset($this->testimonials[$index])) {
                // Check if the file exists and delete it
                if (file_exists(public_path('storage/'.$this->testimonials[$index]['profile_image']))) {
                    @unlink(public_path('storage/'.$this->testimonials[$index]['profile_image']));
                }

                // Remove the testimonial from the array
                unset($this->testimonials[$index]);

                // Reindex the array to maintain proper indices
                $this->testimonials = array_values($this->testimonials);

                // Update settings with the modified testimonials array
                set_setting('theme.testimonials', json_encode($this->testimonials));
            } else {
                throw new \Exception("Testimonial at index {$index} does not exist.");
            }
        } catch (\Exception $e) {
            // Handle the exception (optional: log the error or show a notification)
            $this->notify([
                'type' => 'error',
                'message' => t('error_occurred_remove_testimonial').$e->getMessage(),
            ]);
        }
    }

    public function removeTestimonialFile(string $testimonial_image)
    {
        if (checkPermission('admin.website_settings.edit')) {
            // Extract index from "testimonials.0.profile_image"
            preg_match('/testimonials\.(\d+)\.profile_image/', $testimonial_image, $matches);

            if (! isset($matches[1])) {
                $this->notify([
                    'type' => 'error',
                    'message' => t('invalid_testimonial_image'),
                ]);

                return;
            }

            $index = (int) $matches[1]; // Convert index to integer

            if (! isset($this->testimonials[$index])) {
                $this->notify([
                    'type' => 'error',
                    'message' => t('testimonial_not_found'),
                ]);

                return;
            }

            // Get the image path
            $imagePath = $this->testimonials[$index]['profile_image'];

            // Delete file if it exists in storage
            if ($imagePath && file_exists(public_path('storage/'.$imagePath))) {
                @unlink(public_path('storage/'.$imagePath));
            }

            // Set profile_image to null
            $this->testimonials[$index]['profile_image'] = null;

            // Save updated testimonials
            set_setting('theme.testimonials', json_encode($this->testimonials));

            // Send success notification
            $this->notify([
                'type' => 'success',
                'message' => t('profile_image_removed_successfully'),
            ]);
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.testimonials.settings.view'));
        }
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $rules = [
                'testimonials' => ['array'],
                'testimonials.*.name' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'testimonials.*.position' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'testimonials.*.company' => ['required', 'string', 'max:255', new PurifiedInput(t('sql_injection_error'))],
                'testimonials.*.testimonial' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
            ];

            foreach ($this->testimonials as $index => $testimonial) {
                if (isset($testimonial['profile_image']) && $testimonial['profile_image'] instanceof TemporaryUploadedFile) {
                    $rules["testimonials.{$index}.profile_image"] = ['nullable', 'image', 'mimes:png,jpg,jpeg'];
                }
            }

            $this->validate($rules);

            $settings = get_batch_settings(['theme.testimonials']);
            $originalTestimonials = json_decode($settings['theme.testimonials'], true) ?? [];

            // Handle file uploads for each testimonial
            foreach ($this->testimonials as $index => $testimonial) {
                if ($testimonial['profile_image'] instanceof TemporaryUploadedFile) {
                    // If profile_image is a file, handle the upload
                    $this->testimonials[$index]['profile_image'] = $this->handleFileUpload($testimonial['profile_image'], 'testimonial_'.$index);
                } elseif (empty($testimonial['profile_image']) || is_array($testimonial['profile_image'])) {
                    // If profile_image is an empty array or empty, set it to null
                    $this->testimonials[$index]['profile_image'] = null;
                } elseif (is_string($testimonial['profile_image']) && ! empty($testimonial['profile_image'])) {
                    // If it's a string (existing image path or URL), leave it as is
                    continue;
                }
            }

            if ($this->testimonials !== $originalTestimonials) {
                set_setting('theme.testimonials', json_encode($this->testimonials));

                $this->dispatch('setting-saved');

                $this->notify([
                    'type' => 'success',
                    'message' => t('testimonials_update_successfully'),
                ]);
            }
        }
    }

    protected function handleFileUpload($file, $type)
    {
        create_storage_link();

        $filename = $type.'_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('testimonials', $filename, 'public');

        return $path;
    }

    public function render()
    {
        return view('livewire.admin.settings.website.testimonial-settings');
    }
}
