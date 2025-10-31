<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoPedidoCocina implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    public function broadcastOn()
    {
        return new Channel('cocina');
    }

    public function broadcastAs()
    {
        return 'nuevo-pedido';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->pedido['id'],
            'numero_venta' => $this->pedido['numero_venta'],
            'cliente_nombre' => $this->pedido['cliente_nombre'] ?? 'Cliente General',
            'mesa_id' => $this->pedido['mesa_id'] ?? null,
            'total_items' => count($this->pedido['detalles'] ?? []),
            'mensaje' => '¡Nuevo pedido en cocina!'
        ];
    }
}