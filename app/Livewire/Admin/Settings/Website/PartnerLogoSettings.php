<?php

namespace App\Livewire\Admin\Settings\Website;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class PartnerLogoSettings extends Component
{
    use WithFileUploads;

    public $logoItems = [];

    public $tempImages = [];

    public $removedLogos = [];

    protected $rules = [
        'logoItems.*.image' => 'nullable|image|max:1024|dimensions:max_width=800,max_height=200',
    ];

    protected $messages = [
        'logoItems.*.image.image' => 'The file must be an image.',
        'logoItems.*.image.max' => 'The image must not be larger than 1MB.',
        'logoItems.*.image.dimensions' => 'The image dimensions should not exceed 800x200 pixels.',
    ];

    public function mount()
    {
        if (! checkPermission('admin.website_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $this->loadExistingLogos();

        if (empty($this->logoItems)) {
            $this->addItem();
        }
    }

    protected function loadExistingLogos()
    {
        $settings = get_batch_settings(['theme.partner_logos']);
        $existingLogos = $settings['theme.partner_logos'] ?? null;

        if ($existingLogos) {
            $logosArray = json_decode($existingLogos, true);

            if (is_array($logosArray)) {
                $this->logoItems = array_map(function ($logo) {
                    return [
                        'path' => $logo['path'] ?? null,
                        'image' => null,
                        'existing' => true,
                        'marked_for_removal' => false,
                    ];
                }, $logosArray);
            }
        }
    }

    public function addItem()
    {
        $this->logoItems[] = [
            'image' => null,
            'path' => null,
            'existing' => false,
            'marked_for_removal' => false,
        ];
    }

    public function removeItem($index)
    {
        if (checkPermission('admin.website_settings.edit')) {

            if (isset($this->logoItems[$index]['existing']) && $this->logoItems[$index]['existing']) {
                // Just mark for removal but keep in the array until save
                $this->logoItems[$index]['marked_for_removal'] = true;

                // Add to removed logos list for tracking
                if (isset($this->logoItems[$index]['path'])) {
                    $this->removedLogos[] = $this->logoItems[$index]['path'];
                }

                return;
            }

            // Remove the temporary image if it exists
            if (isset($this->tempImages[$index])) {
                unset($this->tempImages[$index]);
            }

            // Remove the item from logoItems
            unset($this->logoItems[$index]);
            $this->logoItems = array_values($this->logoItems);
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.partner-logo.settings.view'));
        }
    }

    public function restoreItem($index)
    {
        if (isset($this->logoItems[$index])) {
            $this->logoItems[$index]['marked_for_removal'] = false;

            // Remove from removal list if it was there
            $path = $this->logoItems[$index]['path'] ?? null;
            if ($path) {
                $this->removedLogos = array_filter($this->removedLogos, function ($item) use ($path) {
                    return $item !== $path;
                });
            }
        }
    }

    public function updatedLogoItems($value, $key)
    {
        // When an image is uploaded
        if (strpos($key, 'image') !== false) {
            $index = explode('.', $key)[0];
            $this->validateOnly($key);

            // Generate a preview URL for the frontend
            if ($value && ! isset($this->tempImages[$index])) {
                $this->tempImages[$index] = true;
            }
        }
    }

    public function removeImage($index)
    {
        if (isset($this->logoItems[$index]['existing']) && $this->logoItems[$index]['existing']) {
            $this->logoItems[$index]['marked_for_removal'] = true;

            return;
        }

        $this->logoItems[$index]['image'] = null;

        if (isset($this->tempImages[$index])) {
            unset($this->tempImages[$index]);
        }
    }

    public function save()
    {
        if (checkPermission('admin.website_settings.edit')) {
            $this->validate();

            $savedItems = [];
            $deletedPaths = [];

            $settings = get_batch_settings(['theme.partner_logos']);
            $originalLogos = $settings['theme.partner_logos'] ?? null;
            $originalLogosDecoded = $originalLogos ? json_decode($originalLogos, true) : [];

            $changesDetected = false;

            $filteredItems = array_filter($this->logoItems, function ($item) {
                return ! ($item['marked_for_removal'] ?? false);
            });

            foreach ($filteredItems as $index => $item) {

                if (! empty($item['existing']) && ! $item['image']) {
                    $savedItems[] = [
                        'path' => $item['path'],
                    ];

                    continue;
                }

                if ($item['image']) {
                    $filename = 'partner_logo_'.time().'_'.Str::random(8).'.'.$item['image']->getClientOriginalExtension();
                    $path = $item['image']->storeAs('partner-logos', $filename, 'public');

                    if (! empty($item['existing']) && ! empty($item['path'])) {
                        $deletedPaths[] = $item['path'];
                    }

                    $savedItems[] = [
                        'path' => $path,
                    ];

                    $changesDetected = true;
                }
            }

            if (json_encode($savedItems) !== json_encode($originalLogosDecoded)) {
                $changesDetected = true;
                set_setting('theme.partner_logos', json_encode($savedItems));
            }

            if (! empty($deletedPaths) || ! empty($this->removedLogos)) {
                $changesDetected = true;
            }

            foreach (array_merge($deletedPaths, $this->removedLogos) as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $this->reset(['logoItems', 'tempImages', 'removedLogos']);
            $this->loadExistingLogos();

            if (empty($this->logoItems)) {
                $this->addItem();
            }

            if ($changesDetected) {
                $this->notify([
                    'type' => 'success',
                    'message' => t('partner_logo_saved_successfully'),
                ], true);

                return to_route('admin.partner-logo.settings.view');
            }
        }
    }

    public function render()
    {
        // Count active (non-removed) items for UI logic
        $activeItemsCount = count(array_filter($this->logoItems, function ($item) {
            return ! ($item['marked_for_removal'] ?? false);
        }));

        return view('livewire.admin.settings.website.partner-logo-settings', [
            'activeItemsCount' => $activeItemsCount,
        ]);
    }
}
