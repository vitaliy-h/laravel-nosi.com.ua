<?php

use Illuminate\Support\Facades\Route;


//dd(1);
//app/Http/Controllers/FormController.php

Route::middleware(['web'])->group(function () {

    //http://lv-floritta/api/

    Route::post('/basket/change', [\App\Http\Controllers\ApiController::class, 'change'])->name('change_crm_order');



});
