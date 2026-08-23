{{-- Star row for a 0..5 rating: full stars, an optional half star, then empty ones. --}}
@props(['value' => 0])

@php
    $rating = max(0, min(5, (float) $value));
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);
@endphp

@for($i = 0; $i < $full; $i++)
    <i class="bi bi-star-fill"></i>
@endfor

@if($half)
    <i class="bi bi-star-half"></i>
@endif

@for($i = 0; $i < $empty; $i++)
    <i class="bi bi-star"></i>
@endfor
