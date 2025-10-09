<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Handle authentication-related exceptions
        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('info', 'Please login to continue.');
        });
        
        // Handle authorization exceptions (403 errors)
        $this->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => $e->getMessage()
                ], 403);
            }
            
            // If user is not authenticated, redirect to login
            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('warning', 'Please login to access this area.');
            }
            
            // If user is authenticated but lacks permission, show 403
            return response()->view('errors.403', [
                'message' => $e->getMessage()
            ], 403);
        });
    }
}
