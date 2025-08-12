<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::group([
                'prefix' => 'api/v1/admin',
                'middleware' => ['api', 'auth.api']
            ], function () {
                require base_path('routes/others/admin.routes.php');
            });

            Route::prefix('api/v1/familiar')
                ->middleware(['api', 'auth.api'])
                ->group(base_path('routes/others/familiar.routes.php'));


            Route::prefix('api/v1/guardia')
                ->middleware(['api', 'auth.api'])
                ->group(base_path('routes/others/guardia.routes.php'));


            Route::prefix('api/v1/jefe-familia')
                ->middleware(['api', 'auth.api'])
                ->group(base_path('routes/others/jefe_familia.routes.php'));


            Route::prefix('api/v1/jefe-cerrada')
                ->middleware(['api', 'auth.api'])
                ->group(base_path('routes/others/jefe_cerrada.routes.php'));

                
            Route::prefix('api/v1/tecnico')
                ->middleware(['api'])
                ->group(base_path('routes/others/tecnico.routes.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
