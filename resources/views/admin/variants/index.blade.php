@extends('layouts.admin')

@section('title', 'Manage Variants')
@section('page-title', 'Variants Management')
@section('page-subtitle', 'Manage product variants and stock control.')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* { font-family: 'Inter', sans-serif; }

body {
    background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
}

body::before {
    content: '';
    position: fixed;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 40%, rgba(220,38,38,0.04) 0%, rgba(0,0,0,0.02) 50%, transparent 80%);
    animation: cinematicMove 20s ease infinite;
    pointer-events: none;
    z-index: 0;
}
@keyframes cinematicMove {
    0% { transform: translate(0,0); }
    50% { transform: translate(5%,5%); }
    100% { transform: translate(0,0); }
}

.card-cinematic {
    background: rgba(255,255,255,0.96);
    backdrop-filter: blur(2px);
    border: 1px solid rgba(255,255,255,0.5);
    transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
}
.card-cinematic:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 35px -12px rgba(0,0,0,0.15);
    border-color: rgba(220,38,38,0.2);
}

.gradient-title {
    font-weight: 800;
    background: linear-gradient(135deg, #000000, #dc2626);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.btn-sm-3d {
    font-size: 0.75rem;
    padding: 0.4rem 1rem;
    border-radius: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.05s linear;
    transform: translateY(-1px);
    cursor: pointer;
    text-decoration: none;
}
.btn-sm-3d:active { transform: translateY(3px); }

.btn-sm-blue { background:#3b82f6; color:white; box-shadow:0 3px 0 #1e40af; border:none; }
.btn-sm-red { background:#ef4444; color:white; box-shadow:0 3px 0 #991b1b; border:none; }
.btn-sm-green { background:#10b981; color:white; box-shadow:0 3px 0 #047857; border:none; }
.btn-sm-outline { background:transparent; color:#64748b; border:1px solid #e2e8f0; box-shadow:0 2px 0 #cbd5e1; }

.table-row-3d {
    transition: all 0.25s ease;
}
.table-row-3d:hover {
    transform: translateX(4px) translateY(-2px);
    background: #fafcff;
    box-shadow: 0 8px 15px -6px rgba(0,0,0,0.08);
}

.input-premium {
    width: 100%;
    padding: 0.65rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: white;
    transition: all 0.2s;
}
.input-premium:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
    outline: none;
    transform: translateY(-1px);
}

.color-tag {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.variant-group-card {
    border: 1px solid #f0f0f0;
    border-radius: 1rem;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    transition: all 0.2s;
}
.variant-group-card:hover {
    border-color: #dc2626/30;
    box-shadow: 0 8px 20px -10px rgba(0,0,0,0.1);
}

.btn-add-variant {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-add-variant:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220,38,38,0.3);
}

.color-image-assignment {
    margin: 0 0 1rem;
    padding: 0.75rem;
    border-radius: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
</style>
@endsection

@section('content')
@php
    $usSizes = ['7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '11.5', '12', '12.5', '13'];
@endphp
<div class="max-w-7xl mx-auto px-4 py-10 relative z-10">

    {{-- BACK BUTTON --}}
    <div class="mb-6">
        <a href="{{ request('return_to', route('admin.products.index')) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-red-600 transition-all hover:translate-x-[-2px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Products
        </a>
    </div>

    {{-- HEADER WITH PRODUCT INFO --}}
    <div class="card-cinematic rounded-2xl p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold gradient-title">{{ $product->product_name }}</h1>
                <p class="text-gray-500 text-sm mt-1">Manage variants (size / color combinations) for this product</p>
            </div>
            <div class="flex gap-3">
                <div class="bg-gray-100 rounded-xl px-4 py-2 text-center">
                    <p class="text-xs text-gray-500">Variants</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $product->variants->count() }}</p>
                </div>
                <div class="bg-gray-100 rounded-xl px-4 py-2 text-center">
                    <p class="text-xs text-gray-500">Product ID</p>
                    <p class="text-2xl font-bold text-gray-800">#{{ $product->product_id }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT - FULL WIDTH LAYOUT (FIXED THE EMPTY SPACE) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- LEFT: ADD NEW COLOR / VARIANT FORM --}}
        <div class="card-cinematic rounded-2xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h2 class="text-xl font-bold gradient-title">Add New Color</h2>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.products.variants.store', $product->product_id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Color Name</label>
                    <input type="text" name="color" class="input-premium" placeholder="e.g., Black, Red, White" required>
                    <p class="text-xs text-gray-400 mt-1">Enter the color name for this variant</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">First Size</label>
                    <select name="size" class="input-premium" required>
                        <option value="">Select US size</option>
                        @foreach($usSizes as $size)
                            <option value="{{ $size }}" {{ old('size') === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Choose a US size from 7 to 13.</p>
                </div>

                <div>
                    <label for="colorImage" class="block text-sm font-semibold text-gray-700 mb-1">Color Image <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                    <input type="file" name="image" id="colorImage" accept="image/jpeg,image/png,image/webp,image/gif" class="input-premium">
                    <p class="text-xs text-gray-400 mt-1">This image will be assigned to the new color.</p>
                </div>

                <button type="submit" class="w-full btn-add-variant py-2.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New Color
                </button>
            </form>
        </div>

        {{-- RIGHT: ADD SIZE TO EXISTING COLOR --}}
        <div class="card-cinematic rounded-2xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-1 h-6 bg-gradient-to-b from-blue-600 to-black rounded-full"></div>
                <h2 class="text-xl font-bold gradient-title">Add Size to Color</h2>
            </div>

            <form method="POST" action="{{ route('admin.products.variants.store', $product->product_id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Select Color</label>
                    <select name="color" id="existingColor" class="input-premium" required>
                        <option value="">-- Choose a color --</option>
                        @foreach($variants->groupBy('color') as $color => $items)
                            <option value="{{ $color }}" data-used-sizes="{{ $items->pluck('size')->implode(',') }}">{{ $color }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">New Size</label>
                    <select name="size" id="existingSize" class="input-premium" required disabled>
                        <option value="">Choose a color first</option>
                        @foreach($usSizes as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Sizes already used by the selected color are unavailable.</p>
                </div>

                <button type="submit" class="w-full btn-add-variant py-2.5 flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #2563eb, #1e40af);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Size to Color
                </button>
            </form>
        </div>
    </div>

    {{-- VARIANTS LIST SECTION --}}
    <div class="mt-8">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h2 class="text-xl font-bold gradient-title">All Variants</h2>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $variants->count() }} total variants</span>
        </div>

        @if($variants->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($variants->groupBy('color') as $color => $items)
                    <div class="variant-group-card p-5">
                        {{-- COLOR HEADER WITH BADGE --}}
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full" style="background: {{ 
                                    match(strtolower($color)) {
                                        'black' => '#1a1a1a',
                                        'white' => '#f5f5f5',
                                        'red' => '#ef4444',
                                        'blue' => '#3b82f6',
                                        'green' => '#10b981',
                                        'yellow' => '#eab308',
                                        'purple' => '#8b5cf6',
                                        'pink' => '#ec4899',
                                        'orange' => '#f97316',
                                        'brown' => '#8b4513',
                                        'gray', 'grey' => '#6b7280',
                                        default => '#dc2626'
                                    }
                                }}"></div>
                                <h3 class="font-bold text-lg text-gray-800">{{ $color }}</h3>
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $items->count() }} size(s)</span>
                        </div>

                        <div class="color-image-assignment">
                            @php
                                $assignedImages = $product->images->where('color', $color);
                            @endphp
                            @if($product->images->isNotEmpty())
                                <form method="POST"
                                      id="assign-image-{{ Str::slug($color) }}"
                                      action="{{ route('admin.products.images.assignColor', $product->images->first()->image_id) }}"
                                      class="flex flex-col gap-2 sm:flex-row sm:items-end"
                                      data-default-action="{{ route('admin.products.images.assignColor', $product->images->first()->image_id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="color" value="{{ $color }}">
                                    <div class="flex-1">
                                        <label for="image-for-{{ Str::slug($color) }}" class="block text-xs font-semibold text-gray-600 mb-1">Color Image</label>
                                        <select name="image_id" id="image-for-{{ Str::slug($color) }}" class="input-premium text-sm py-2" required>
                                            <option value="">Choose an existing product image</option>
                                            @foreach($product->images as $image)
                                                <option value="{{ $image->image_id }}"
                                                        data-action="{{ route('admin.products.images.assignColor', $image->image_id) }}"
                                                        {{ $assignedImages->contains('image_id', $image->image_id) ? 'selected' : '' }}>
                                                    Image {{ $loop->iteration }}{{ $image->color ? ' - ' . $image->color : ' - General' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-sm-3d btn-sm-blue whitespace-nowrap">
                                        <i class="fas fa-image"></i> Assign Image
                                    </button>
                                </form>
                                @if($assignedImages->isNotEmpty())
                                    <p class="mt-2 text-xs text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i> Image assigned to {{ $color }}
                                    </p>
                                @else
                                    <p class="mt-2 text-xs text-gray-400">No image assigned to this color yet.</p>
                                @endif
                            @else
                                <p class="text-xs text-gray-500">Upload product images first to assign one to this color.</p>
                            @endif
                        </div>

                        {{-- SIZES LIST --}}
                        <div class="space-y-2">
                            @foreach($items as $variant)
                                <div class="flex items-center justify-between py-2 border-b border-gray-50 hover:bg-gray-50/50 rounded-lg px-2 transition-all">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                        <span class="font-medium text-gray-700">{{ $variant->size }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.variants.edit', ['variant' => $variant->product_variant_id, 'return_to' => request()->fullUrl()]) }}" class="btn-sm-3d btn-sm-blue px-3 py-1 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Edit
                                        </a>
                                        <button type="button" onclick="openDeleteVariant({{ $variant->product_variant_id }})" class="btn-sm-3d btn-sm-red px-3 py-1 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2h6m4-1l3 3m0 0l-3 3m3-3H9"/></svg>
                                            Archive
                                        </button>
                                        <a href="{{ route('admin.stocks.index', ['variant' => $variant->product_variant_id, 'return_to' => request()->fullUrl()]) }}" class="btn-sm-3d btn-sm-green px-3 py-1 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            Stock
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card-cinematic rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                <p class="text-gray-500 text-sm">No variants yet. Add your first variant using the forms above.</p>
            </div>
        @endif
    </div>
</div>

{{-- SUCCESS MODAL --}}
@if(session('success'))
<div id="successModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-sm p-6 rounded-2xl text-center shadow-2xl transform transition-all">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Success!</h2>
        <p class="text-gray-600 mt-2">{{ session('success') }}</p>
        <button onclick="closeSuccessModal()" class="mt-4 px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-semibold hover:shadow-lg transition-all">OK</button>
    </div>
</div>
@endif

{{-- ARCHIVE CONFIRMATION MODAL --}}
<div id="deleteVariantModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-2xl transform transition-all">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h2 class="font-bold text-lg text-center">Archive Variant?</h2>
        <p class="text-gray-500 text-sm text-center mt-2">This will disable the variant from the catalog while preserving its stock records.</p>
        <form id="deleteVariantForm" method="POST" class="mt-4">
            @csrf
            @method('DELETE')
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeDeleteVariant()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Archive</button>
            </div>
        </form>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('successModal');
    if (modal) {
        setTimeout(() => {
            modal.style.display = 'none';
        }, 3000);
    }
});

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}

function openDeleteVariant(id) {
    document.getElementById('deleteVariantForm').action = '/admin/variants/' + id;
    document.getElementById('deleteVariantModal').style.display = 'flex';
}

function closeDeleteVariant() {
    document.getElementById('deleteVariantModal').style.display = 'none';
}
</script>
<script>
const existingColor = document.getElementById('existingColor');
const existingSize = document.getElementById('existingSize');

function updateAvailableSizes() {
    if (!existingColor || !existingSize) {
        return;
    }

    const selectedOption = existingColor.options[existingColor.selectedIndex];
    const usedSizes = selectedOption?.dataset.usedSizes
        ? selectedOption.dataset.usedSizes.split(',').map(size => size.trim())
        : [];

    existingSize.disabled = !existingColor.value;
    existingSize.value = '';

    [...existingSize.options].forEach(option => {
        if (!option.value) {
            option.textContent = existingColor.value ? 'Select new US size' : 'Choose a color first';
            option.disabled = false;
            return;
        }

        const isUsed = usedSizes.map(String).includes(String(option.value));
        option.disabled = isUsed;
        option.textContent = isUsed ? `${option.value} (already added)` : option.value;
    });
}

existingColor.addEventListener('change', updateAvailableSizes);
updateAvailableSizes();

document.querySelectorAll('.color-image-assignment form').forEach(form => {
    const imageSelect = form.querySelector('select[name="image_id"]');

    imageSelect.addEventListener('change', () => {
        const selectedImage = imageSelect.options[imageSelect.selectedIndex];
        form.action = selectedImage.dataset.action || form.dataset.defaultAction;
    });

    imageSelect.dispatchEvent(new Event('change'));
});
</script>
@endsection