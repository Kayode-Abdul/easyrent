&lt;?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response-&gt;headers-&gt;set(&#39;X-Content-Type-Options&#39;, &#39;nosniff&#39;);
        $response-&gt;headers-&gt;set(&#39;X-Frame-Options&#39;, &#39;SAMEORIGIN&#39;);
        $response-&gt;headers-&gt;set(&#39;X-XSS-Protection&#39;, &#39;1; mode=block&#39;);
        $response-&gt;headers-&gt;set(&#39;Referrer-Policy&#39;, &#39;strict-origin-when-cross-origin&#39;);
        $response-&gt;headers-&gt;set(&#39;Strict-Transport-Security&#39;, &#39;max-age=31536000; includeSubDomains&#39;);

        return $response;
    }
}