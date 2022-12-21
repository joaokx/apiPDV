<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

class CartaPost extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $nota = DB::table('nfe_temp')
                ->where('id', $request->id)
                ->first();

            $empresa = DB::table('empresa')
                ->where('id', $nota->empresa_id)
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

            $chave = $nota->ide_Id;

            $xCorrecao = $request->jutificativa;
            $nSeqEvento = $request->evento;
            $response = $tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
            $stdCl = new Standardize($response);

            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();

            dd($arr);
            $json = $stdCl->toJson();
            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '135' || $cStat == '136') {
                    $xml = Complements::toAuthorize(
                        $tools->lastRequest,
                        $response
                    );
                } else {
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
