<?php namespace Dstokesy\Franchises\Classes;

use Closure;
use Dstokesy\Franchises\Classes\Franchiser;

class Middleware
{
	/**
	 * Intercept incoming requests, if the request
	 * results in a 404 - try load a category/page.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		$franchiser = Franchiser::instance();
    	$redirect = $franchiser->getRedirect();

	    $contentType = $response->headers->get('content-type');

	    $isCss = str_contains($contentType, 'text/css');

    	if ($redirect && !$isCss) {
    		return $redirect;
    	}

		return $response;
	}
}
