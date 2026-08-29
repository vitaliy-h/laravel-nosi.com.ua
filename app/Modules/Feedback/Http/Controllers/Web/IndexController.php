<?php

namespace App\Modules\Feedback\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function index(Request $request)
    {
        return $this->render('pages.feedback');
    }
}
