<?php

namespace App\Console\Commands\Producto;

use App\Models\Producto;
use Illuminate\Console\Command;

class StateStockProducto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'producto:state-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar al producto 3 estados (1 es disponible, 2 por agotar y 3 agotado)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productos = Producto::where("state",1)->get();
        foreach ($productos as $producto) {
            $nuevo_estado = $producto->state_stock;
    
            if ($producto->stock === 0) {
                $nuevo_estado = 3;
            } elseif ($producto->stock > $producto->stock_seguridad) {
                $nuevo_estado = 1;
            } else {
                $nuevo_estado = 2;
            }
    
            // Solo actualiza si el estado ha cambiado
            if ($producto->state_stock !== $nuevo_estado) {
                $producto->update([
                    'state_stock' => $nuevo_estado
                ]);
            }
        }
    }
}
