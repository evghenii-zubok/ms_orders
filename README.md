## Due righe di info

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
- Configurazione docker-compose. Non è una configurazione testata e ready to use. E' utilizzabile dal mio ambiente di sviluppo, ma probabilmente ci saranno degli errori se lanciato altrove. Inoltre prima dei container nel mio caso c'è un nginx reverse proxy ed è per questo motivo che ho bisogno sia dell'https che dei proxy abilitati. \
\
*Questo per dire che ci sarà da mettere mano e sistemare un po' il docker compose*

- Se decidete di modificare la mia configurazione del docker-compose tenete presente che ci sono un po' di cose da sostituire a mano. Ecco l'elenco:\
    1. Seguire il path: docker/webserver/httpd/dev e modificare il file app_vhost.conf sostituendo soltanto il dominio giusto .... non ha senso farlo nella cartella loc
    2. Aprire il file .env e controllare il contenuto. Ci sono le password (tanto sono quelle dell'ambiente dev). Poi ci sarebbero i VIRTUAL_HOST e VIRTUAL_PORT che devono corrispondere al dominio nel file vhost di httpd. Questi sono per il reverse proxy. Vedete voi se lo usate.
    3. Aprire anche il file dev.env che sta nella stessa posizione del file .env e aggiustare i suoi dati come nell'altro.

- Il file .env.example di laravel l'ho popolato simile al mio file .env quindi c'è poco da modificare ... il dominio, connessioni al db e connessione a redis. Ho già lasciato i dati di connessione al servizio CoudAMQP (che ho creato appositamente per questo test quindi no problem se sono visibili). Laravel di test è configurato con rabbitmq usando appunto CoudAMQP.

## Istruzioni interne a Laravel
- Una volta entrati nel progetto, entrare nel container e lanciare *php artisan app:init*. Non fa nulla di spazionale, fa un migrate, composer update e crea lìutente per lo swagger.
- {Schema}://{YourDomain}/api/documentation - Qua troverete lo swagger per eseguire i test.
- Autentucazione swagger - Aprire il gruppo "Autentication" e fare Try it out. I dati inseriti nell'esempio corrispondo all'utente creato con app:init.
- Copiare il token restituito dopo aver eseguito /api/login. Cliccare "Authorize" in alto a destra e ATTENZIONE
- Copiare il token usando la seguente sintassi: "Bearer {token_copiato}". Purtroppo non ho ancora trovato il modo per eseguire il login in automatico e aggiungere la parte "Bearer " senza doverla scrivere ogni volta.
- A quel punto si possno testare le API
