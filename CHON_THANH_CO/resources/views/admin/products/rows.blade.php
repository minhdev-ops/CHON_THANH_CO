@forelse ($products as $product)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
            <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-check rounded border-gray-300">
        </td>
        <td class="px-4 py-3">
            <img src="{{ $product->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
        </td>
        <td class="px-4 py-3">
            <div class="font-medium">{{ $product->name }}</div>
            <div class="text-xs text-gray-500">{{ $product->code }}</div>
        </td>
        <td class="px-4 py-3 text-gray-600">{{ $product->category?->name }}</td>
        <td class="px-4 py-3 text-center text-gray-600">
            {{ $product->strength_min !== null ? $product->strength_min : '—' }}
            @if ($product->strength_max !== null && $product->strength_max != $product->strength_min)
                – {{ $product->strength_max }}
            @endif
        </td>
        <td class="px-4 py-3 text-center text-gray-500">{{ $product->specs_count }}</td>
        <td class="px-4 py-3 text-center">
            @if ($product->is_featured)
                <span class="text-amber-500 material-symbols-outlined">star</span>
            @else
                <span class="text-gray-300">—</span>
            @endif
        </td>
        <td class="px-4 py-3 text-center">
            @if ($product->is_active)
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
            @else
                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
            @endif
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Không có sản phẩm nào.</td>
    </tr>
@endforelse
