<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapFake;
use NFePHP\NFe\Common\FakePretty;
use NFePHP\NFe\Common\Standardize;

class CancelarPost extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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

        try {
            $chave = $nota->ide_Id;
            $justificativa = $request->justificativa;
            $nProt = $nota->nProt;
            $response = $tools->sefazCancela($chave, $justificativa, $nProt);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();

            $retornoXML = $arr['retEvento']['infEvento'];



            $affected = DB::table('nfe_temp')
                ->where('id', $nota->id)
                ->update([
                    'status_id' => 10,
                    'xMotivo' => $retornoXML['xMotivo'],
                    'xEvento' => $retornoXML['xEvento'],
                    'dhRegEvento' => $retornoXML['dhRegEvento'],
                    'nProt' => $retornoXML['nProt'],
                    'cStat' => $retornoXML['cStat'],
                ]);
            return 'Cancelameento realizado  com sucesso!';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
