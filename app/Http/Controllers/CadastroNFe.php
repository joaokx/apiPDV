<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CadastroNFe extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $resquet)
    {


        try {
            DB::table('nfe_temp')->insert([
            'status_id' => $resquet->status_id,
            'numero_nfe' => $resquet->numero_nfe,
            'ide_serie' => $resquet->ide_serie,
            'empresa_id' => $resquet->empresa_id,
            'cliente_id' => $resquet->cliente_id,
            'pedidos_id' => $resquet->pedidos_id,
        ]);


          return 'NFe Cadastrada ccom Sucesso!';
        } catch(Exception $e){

         echo $e->getMessage();
        }
    }
}
