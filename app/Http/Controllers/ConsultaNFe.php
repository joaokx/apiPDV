<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

class ConsultaNFe extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $empresa = DB::table('empresa')
                ->where('id', 6)
                ->first();

            $antes = ['.', '-', '/', '(', ')'];
            $depos = ['', '', '', '', ''];
            $cpf_cnpj = str_replace($antes, $depos, $empresa->cnpj);

            $config = [
                'atualizacao' => date('Y-m-d H:i:s'),
                'tpAmb' => $empresa->ambiente,
                'razaosocial' => $empresa->razao_social,
                'cnpj' => $cpf_cnpj, // PRECISA SER VÁLIDO
                'ie' => $empresa->ie, // PRECISA SER VÁLIDO
                'siglaUF' => $empresa->UF,
                'schemes' => 'PL_009_V4',
                'versao' => '4.00',
            ];

            $certificadoDigital = file_get_contents(
                $empresa->url_dominio .
                    $empresa->path_site .
                    $empresa->certificado_a3
            );
            $tools = new Tools(
                json_encode($config),
                Certificate::readPfx(
                    $certificadoDigital,
                    $empresa->senha_centificado
                )
            );
            $tools->model('55');

            $chave = '29220132596049000145551020000000121699434045';
            $response = $tools->sefazConsultaChave($chave);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();

            dd($arr);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
