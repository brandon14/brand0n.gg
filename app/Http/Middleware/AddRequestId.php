<?php

namespace App\Http\Middleware;

use Closure;
use function is_null;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

/**
 * Class AddRequestId
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class AddRequestId
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $uuid = $request->headers->get('X-Request-ID');

        if (is_null($uuid)) {
            $uuid = Uuid::uuid4()->toString();

            $request->headers->set('X-Request-ID', $uuid);
        }

        $_SERVER['HTTP_X_REQUEST_ID'] = $uuid;

        $response = $next($request);

        $response->headers->set('X-Request-ID', $uuid);

        return $response;
    }
}
