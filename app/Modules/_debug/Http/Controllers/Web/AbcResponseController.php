<?php

namespace App\Modules\_debug\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbcResponseController extends BaseWebController
{
    public function abc_response_index(Request $request)
    {
        return view('pages.test.abc_response');
    }

    public function abc_response_request(Request $request, string $action): JsonResponse
    {
        $response = [
            //'success' => true,
            'actions' => [],
        ];

        switch ($action) {
            case 'html':
                $response['actions'][] = [
                    'method' => 'html',
                    'selector' => '#test-html',
                    'content' => '<strong>HTML replaced successfully</strong>',
                ];
                break;

            case 'text':
                $response['actions'][] = [
                    'method' => 'text',
                    'selector' => '#test-text',
                    'content' => 'Updated text content',
                ];
                break;

            case 'append':
                $response['actions'][] = [
                    'method' => 'append',
                    'selector' => '#test-append',
                    'content' => '<div>New appended item</div>',
                ];
                break;

            case 'prepend':
                $response['actions'][] = [
                    'method' => 'prepend',
                    'selector' => '#test-append',
                    'content' => '<div>Prepended item</div>',
                ];
                break;

            case 'class':
                $response['actions'][] = [
                    'method' => 'addClass',
                    'selector' => '#test-class',
                    'content' => 'red',
                ];
                break;

            case 'removeClass':
                $response['actions'][] = [
                    'method' => 'removeClass',
                    'selector' => '#test-class',
                    'content' => 'red',
                ];
                break;

            case 'hide':
                $response['actions'][] = [
                    'method' => 'hide',
                    'selector' => '#test-hide',
                ];
                break;

            case 'show':
                $response['actions'][] = [
                    'method' => 'show',
                    'selector' => '#test-hide',
                ];
                break;

            case 'replace':
                $response['actions'][] = [
                    'method' => 'replaceWith',
                    'selector' => '#test-replace',
                    'content' => '<div id="test-replace">Element replaced</div>',
                ];
                break;

            case 'alert':
                $response['actions'][] = [
                    'method' => 'alert',
                    'content' => 'Alert works',
                ];
                break;

            case 'reload':
                $response['actions'][] = [
                    'method' => 'reload',
                ];
                break;

            case 'location':
                $response['actions'][] = [
                    'method' => 'location',
                    'content' => 'https://google.com',
                ];
                break;

            case 'script':
                $response['actions'][] = [
                    'method' => 'script',
                    'content' => '<script>alert("Dynamic script executed")</script>',
                ];
                break;

            case 'form':

                $response['actions'][] = [
                    'method' => 'html',
                    'selector' => '#test-form .abcr-result',
                    'content' => 'Form submitted successfully',
                ];

                $response['actions'][] = [
                    'method' => 'clearForm',
                    'selector' => '#test-form',
                ];

                break;


        }

        return response()->json($response);
    }
}
