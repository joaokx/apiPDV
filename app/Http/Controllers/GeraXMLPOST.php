<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Make;
use stdClass;

date_default_timezone_set('America/Bahia');

class GeraXMLPOST extends Controller
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

            $nfe = new Make();
            $std = new stdClass();
            $std->versao = '4.00';
            $std->Id = null;
            $std->pk_nItem = '';
            $nfe->taginfNFe($std);

            $stdIde = new stdClass();
            $stdIde->cUF = $empresa->cUF;
            $stdIde->cNF = rand(11111111, 99999999);
            $stdIde->natOp = 'VENDA';
            $stdIde->mod = 55; //Modelo do Documento Fiscal
            $stdIde->serie = $nota->ide_serie;
            $stdIde->nNF = $nota->numero_nfe; //Código Numérico que compõe a Chave de Acesso
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

            $stdEmit = new stdClass();
            $stdEmit->xNome = $empresa->razao_social;
            $stdEmit->xFant = $empresa->nome_fantasia;
            $stdEmit->IE = $empresa->ie;
            $stdEmit->IEST = null;
            $stdEmit->IM = '';
            $stdEmit->CNAE = $empresa->CNAE;
            $stdEmit->CRT = $empresa->CRT_ID;
            $stdEmit->CNPJ = $cpf_cnpj; //indicar apenas um CNPJ ou CPF
            $tagemit = $nfe->tagemit($stdEmit);

            $telefone = str_replace($antes, $depos, $empresa->fone);
            $CEP = str_replace($antes, $depos, $empresa->cep);

            $stdEnderEmit = new stdClass();
            $stdEnderEmit->xLgr = $empresa->logradouro;
            $stdEnderEmit->nro = $empresa->numero;
            $stdEnderEmit->xCpl = $empresa->emitentexCpl;
            $stdEnderEmit->xBairro = $empresa->bairro;
            $stdEnderEmit->cMun = $empresa->ibge;
            $stdEnderEmit->xMun = $empresa->municipio;
            $stdEnderEmit->UF = $empresa->UF;
            $stdEnderEmit->CEP = $CEP;
            $stdEnderEmit->cPais = '1058';
            $stdEnderEmit->xPais = 'Brasil';
            $stdEnderEmit->fone = $telefone;
            $tagenderEmit = $nfe->tagenderEmit($stdEnderEmit);

            /// SEFAZ Bahia 13.937.073/0001-56
            $std = new stdClass();
            $std->CNPJ = null; //indicar um CNPJ ou CPF
            $std->CPF = '93102208568';
            $tagautXML = $nfe->tagautXML($std);
            /** DADOS DO CONTADOR */

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

            $stdtransp = new stdClass();
            $stdtransp->modFrete = 9;
            $trasnp = $nfe->tagtransp($stdtransp);

            $stdfat = new stdClass();
            $stdfat->nFat = '1736';
            $stdfat->vOrig = $stdICMSTot->vNF;
            $stdfat->vDesc = 0.0;
            $stdfat->vLiq = $stdICMSTot->vNF;

            $fat = $nfe->tagfat($stdfat);

            $std = new stdClass();
            $std->nDup = '001';
            $std->dVenc = '2022-12-22';
            $std->vDup = $stdICMSTot->vNF;

            $nfe->tagdup($std);
            $stdtroco = new stdClass();
            $stdtroco->vTroco = 0.0;
            $troco = $nfe->tagpag($stdtroco);

            $stdPag = new stdClass();
            $stdPag->tPag = '05';
            $stdPag->vPag = number_format($stdProd->vProd, 2, '.', '');
            //$std->indPag = 0;
            $pags = $nfe->tagdetPag($stdPag);

            $stdinfAdic = new stdClass();
            $stdinfAdic->infAdFisco = 'informacoes para o fisco';
            $stdinfAdic->infCpl = 'aula gerando xml 24/01/2022 as 08:26';
            $taginfAdic = $nfe->taginfAdic($stdinfAdic);

            $XML = $nfe->getXML();
            $Chave = $nfe->getChave();

            $erros = $nfe->getErrors();
            $modelo = $nfe->getModelo();

            /**
             * Grava pasta temp
             */
            $data_geracao_ano = date('Y');
            $data_geracao_mes = date('m');
            $data_geracao_dia = date('d');

            if ($stdIde->tpAmb == 1):
                $PastaAmbiente = 'producao';
            else:
                $PastaAmbiente = 'homologacao';
            endif;

            #################################GERANDO XML E SALVANDO NA  PASTA########################################

            $path = "XML/NF-e/{$stdEmit->CNPJ}/{$PastaAmbiente}/temporaria/{$data_geracao_ano}/{$data_geracao_mes}/{$data_geracao_dia}";
            if (is_dir($path)) {
            } else {
                mkdir($path, 0777, true);
            }
            $Filename = $path . '/' . $Chave . '-nfe.xml';
            $response = file_put_contents($Filename, $XML);
            #################################GERANDO XML E SALVANDO NA  PASTA########################################

            #################################ASSINADO XML E SALVANDO NA  PASTA########################################
            $response_assinado = $tools->signNFe(file_get_contents($Filename));
            $path_assinadas = "XML/NF-e/{$stdEmit->CNPJ}/{$PastaAmbiente}/assinadas/{$data_geracao_ano}/{$data_geracao_mes}/{$data_geracao_dia}";
            $caminho = $path_assinadas . '/' . $Chave . '-nfe.xml';
            if (is_dir($path_assinadas)) {
            } else {
                mkdir($path_assinadas, 0777, true);
            }
            $resp = file_put_contents($caminho, $response_assinado);
            #################################ASSINADO XML E SALVANDO NA  PASTA########################################

            #################################PROTOCOLANDO XML########################################
            try {
                $idLote = str_pad(100, 15, '0', STR_PAD_LEFT); // Identificador do lote
                $resp = $tools->sefazEnviaLote([$response_assinado], $idLote);

                $st = new Standardize();
                $std = $st->toStd($resp);

                if ($std->cStat != 103) {
                    //erro registrar e voltar
                    exit("[$std->cStat] $std->xMotivo");
                }
                $recibo = $std->infRec->nRec; // Vamos usar a variável $recibo para consultar o status da nota
            } catch (Exception $e) {
                //aqui você trata possiveis exceptions do envio
                exit($e->getMessage());
            }
            #################################PROTOCOLANDO XML########################################

            #################################VERIFICA O RECIBO########################################
            try {
                $protocolo = $tools->sefazConsultaRecibo($recibo);
            } catch (Exception $e) {
                //aqui você trata possíveis exceptions da consulta
                exit($e->getMessage());
            }
            $request = $response_assinado;
            $response = $protocolo;
            #################################VERIFICA O RECIBO########################################

            /** TRANSMITI PARA SEFAZ */
            // echo $response_assinado;
            try {
                $xml_autorizado = Complements::toAuthorize($request, $response);
                $path_autorizadas = "XML/NF-e/{$stdEmit->CNPJ}/{$PastaAmbiente}/autorizadas/{$data_geracao_ano}/{$data_geracao_mes}/{$data_geracao_dia}";
                $caminho_aut = $path_autorizadas . '/' . $Chave . '-nfe.xml';
                if (is_dir($path_autorizadas)) {
                } else {
                    mkdir($path_autorizadas, 0777, true);
                }
                file_put_contents($caminho_aut, $xml_autorizado);

                $resp = $stdCl = new Standardize($xml_autorizado);
                $std = $stdCl->toStd();
                $arr = $stdCl->toArray();


                $retornoXML = $arr['protNFe']['infProt'];

                $affected = DB::table('nfe_temp')
                    ->where('id', $nota->id)
                    ->update([
                        'ide_Id' => $Chave,
                        'path_xml' => $path_autorizadas,
                        'parh_file' => $caminho_aut,
                        'status_id' => 5,
                        'xMotivo' => $retornoXML['xMotivo'],
                        'digVal' => $retornoXML['digVal'],
                        'dhRecbto' => $retornoXML['dhRecbto'],
                        'nProt' => $retornoXML['nProt'],
                        'cStat' => $retornoXML['cStat'],
                    ]);
                     return 'Nota Atualizada  com sucesso!';

            } catch (Exception $e) {
                //reporta erro  na autorização
                echo 'Erro: ' . $e->getMessage();
            }

    }
}
