<ul class="{{ $level === 0 ? 'category-tree list-unstyled mb-0' : 'subcategory-list list-unstyled ps-3 mt-2 collapse' }} {{ $level > 0 && !empty($parentOpened) ? 'show' : '' }}">
    @foreach($categories as $item)
        @php
            $hasChildren = $item->children->isNotEmpty();
            $isExpanded = $item->is_active || $item->is_opened;
            $collapseId = 'category-' . $item->id . '-children';
        @endphp

        <li class="category-item">
            <div
                class="d-flex justify-content-between align-items-center category-header {{ $hasChildren && !$isExpanded ? 'collapsed' : '' }}"
                @if($hasChildren)
                    data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}"
                aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                aria-controls="{{ $collapseId }}"
                @endif
            >
                <a
                    href="{{ $item->url }}"
                    class="{{ $level === 0 ? 'category-link' : 'subcategory-link' }} {{ $item->is_active ? 'active' : '' }}"
                >
                    {{ $item->title }}
                    <span class="category-count">({{ $item->products_count }})</span>
                </a>

                @if($hasChildren)
                    <span class="category-toggle">
                        <i class="bi bi-chevron-down"></i>
                        <i class="bi bi-chevron-up"></i>
                    </span>
                @endif
            </div>

            @if($hasChildren)
                <ul
                    id="{{ $collapseId }}"
                    class="subcategory-list list-unstyled ps-3 mt-2 collapse {{ $isExpanded ? 'show' : '' }}"
                >
                    @include('modules.shop.partials.category_tree_items', [
                        'categories' => $item->children,
                        'level' => $level + 1,
                    ])
                </ul>
            @endif
        </li>
    @endforeach
</ul>
