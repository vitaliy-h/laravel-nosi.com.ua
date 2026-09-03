{{-- Newsletter subscribe result, rendered inline by abc-response.js into #newsletter-message. --}}
<div class="alert {{ $already_exists ? 'alert-info' : 'alert-success' }} mb-0">
    {{ $message }}
</div>
