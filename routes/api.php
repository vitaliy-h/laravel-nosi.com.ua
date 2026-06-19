<?php

use Illuminate\Support\Facades\Route;


//dd(1);
//app/Http/Controllers/FormController.php

Route::middleware(['web'])->group(function () {

    //http://lv-floritta/api/

    Route::post('/basket/change', [App\Modules\Basket\Http\Controllers\Web\IndexController::class, 'api_change'])->name('change_product_count');



});
