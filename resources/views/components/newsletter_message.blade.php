{{-- Newsletter subscribe result, rendered inline by abc-response.js into #newsletter-message. --}}
<div class="alert {{ $alreadyExists ? 'alert-info' : 'alert-success' }} mb-0">
    {{ $message }}
</div>
