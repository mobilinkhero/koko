<?php

namespace App\Livewire\Admin\Page;

use App\Models\Page;
use App\Rules\PurifiedInput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use Stevebauman\Purify\Facades\Purify;

class ManagePages extends Component
{
    public Page $page;

    public $showPageModal = false;

    public $search = '';

    public $title = '';

    public $slug = '';

    public $parent_id;

    public $description = '';

    public $show_in_menu = false;

    public $status = true;

    public $order = 0;

    public $page_id = null;

    public $confirmingDeletion = false;

    protected $parentPagesCache = null;

    protected $listeners = [
        'editPage' => 'editPage',
        'confirmDelete' => 'confirmDelete',
    ];

    protected function rules()
    {
        return [
            'page.title' => ['required', 'min:3', 'max:255', new PurifiedInput(t('sql_injection_error'))],
            'page.slug' => ['required', 'unique:pages,slug,'.($this->page->id ?? 'NULL'), new PurifiedInput(t('sql_injection_error'))],
            'page.description' => ['nullable'],
            'page.parent_id' => [
                'nullable',
                'integer',
                'exists:pages,id',
                function ($attribute, $value, $fail) {
                    // Prevent setting self as parent
                    if ($value && $this->page->id && $value == $this->page->id) {
                        $fail('A page cannot be its own parent.');
                    }

                    // Prevent circular relationships
                    if ($value && $this->page->id && $this->wouldCreateCircularRelation($value)) {
                        $fail('Circular parent-child relationship not allowed.');
                    }
                },
            ],
            'page.order' => ['nullable', 'numeric'],
            'page.show_in_menu' => ['nullable', 'boolean'],
            'page.status' => ['nullable', 'boolean'],
        ];
    }

    public function mount()
    {
        if (! checkPermission('admin.pages.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->resetForm();
        $this->page = new Page;
    }

    public function createPage()
    {
        $this->resetForm();
        $this->refreshParentPages(); // Refresh parent pages when creating
        $this->showPageModal = true;
    }

    private function resetForm()
    {
        $this->resetExcept('page');
        $this->resetValidation();
        $this->page = new Page;
        // Set default values
        $this->page->status = true;
        $this->page->show_in_menu = false;
        $this->page->order = 0;
        $this->page->parent_id = null;

        // Reset deletion-related properties
        $this->confirmingDeletion = false;

        // Clear parent pages cache
        $this->parentPagesCache = null;
    }

    public function save()
    {
        if (checkPermission(['admin.pages.create', 'admin.pages.edit'])) {
            // Clean parent_id if empty string
            if ($this->page->parent_id === '') {
                $this->page->parent_id = null;
            }

            $this->validate();
            try {
                if ($this->page->isDirty()) {
                    $this->page->save();
                    $this->showPageModal = false;
                    $this->notify([
                        'type' => 'success',
                        'message' => $this->page->wasRecentlyCreated
                            ? t('page_created_successfully')
                            : t('page_updated_successfully'),
                    ]);
                    Cache::forget('menu_items');

                    // Refresh the parent pages data to include the newly created page
                    $this->refreshParentPages();

                    $this->dispatch('pg:eventRefresh-page-table');

                    // Force UI refresh by updating a reactive property
                    $this->dispatch('parentPagesUpdated');
                } else {
                    $this->showPageModal = false;
                }
            } catch (\Exception $e) {
                app_log('Page save failed: '.$e->getMessage(), 'error', $e, [
                    'page_id' => $this->page->id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('page_save_failed')]);
            }
        }
    }

    protected function sanitizeContent($content)
    {
        // First clean special Unicode characters
        $content = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $content);

        // Then apply HTML purifier
        $content = Purify::clean($content, [
            'HTML.Allowed' => 'a[href|title|target],p,br,b,i,u,strong,em,strike,blockquote,h1,h2,h3,h4,h5,h6,ol,ul,li,pre,code,div[class|style],span[class|style]',
        ]);

        // Remove empty paragraphs and multiple spaces
        $content = preg_replace('/<p[^>]*>(?:\s|&nbsp;)*<\/p>/', '', $content);
        $content = preg_replace('/\s+/', ' ', $content);

        return trim($content);
    }

    public function editPage($pageId)
    {
        $this->resetValidation();
        $page = Page::findOrFail($pageId);
        $this->page = $page;
        $this->resetValidation();
        $this->refreshParentPages(); // Refresh parent pages when editing
        Cache::forget('menu_items');

        $this->showPageModal = true;
    }

    public function confirmDelete($pageId)
    {
        $this->page_id = $pageId;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->page_id = null;

        // Reset any validation errors
        $this->resetValidation();
    }

    public function delete()
    {
        if (checkPermission('admin.pages.delete')) {
            try {
                $pageToDelete = Page::findOrFail($this->page_id);

                // Delete the page (children will be handled by PageObserver)
                $pageToDelete->delete();
                $this->confirmingDeletion = false;
                $this->page_id = null;

                Cache::forget('menu_items');

                // Refresh parent pages since the deleted page is no longer available
                $this->refreshParentPages();

                $this->notify(['type' => 'success', 'message' => t('page_deleted_successfully')]);
                $this->redirect(route('admin.pages'), navigate: true);
            } catch (\Exception $e) {
                app_log('Page deletion failed: '.$e->getMessage(), 'error', $e, [
                    'page_id' => $this->page_id ?? null,
                ]);

                $this->notify(['type' => 'danger', 'message' => t('page_delete_failed')]);
            }
        }
    }

    public function updatedPageTitle()
    {
        if ($this->page->title) {
            $this->page->slug = Str::slug($this->page->title);
        }
    }

    public function getParentPagesProperty()
    {
        if ($this->parentPagesCache === null) {
            $this->parentPagesCache = Page::where('id', '!=', $this->page->id ?? null)
                ->orderBy('title')
                ->get();
        }

        return $this->parentPagesCache;
    }

    /**
     * Refresh the parent pages cache
     */
    public function refreshParentPages()
    {
        $this->parentPagesCache = null;
    }

    /**
     * Get hash for parent pages to force re-render when data changes
     */
    public function getParentPagesHashProperty()
    {
        $pages = $this->parentPages;

        return md5($pages->pluck('id')->sort()->implode(',').'-'.($this->page->id ?? 'new'));
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-page-table');
    }

    /**
     * Check if setting a parent would create a circular relationship
     */
    private function wouldCreateCircularRelation($parentId)
    {
        if (! $this->page->id) {
            return false;
        }

        // Get all children of current page (recursively)
        $childrenIds = $this->getAllChildrenIds($this->page->id);

        // If the proposed parent is one of the children, it would create a circular relation
        return in_array($parentId, $childrenIds);
    }

    /**
     * Get all children IDs recursively
     */
    private function getAllChildrenIds($pageId, $visited = [])
    {
        if (in_array($pageId, $visited)) {
            return [];
        }

        $visited[] = $pageId;
        $childrenIds = [];

        $children = Page::where('parent_id', $pageId)->pluck('id');

        foreach ($children as $childId) {
            $childrenIds[] = $childId;
            $childrenIds = array_merge($childrenIds, $this->getAllChildrenIds($childId, $visited));
        }

        return $childrenIds;
    }

    public function render()
    {
        return view('livewire.admin.page.manage-pages');
    }
}
