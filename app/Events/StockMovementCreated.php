<?php

namespace App\Events;

use App\Models\StockMovement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockMovementCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stockMovement;

    public function __construct(StockMovement $stockMovement)
    {
        $this->stockMovement = $stockMovement;
    }
} 