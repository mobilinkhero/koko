<?php

namespace App\Livewire\Tenant\Ecommerce;

use App\Models\Tenant\Product;
use App\Models\Tenant\EcommerceConfiguration;
use App\Services\GoogleSheetsService;
use App\Services\DynamicTenantTableService;
use App\Services\FeatureService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = 'all';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    
    public $showProductModal = false;
    public $editingProduct = null;
    public $productForm = [
        'name' => '',
        'description' => '',
        'price' => '',
        'sale_price' => '',
        'stock_quantity' => '',
        'category' => '',
        'subcategory' => '',
        'sku' => '',
        'status' => 'active',
        'featured' => false,
        'low_stock_threshold' => 5,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    protected $rules = [
        'productForm.name' => 'required|string|max:255',
        'productForm.description' => 'nullable|string',
        'productForm.price' => 'required|numeric|min:0',
        'productForm.sale_price' => 'nullable|numeric|min:0',
        'productForm.stock_quantity' => 'required|integer|min:0',
        'productForm.category' => 'nullable|string|max:100',
        'productForm.subcategory' => 'nullable|string|max:100',
        'productForm.sku' => 'nullable|string|max:100',
        'productForm.status' => 'required|in:active,inactive,draft',
        'productForm.featured' => 'boolean',
        'productForm.low_stock_threshold' => 'required|integer|min:0',
    ];

    public function mount(FeatureService $featureService)
    {
        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }
        if (!checkPermission('tenant.ecommerce.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        // Check if user has access to Ecommerce Bot feature
        if (!$featureService->hasAccess('ecommerce_bot')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note') . ' - Ecommerce Bot feature is not available in your plan.'], true);
            return redirect()->to(tenant_route('tenant.dashboard'));
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function createProduct()
    {
        $this->resetProductForm();
        $this->editingProduct = null;
        $this->showProductModal = true;
    }

    public function editProduct($productId)
    {
        $product = Product::where('tenant_id', tenant_id())->findOrFail($productId);
        $this->editingProduct = $product;
        $this->productForm = [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'stock_quantity' => $product->stock_quantity,
            'category' => $product->category,
            'subcategory' => $product->subcategory,
            'sku' => $product->sku,
            'status' => $product->status,
            'featured' => $product->featured,
            'low_stock_threshold' => $product->low_stock_threshold,
        ];
        $this->showProductModal = true;
    }

    public function saveProduct()
    {
        $this->validate();

        try {
            $productData = $this->productForm;
            $productData['tenant_id'] = tenant_id();
            
            if (!$productData['sku']) {
                $productData['sku'] = 'PRD-' . strtoupper(substr(md5(uniqid()), 0, 8));
            }

            if ($this->editingProduct) {
                $this->editingProduct->update($productData);
                $this->notify(['type' => 'success', 'message' => 'Product updated successfully']);
            } else {
                Product::create($productData);
                $this->notify(['type' => 'success', 'message' => 'Product created successfully']);
            }

            $this->showProductModal = false;
            $this->resetProductForm();
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error saving product: ' . $e->getMessage()]);
        }
    }

    public function deleteProduct($productId)
    {
        try {
            $product = Product::where('tenant_id', tenant_id())->findOrFail($productId);
            $product->delete();
            $this->notify(['type' => 'success', 'message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error deleting product: ' . $e->getMessage()]);
        }
    }

    public function toggleFeatured($productId)
    {
        try {
            $product = Product::where('tenant_id', tenant_id())->findOrFail($productId);
            $product->update(['featured' => !$product->featured]);
            $this->notify(['type' => 'success', 'message' => 'Product updated successfully']);
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error updating product: ' . $e->getMessage()]);
        }
    }

    public function adjustStock($productId, $adjustment)
    {
        try {
            $product = Product::where('tenant_id', tenant_id())->findOrFail($productId);
            $newStock = max(0, $product->stock_quantity + $adjustment);
            $product->update(['stock_quantity' => $newStock]);
            $this->notify(['type' => 'success', 'message' => 'Stock updated successfully']);
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Error updating stock: ' . $e->getMessage()]);
        }
    }

    public function syncProducts()
    {
        try {
            $sheetsService = new GoogleSheetsService();
            $result = $sheetsService->syncProductsFromSheets();
            
            if ($result['success']) {
                $this->notify(['type' => 'success', 'message' => $result['message']]);
            } else {
                $this->notify(['type' => 'danger', 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            $this->notify(['type' => 'danger', 'message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    public function resetProductForm()
    {
        $this->productForm = [
            'name' => '',
            'description' => '',
            'price' => '',
            'sale_price' => '',
            'stock_quantity' => '',
            'category' => '',
            'subcategory' => '',
            'sku' => '',
            'status' => 'active',
            'featured' => false,
            'low_stock_threshold' => 5,
        ];
    }

    public function closeModal()
    {
        $this->showProductModal = false;
        $this->resetProductForm();
        $this->editingProduct = null;
    }

    public function render()
    {
        $tenantId = tenant_id();
        $tableService = new DynamicTenantTableService();
        $tableName = $tableService->getTenantTableName($tenantId);
        
        // Check if table exists
        if (!Schema::hasTable($tableName)) {
            return view('livewire.tenant.ecommerce.product-manager', [
                'products' => collect(),
                'categories' => collect(),
                'stats' => ['total' => 0, 'active' => 0, 'low_stock' => 0, 'featured' => 0],
                'columns' => [],
                'tableName' => $tableName,
                'tableExists' => false,
            ]);
        }
        
        // Get all columns from the table
        $columns = Schema::getColumnListing($tableName);
        $columns = array_diff($columns, ['id', 'created_at', 'updated_at']); // Remove default columns
        
        // Build dynamic query
        $query = DB::table($tableName);
        
        // Apply search filter (search across all text columns)
        if ($this->search) {
            $query->where(function($q) use ($columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', '%' . $this->search . '%');
                }
            });
        }
        
        // Apply category filter if category column exists
        if ($this->categoryFilter && in_array('category', $columns)) {
            $query->where('category', $this->categoryFilter);
        }
        
        // Apply status filter if status column exists
        if ($this->statusFilter !== 'all' && in_array('status', $columns)) {
            $query->where('status', $this->statusFilter);
        }
        
        // Apply sorting if column exists
        if (in_array($this->sortBy, $columns)) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }
        
        // Paginate
        $perPage = 20;
        $page = request()->get('page', 1);
        $total = $query->count();
        $products = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        
        // Get categories for filter (if category column exists)
        $categories = collect();
        if (in_array('category', $columns)) {
            $categories = DB::table($tableName)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category');
        }
        
        // Get stats
        $stats = [
            'total' => DB::table($tableName)->count(),
            'active' => in_array('status', $columns) ? DB::table($tableName)->where('status', 'Active')->count() : 0,
            'low_stock' => 0,
            'featured' => 0,
        ];
        
        return view('livewire.tenant.ecommerce.product-manager', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats,
            'columns' => $columns,
            'tableName' => $tableName,
            'tableExists' => true,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
        ]);
    }
}
