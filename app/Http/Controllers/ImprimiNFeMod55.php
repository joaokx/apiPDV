<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;

class ImprimiNFeMod55 extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $logo ='';

$xml = file_get_contents('XML/NF-e/42318190000130/homologacao/autorizadas/2022/01/24/29220142318190000130551020000000031521372739-nfe.xml');
//$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(realpath(__DIR__ . '/../images/tulipas.png')));
 //$logo = realpath(__DIR__ . '/../images/tulipas.png');

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
    $danfe->logoParameters($logo, $logoAlign = 'C', $mode_bw = false);
    $danfe->setDefaultFont($font = 'times');
    $danfe->setDefaultDecimalPlaces(4);
    $danfe->debugMode(false);
    $danfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
    //$danfe->epec('891180004131899', '14/08/2018 11:24:45'); //marca como autorizada por EPEC

    // Caso queira mudar a configuracao padrao de impressao
    /*  $this->printParameters( $orientacao = '', $papel = 'A4', $margSup = 2, $margEsq = 2 ); */
    // Caso queira sempre ocultar a unidade tributável
    /*  $this->setOcultarUnidadeTributavel(true); */
    //Informe o numero DPEC
    /*  $danfe->depecNumber('123456789'); */
    //Configura a posicao da logo
    /*  $danfe->logoParameters($logo, 'C', false);  */
    //Gera o PDF
    $pdf = $danfe->render($logo);
    header('Content-Type: application/pdf');
    echo $pdf;
} catch (InvalidArgumentException $e) {
    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
}

    }

}
