<?php

namespace App\View\Components;

use App\Models\Page;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class FrontendMenu extends Component
{
    public $menuItems;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->menuItems = Cache::remember('menu_items', 3600, function () {
            return Page::select(['id', 'title', 'slug', 'parent_id', 'order'])
                ->whereNull('parent_id')
                ->where('show_in_menu', true)
                ->where('status', true)
                ->orderBy('order')
                ->with(['children' => function ($query) {
                    $query->select(['id', 'title', 'slug', 'parent_id', 'order'])
                        ->where('show_in_menu', true)
                        ->where('status', true)
                        ->orderBy('order');
                }])
                ->get();
        });
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.frontend-menu');
    }
}
