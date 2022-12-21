<?php

namespace App\Http\Controllers;

use NFePHP\Common\Certificate;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use stdClass;

class GeraXMLNFe extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => 2,
            'razaosocial' => 'Isol - LTDA',
            'cnpj' => '05333948000182', // PRECISA SER VÁLIDO
            'ie' => '647416022117', // PRECISA SER VÁLIDO
            'siglaUF' => 'SP',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
        ];

        $certificadoDigital = file_get_contents(
            'certificados/certificado_000001011265929.pfx'
        );
        $tools = new Tools(
            json_encode($config),
            Certificate::readPfx($certificadoDigital, '12345678')
        );

        $nfe = new Make();
        $std = new stdClass();
        $std->versao = '4.00';
        $std->Id = null;
        $std->pk_nItem = '';
        $nfe->taginfNFe($std);

        ########################## IDE ##########################
        $stdIde = new stdClass();
        $stdIde->cUF = 29;
        $stdIde->cNF = rand(11111111, 99999999);
        $stdIde->natOp = 'VENDA';
        $stdIde->mod = 55; //Modelo do Documento Fiscal
        $stdIde->serie = 102;
        $stdIde->nNF = 1; //Código Numérico que compõe a Chave de Acesso
        $stdIde->dhEmi = date('Y-m-d') . 'T' . date('H:i:s') . '-03:00';
        $std->dhSaiEnt = date('Y-m-d') . 'T' . date('H:i:s') . '-03:00';
        $stdIde->tpNF = 1;
        $stdIde->idDest = 1;
        $stdIde->cMunFG = 2925303; //Código do Município dO ibge
        $stdIde->tpImp = 1;
        $stdIde->tpEmis = 1; //Número do Documento Fiscal
        $stdIde->cDV = 2; //Dígito Verificador da Chave de Acesso
        $stdIde->tpAmb = 2;
        $stdIde->finNFe = 1; //Se NF-e complementar (finNFe=2):– Não informado NF referenciada (NF modelo 1 ou NF-e)
        $stdIde->indFinal = 1;
        $stdIde->indPres = 0;
        $stdIde->indIntermed = null;
        $stdIde->procEmi = 0;
        $stdIde->verProc = 0; //Identificador da versão do processo de emissão (informar a versão do aplicativo emissor de NF-e).
        $tagide = $nfe->tagide($stdIde);
        ########################## IDE ##########################

        ########################## EMITENTE##########################
        $stdEmit = new stdClass();
        $stdEmit->xNome = 'Isol - LTDA';
        $stdEmit->xFant = 'kapille';
        $stdEmit->IE = '041435715 ';
        $stdEmit->IEST = null;
        $stdEmit->IM = '';
        $stdEmit->CNAE = '47.89-0-99';
        $stdEmit->CRT = '3';
        $stdEmit->CNPJ = '05333948000182'; //indicar apenas um CNPJ ou CPF
        $tagemit = $nfe->tagemit($stdEmit);

        $stdEnderEmit = new stdClass();
        $stdEnderEmit->xLgr = 'Av. Danilo Galeazzi';
        $stdEnderEmit->nro = '3421';
        $stdEnderEmit->xCpl = 'Loja 01';
        $stdEnderEmit->xBairro = 'Jardim Joao Paulo II';
        $stdEnderEmit->cMun = '2925303';
        $stdEnderEmit->xMun = 'São José do Rio Preto';
        $stdEnderEmit->UF = 'SP';
        $stdEnderEmit->CEP = '15051000';
        $stdEnderEmit->cPais = '1058';
        $stdEnderEmit->xPais = 'Brasil';
        $stdEnderEmit->fone = '173223-5055';
        $tagenderEmit = $nfe->tagenderEmit($stdEnderEmit);
        ########################## EMITENTE##########################

        ########################## DESTINATARIO##########################
        $stdDest = new stdClass();
        $stdDest->xNome = 'Rubens dos Santos';
        $stdDest->indIEDest = 9;
        $stdDest->IE = '';
        $stdDest->ISUF = '';
        $stdDest->IM = '';
        $stdDest->email = 'salvadorbba@gmail.com';
        // $stdDest->CNPJ = "57219214553";
        $stdDest->CPF = '57219214553';
        $tagdest = $nfe->tagdest($stdDest);

        $stdEndereDest = new stdClass();
        $stdEndereDest->xLgr = 'Rua teste';
        $stdEndereDest->nro = '100';
        $stdEndereDest->xCpl = 'Loja35';
        $stdEndereDest->xBairro = 'Centro';
        $stdEndereDest->cMun = '2925303';
        $stdEndereDest->xMun = 'Porto Seguro';
        $stdEndereDest->UF = 'BA';
        $stdEndereDest->CEP = '45810000';
        $stdEndereDest->cPais = '1058';
        $stdEndereDest->xPais = 'Brasil';
        $stdEndereDest->fone = '73988347818';
        $nfe->tagenderDest($stdEndereDest);

        ########################## DESTINATARIO##########################

        ########################## PRODUTOS ##########################

        $valor = 306.8;
        $stdProd = new stdClass();
        $stdProd->item = 1;
        $stdProd->cEAN = '7896745800660';
        $stdProd->cEANTrib = '7896745800660';
        $stdProd->cProd = '1057';
        $stdProd->xProd = 'GENFLOC CLARIFICANTE 01LT';
        $stdProd->NCM = '38089419';
        $stdProd->CFOP = '5102';
        $stdProd->uCom = 'UN';
        $stdProd->uTrib = 'UN';
        $stdProd->qCom = 1.0;
        $std = new stdClass();
        $std->CNPJ = null; //indicar um CNPJ ou CPF
        $std->CPF = '93102208568';
        $stdProd->vUnCom = number_format($valor, 2, '.', '');
        $stdProd->qTrib = 1;
        $stdProd->vUnTrib = number_format($stdProd->vUnCom, 2, '.', '');
        $stdProd->vProd = $stdProd->qTrib * $stdProd->vUnTrib;
        $stdProd->indTot = 1;
        $tagprod = $nfe->tagprod($stdProd);

        /** TRIBUTOS */
        $stdimposto = new stdClass();
        $stdimposto->item = 1;
        $stdimposto->vTotTrib = 20.93;
        $tagimposto = $nfe->tagimposto($stdimposto);

        $stdICMS = new stdClass();
        $stdICMS->item = 1; //item da NFe
        $stdICMS->orig = 0;
        $stdICMS->CST = '00';
        $stdICMS->modBC = 1;
        $stdICMS->vBC = 0.0;
        $stdICMS->pICMS = 0.0;
        $stdICMS->vICMS = 0.0;
        $ICMS = $nfe->tagICMS($stdICMS);

        $stdPIS = new stdClass();
        $stdPIS->item = 1; //item da NFe
        $stdPIS->CST = '99';
        $stdPIS->vBC = 0.0;
        $stdPIS->pPIS = 0.0;
        $stdPIS->vPIS = 0.0;
        $pis = $nfe->tagPIS($stdPIS);

        $stdCOFINS = new stdClass();
        $stdCOFINS->item = 1; //item da NFe
        $stdCOFINS->CST = '99';
        $stdCOFINS->vBC = 0.0;
        $stdCOFINS->pCOFINS = 0.0;
        $stdCOFINS->vCOFINS = 0.0;
        $COFINS = $nfe->tagCOFINS($stdCOFINS);

        $stdICMSTot = new stdClass();
        $stdICMSTot->vBC = 0.0;
        $stdICMSTot->vICMS = 0.0;
        $stdICMSTot->vProd = $stdProd->vProd;
        $stdICMSTot->vPIS = 0.0;
        $stdICMSTot->vCOFINS = 0.0;
        $stdICMSTot->vNF = number_format($stdProd->vProd, 2, '.', '');
        $stdICMSTot->vTotTrib = 0.0;
        $ICMSTot = $nfe->tagICMSTot($stdICMSTot);

        /** TRIBUTOS */

        ########################## PRODUTOS ##########################

        ########################## TRANSPORTES ##########################
        $stdtransp = new stdClass();
        $stdtransp->modFrete = 9;
        $trasnp = $nfe->tagtransp($stdtransp);

        ########################## TRANSPORTES ##########################

        ########################## DADOS DA FATURA ##########################

        $stdfat = new stdClass();
        $stdfat->nFat = '1736';
        $stdfat->vOrig = $stdICMSTot->vNF;
        $stdfat->vDesc = 0.0;
        $stdfat->vLiq = $stdICMSTot->vNF;
        $fat = $nfe->tagfat($stdfat);

        $stddup = new stdClass();
        $stddup->nDup = '001';
        $stddup->dVenc = '2022-06-29';
        $stddup->vDup = $stdICMSTot->vNF;
        $nfe->tagdup($stddup);

        $stdtroco = new stdClass();
        $stdtroco->vTroco = 0.0;
        $troco = $nfe->tagpag($stdtroco);

        $stdPag = new stdClass();
        $stdPag->tPag = '05';
        $stdPag->vPag = number_format($stdProd->vProd, 2, '.', '');
        //$std->indPag = 0;
        $pags = $nfe->tagdetPag($stdPag);
        ########################## DADOS DA FATURA ##########################


        $stdinfAdic = new stdClass();
        $stdinfAdic->infAdFisco = 'informacoes para o fisco';
        $stdinfAdic->infCpl = 'aula gerando xml 29/06/2022 as 07:38';
        $taginfAdic = $nfe->taginfAdic($stdinfAdic);

        $XML = $nfe->getXML();
        $Chave = $nfe->getChave();
 
        $erros = $nfe->getErrors();
        $modelo = $nfe->getModelo();

       // dd($nfe->dom);


            /**
         * usada para criar pastas 
         */
        $data_geracao_ano = date('Y');
        $data_geracao_mes = date('m');
        $data_geracao_dia = date('d');


        if ($stdIde->tpAmb == 1):
            $PastaAmbiente = 'producao';
        else:
            $PastaAmbiente = 'homologacao';
        endif;

        $path = "XML/NF-e/{$stdEmit->CNPJ}/{$PastaAmbiente}/temporaria/{$data_geracao_ano}/{$data_geracao_mes}/{$data_geracao_dia}";


        if (is_dir($path)) {

            /// nada
        } else {
            mkdir($path, 0777, true);
        }


        $Filename = $path . '/' . $Chave . '-nfe.xml';
        $response = file_put_contents($Filename, $XML);

 

 // (73)98138-2758
      
    }
}



 