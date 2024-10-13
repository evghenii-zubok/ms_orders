## HOW TO

- In boostrap/app.php - Ho inserito "trust proxies" abilitato per tutti. Per il mio ambiente serve specificarlo. Non sapendo se nel vostro caso servono ho messo abilitato a tutto non conoscendo il vostro ambiente.
```
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
        ->trustProxies(at: [
            '*',
        ]);
    })
```
- In app/Providers/AppServiceProvider nel boot() - è stato inserito URL::forceScheme('https'). Togliere se non si usa HTTPS in development.
```
    public function boot(): void
    {
        // forza a usare HTTPS sempre
        URL::forceScheme('https');
    }
```