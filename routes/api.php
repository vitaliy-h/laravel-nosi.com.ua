<?php

use Illuminate\Support\Facades\Route;


// todo: remove comments, move this route to Basket/routes/web.php (not api.php — this is an AJAX request, not a REST API endpoint). Also rename the handler: drop 'api' from the method name to avoid confusion with actual API routes.
//dd(1);
//app/Http/Controllers/FormController.php

Route::middleware(['web'])->group(function () {

    //http://lv-floritta/api/

    Route::post('/basket/change', [App\Modules\Basket\Http\Controllers\Web\IndexController::class, 'api_change'])->name('change_product_count');



});
