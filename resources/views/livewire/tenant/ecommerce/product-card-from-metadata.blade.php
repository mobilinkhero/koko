{{-- Example: Display Product from Meta Data --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
    {{-- Product Image --}}
    @if(isset($product->meta_data['image_url']) && !empty($product->meta_data['image_url']))
        <img src="{{ $product->meta_data['image_url'] }}" 
             alt="{{ $product->name }}"
             class="w-full h-48 object-cover rounded-lg mb-4">
    @endif
    
    {{-- Product Name --}}
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ $product->name }}
    </h3>
    
    {{-- Product ID --}}
    @if(isset($product->meta_data['product_id']))
        <p class="text-sm text-gray-600 dark:text-gray-400">
            ID: {{ $product->meta_data['product_id'] }}
        </p>
    @endif
    
    {{-- Product Type --}}
    @if(isset($product->meta_data['product_type']))
        <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full mb-2">
            {{ $product->meta_data['product_type'] }}
        </span>
    @endif
    
    {{-- Colors --}}
    @if(isset($product->meta_data['colors']) && !empty($product->meta_data['colors']))
        <div class="mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Colors:</span>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ $product->meta_data['colors'] }}
            </span>
        </div>
    @endif
    
    {{-- Sizes --}}
    @if(isset($product->meta_data['sizes']) && !empty($product->meta_data['sizes']))
        <div class="mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sizes:</span>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ $product->meta_data['sizes'] }}
            </span>
        </div>
    @endif
    
    {{-- Pricing --}}
    <div class="flex items-center justify-between mb-3">
        @if(isset($product->meta_data['selling_price']))
            <div>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">
                    ${{ number_format($product->meta_data['selling_price'], 2) }}
                </span>
                
                @if(isset($product->meta_data['purchase_price']) && $product->meta_data['purchase_price'])
                    <span class="text-sm text-gray-500 line-through ml-2">
                        ${{ number_format($product->meta_data['purchase_price'], 2) }}
                    </span>
                @endif
            </div>
        @endif
        
        @if(isset($product->meta_data['price_cut_shown']) && $product->meta_data['price_cut_shown'] === 'TRUE')
            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">
                SALE
            </span>
        @endif
    </div>
    
    {{-- Stock Status --}}
    @if(isset($product->meta_data['quantity_type']))
        <div class="mb-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Stock:</span>
            <span class="text-sm 
                {{ $product->meta_data['quantity_type'] === 'In Stock' ? 'text-green-600' : 'text-red-600' }}">
                {{ $product->meta_data['quantity_type'] }}
            </span>
            @if(isset($product->meta_data['quantity_int']))
                <span class="text-sm text-gray-600">({{ $product->meta_data['quantity_int'] }} units)</span>
            @endif
        </div>
    @endif
    
    {{-- Creative Grade --}}
    @if(isset($product->meta_data['creative_grade']) && !empty($product->meta_data['creative_grade']))
        <div class="mb-2">
            <span class="inline-block px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">
                Grade: {{ $product->meta_data['creative_grade'] }}
            </span>
        </div>
    @endif
    
    {{-- Slider Group --}}
    @if(isset($product->meta_data['slider_group']) && !empty($product->meta_data['slider_group']))
        <div class="mb-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                📁 {{ $product->meta_data['slider_group'] }}
            </span>
        </div>
    @endif
    
    {{-- Tags --}}
    @if(!empty($product->tags))
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach($product->tags as $tag)
                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                    #{{ $tag }}
                </span>
            @endforeach
        </div>
    @endif
    
    {{-- Advance Amount --}}
    @if(isset($product->meta_data['advance_amount']) && $product->meta_data['advance_amount'] > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-3">
            <p class="text-sm text-yellow-800">
                💰 Advance: ${{ number_format($product->meta_data['advance_amount'], 2) }}
            </p>
        </div>
    @endif
    
    {{-- Expiry Info --}}
    @if(isset($product->meta_data['expiry_at_urgent']))
        <div class="bg-red-50 border border-red-200 rounded p-2 mb-3">
            <p class="text-xs text-red-800">
                ⏰ Urgent Expiry: {{ $product->meta_data['expiry_at_urgent'] }}
            </p>
        </div>
    @endif
    
    {{-- Actions --}}
    <div class="flex gap-2 mt-4">
        <button class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Add to Cart
        </button>
        
        @if(isset($product->meta_data['video_url']) && $product->meta_data['video_url'] !== '[URL]')
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                ▶️ Video
            </button>
        @endif
    </div>
    
    {{-- Additional Info Toggle --}}
    <details class="mt-4">
        <summary class="text-sm text-blue-600 cursor-pointer hover:text-blue-800">
            More Details
        </summary>
        <div class="mt-2 p-3 bg-gray-50 rounded text-sm">
            @if(isset($product->meta_data['shopify_product_id']))
                <p><strong>Shopify ID:</strong> {{ $product->meta_data['shopify_product_id'] }}</p>
            @endif
            @if(isset($product->meta_data['created_at']))
                <p><strong>Created:</strong> {{ $product->meta_data['created_at'] }}</p>
            @endif
            @if(isset($product->meta_data['lock_until']))
                <p><strong>Locked Until:</strong> {{ $product->meta_data['lock_until'] }}</p>
            @endif
            @if(isset($product->meta_data['never_push_to_ads']) && $product->meta_data['never_push_to_ads'] === 'TRUE')
                <p class="text-orange-600"><strong>⚠️ Not for Ads</strong></p>
            @endif
        </div>
    </details>
</div>
