{{-- Category tree of the catalogue sidebar, recursive. Children are shown for the active
     category and its ancestors (is_active / is_opened from GetAllCategoriesQuery). --}}
<ul @class(['cat-tree' => $level === 0])>
    @foreach($categories as $item)
        <li>
            <a href="{{ $item->url }}" @class(['is-active' => $item->is_active])>
                {{ $item->title }}
                <span>{{ $item->products_count }}</span>
            </a>

            @if($item->children->isNotEmpty() && ($item->is_active || $item->is_opened))
                @include('modules.shop.partials.category_tree', [
                    'categories' => $item->children,
                    'level' => $level + 1,
                ])
            @endif
        </li>
    @endforeach
</ul>
