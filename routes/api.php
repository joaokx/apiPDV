<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeraXMLNFe;

use App\Http\Controllers\ImprimiNFeMod55;
use App\Http\Controllers\CancelaNFe;
use App\Http\Controllers\ConsultaNFe;
use App\Http\Controllers\CartaDeCorrecaoNFe;
use App\Http\Controllers\StatusSefaz;
use App\Http\Controllers\GeraXMLPOST;
use App\Http\Controllers\ConsultaPost;
use App\Http\Controllers\CancelarPost;
use App\Http\Controllers\ImprimirPost;
use App\Http\Controllers\CartaPost;
use App\Http\Controllers\CadastroNFe;

use App\Http\Controllers\SendEmailNFe;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


///GeraXmlNFe
Route::get('/GeraXmlNFe', [GeraXMLNFe::class,'index']);
//Imprimir
Route::post('/Imprimir', [ImprimiNFeMod55::class,'store']);

//CancelarCancelar
Route::get('/Cancelar', [CancelaNFe::class,'index']);
//CancelarCancelar
Route::get('/Consulta', [ConsultaNFe::class,'index']);
Route::get('/Carta', [CartaDeCorrecaoNFe::class,'index']);
Route::get('/Status', [StatusSefaz::class,'index']);
Route::post('/GeraXML_auto', [GeraXMLPOST::class,'store']);
Route::post('/ConsultaPotss', [ConsultaPost::class,'store']);
Route::post('/CancelamentoPotss', [CancelarPost::class,'store']);
Route::post('/ImprimirPotss', [ImprimirPost::class,'store']);
Route::post('/CartaPosts', [CartaPost::class,'store']);

///criando nova nfe
Route::post('/Cadastro', [CadastroNFe::class,'create']);

Route::post('/Send', [SendEmailNFe::class,'store']);
