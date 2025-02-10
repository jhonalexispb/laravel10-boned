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
        foreach ($productos as $k => $v){
            $stock_seguridad = $productos->stock_seguridad;
            if($productos->stock_seguridad <= $productos->stock_seguridad){
                $prod
            }
        }
    }
}
