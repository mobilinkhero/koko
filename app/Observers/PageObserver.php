<?php

namespace App\Observers;

use App\Facades\AdminCache;
use App\Models\Page;

class PageObserver
{
    public function saved(Page $page)
    {
        $this->clearCache();
    }

    public function deleting(Page $page)
    {
        // When deleting a page, update its children to have no parent
        // This prevents orphaning and maintains data integrity
        Page::where('parent_id', $page->id)->update(['parent_id' => null]);
    }

    public function deleted(Page $page)
    {
        $this->clearCache();
    }

    private function clearCache()
    {
        // Clear navigation and page related caches
        AdminCache::invalidateTags(['admin.navigation', 'model.page', 'frontend.menu']);
    }
}
