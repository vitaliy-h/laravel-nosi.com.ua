<footer class="bg-light mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row text-start">
            @foreach($footerMenu as $item)
                <div class="col-md-3 mb-3">
                    <ul class="list-unstyled">
                        <li class="nav-item">
                            <a class="text-decoration-none text-dark"
                               href="{{\App\Modules\Core\Facades\UrlFacade::normalize($item->slug)}}">{{$item->title}}</a>
                        </li>
                    </ul>
                </div>
            @endforeach
        </div>

        <hr>

        <div class="text-center">
            <p class="mb-0">
                © {{ date('Y') }} {{t('footer_copyright')}}
            </p>
        </div>
    </div>
</footer>
