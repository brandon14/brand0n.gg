<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function str_contains;

final class DynamicToken extends Controller
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private Application $app;

    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    private ResponseFactory $response;

    /**
     * @var \Illuminate\Contracts\Session\Session
     */
    private Session $session;

    /**
     * Constructs a new DynamicToken.
     *
     * @param \Illuminate\Contracts\Foundation\Application  $app
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Illuminate\Contracts\Session\Session         $session
     */
    public function __construct(Application $app, ResponseFactory $response, Session $session)
    {
        $this->app = $app;
        $this->response = $response;
        $this->session = $session;
    }

    /**
     * TODO: Undocumented method.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRefresh(Request $request): JsonResponse
    {
        $referer = $request->headers->get('referer');
        $contains = str_contains($referer, $request->getHttpHost());

        if (empty($referer) || ! $contains) {
            $this->app->abort(404);
        }

        return $this->response->json([
            'csrf_token' => $this->session->token(),
        ]);
    }
}
