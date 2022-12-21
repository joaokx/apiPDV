<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;
class ImprimirPost extends Controller
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


         if ($nota) {
            $xml = file_get_contents($nota->parh_file);
            $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(realpath($empresa->logamarca)));
            $logo = realpath($empresa->logamarca);

            try {
                $danfe = new Danfe($xml);
                $danfe->exibirTextoFatura = false;
                $danfe->exibirPIS = false;
                $danfe->exibirIcmsInterestadual = false;
                $danfe->exibirValorTributos = false;
                $danfe->descProdInfoComplemento = false;
                $danfe->setOcultarUnidadeTributavel(true);
                $danfe->obsContShow(false);
                $danfe->printParameters(
                    $orientacao = 'P',
                    $papel = 'A4',
                    $margSup = 2,
                    $margEsq = 2
                );
                $danfe->logoParameters(
                    $logo,
                    $logoAlign = 'C',
                    $mode_bw = false
                );
                $danfe->setDefaultFont($font = 'times');
                $danfe->setDefaultDecimalPlaces(4);
                $danfe->debugMode(false);
                $danfe->creditsIntegratorFooter(
                    'WEBNFe Sistemas - http://www.webenf.com.br'
                );
                $pdf = $danfe->render($logo);
                header('Content-Type: application/pdf');
                echo $pdf;
            } catch (InvalidArgumentException $e) {
                echo 'Ocorreu um erro durante o processamento :' .
                    $e->getMessage();
            }
        }else{
        return 'NFe não existe';
        }
    }
}
