@foreach($content ?? [] as $block)
    @includeWhen(
        !empty($block['type']),
        'components.builder.' . $block['type'],
        ['block' => $block]
    )
@endforeach

