@foreach($content ?? [] as $block)
    @includeWhen(
        !empty($block['type']),
        'partials.builder.' . $block['type'],
        ['block' => $block]
    )
@endforeach

