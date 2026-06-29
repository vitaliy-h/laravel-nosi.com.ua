@extends('layouts.app')

@section('content')
    <div class="page-title light-background">
        <div class="container">
            <h1 class="mb-0">{{ t('profile.order_id') }}: #{{ $order->order_number }}</h1>
        </div>
    </div>

    <section class="cart section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center mb-4" data-aos="zoom-in">
                    <div class="cart-items py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="mb-2">{{ t('shop.payment_success_title') }}</h3>
                        <p class="text-muted mb-4">{{ t('shop.payment_success_description') }}</p>

                        <div class="cart-summary d-inline-block text-start w-100 mb-4" style="position:static;">
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.order_id') }}</span>
                                <span class="summary-value">#{{ $order->order_number }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.order_status') }}</span>
                                <span class="summary-value">
                                    <span class="badge rounded-pill bg-success">{{ $order->payment_status->label() }}</span>
                                </span>
                            </div>
                            <div class="summary-total">
                                <span class="summary-label">{{ t('profile.order_total') }}</span>
                                <span class="summary-value">{{ $order->total_amount_formatted }}</span>
                            </div>
                        </div>

                        <a href="{{ route('order.show', $order->uuid) }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-receipt me-2"></i>{{ t('shop.view_order') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
