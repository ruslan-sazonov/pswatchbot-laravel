<?php

namespace App\Http\Middleware;

use Closure;
use App\Bot\Manager;

class CheckMessengerWebhook
{

    /**
     * @var \App\Bot\Manager
     */
    protected $messenger;

    /**
     * CheckMessengerWebhook constructor.
     *
     * @param \App\Bot\Manager $bot
     */
    public function __construct(Manager $bot)
    {
        $this->messenger = $bot->messenger()->client();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        if (!$this->messenger->isValidCallback()) {
            abort(400, 'Invalid Request');
        }

        return $next($request);
    }
}
