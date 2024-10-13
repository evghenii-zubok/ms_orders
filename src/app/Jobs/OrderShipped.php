<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    private array $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function handle(): void {}
}
