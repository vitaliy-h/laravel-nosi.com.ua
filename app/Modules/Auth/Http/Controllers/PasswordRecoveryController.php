<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\PasswordRecoveryRequest;
use App\Modules\Core\Dtos\SendLetterTemplateData;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Facades\LetterTemplateFacade;
use App\Modules\Core\Repositories\UsersRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordRecoveryController extends Controller
{





}
