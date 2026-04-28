<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitLang
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($request->has('lang') && !$request->is('livewire-*')) {
            $locale = $request->input('lang');
            if (in_array($locale, ['vi', 'en'])) {
                $_SESSION['locale'] = $locale;
                \LangHelper::setLocale($locale);
                
                $url = $request->url();
                return redirect($url);
            }
        }
        
        \LangHelper::init();
        return $next($request);
    }
}
