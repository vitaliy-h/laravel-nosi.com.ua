{{-- Review submit result: abc-response.js puts it into #review-dialog-body in place of the form. --}}
<h3>{{ t('home.review_form_title') }}</h3>
<div class="alert alert-success">{{ $message }}</div>
<div class="modal-actions">
    <button type="button" class="btn btn-dark btn-sm" data-dialog-close>{{ t('buttons.close') }}</button>
</div>
