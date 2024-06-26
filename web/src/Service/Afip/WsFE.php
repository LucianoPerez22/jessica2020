<?php

namespace App\Service\Afip;

#==============================================================================
use SoapClient;

define ("WSDLWSAA", dirname(__FILE__)."/wsaa.wsdl");
define ("WSDLWSW", dirname(__FILE__)."/wsfe.wsdl");
define ("WSDLWSCDC", dirname(__FILE__)."/wscdc.wsdl");
define ("WSDLWSpersonaServiceA4", dirname(__FILE__)."/wspersonaServiceA4.wsdl");
define ("WSDLWSpersonaServiceA5", dirname(__FILE__)."/wspersonaServiceA5.wsdl");
define ("URLWSAA", "https://wsaahomo.afip.gov.ar/ws/services/LoginCms");
define ("URLWSW", "https://wswhomo.afip.gov.ar/wsfev1/service.asmx");
define ("URLWSCDC", "https://wswhomo.afip.gov.ar/WSCDC/service.asmx");
define ("URLWSpersonaServiceA4", "https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA4");
define ("URLWSpersonaServiceA5", "https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5");
define ("ARBA_TEMPLATE_XML", "<CONSULTA-ALICUOTA><fechaDesde>20160801</fechaDesde><fechaHasta>20160831</fechaHasta><cantidadContribuyentes>1</cantidadContribuyentes><contribuyentes class=\"list\"><contribuyente><cuitContribuyente>20123456789</cuitContribuyente></contribuyente></contribuyentes></CONSULTA-ALICUOTA>");
# Cambiar para produccion
define ("URLWSAA_PROD", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
define ("URLWSW_PROD", "https://servicios1.afip.gov.ar/wsfev1/service.asmx");
define ("URLWSCDC_PROD", "https://servicios1.afip.gov.ar/WSCDC/service.asmx");
define ("URLWSpersonaServiceA4_PROD", "https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA4");
define ("URLWSpersonaServiceA5_PROD", "https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5");
#==============================================================================

date_default_timezone_set('America/Buenos_Aires');

class WsFE
{

    private $Token;
    private $Sign;
    public $CUIT;
    public $ErrorCode;
    public $ErrorDesc;
    public $PasswodArba;

    public $RespCAE;
    public $RespVencimiento;
    public $RespResultado;
    public $RespUltNro;

    private $client;
    private $Request;
    private $Response;
    private $urlCdc;

    private $certificado;
    private $clave;
    private $urlWsaa;

    private $currentPath;

    function __construct(){
        $this->currentPath = dirname(__FILE__)."/";
    }

    private function CreateTRA($SERVICE)
    {
        $TRA = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<loginTicketRequest version="1.0">' .
            '</loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', date('U'));
        $TRA->header->addChild('generationTime', date('c', date('U') - 60 * 5));
        $TRA->header->addChild('expirationTime', date('c', date('U') + 3600 * 12));
        $TRA->addChild('service', $SERVICE);
        $TRA->asXML($this->currentPath.'tmp/TRA.xml');
    }

    private function SignTRA($certificado, $clave)
    {
        $STATUS = openssl_pkcs7_sign($this->currentPath . "tmp/TRA.xml", $this->currentPath . "tmp/TRA.tmp", "file://".$certificado,
            array("file://".$clave, ""),
            array(),
            !PKCS7_DETACHED
        );
        if (!$STATUS) {
            exit("ERROR generating PKCS#7 signature\n");
        }
        $inf = fopen($this->currentPath . "tmp/TRA.tmp", "r");
        $i = 0;
        $CMS = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4) {
                $CMS .= $buffer;
            }
        }
        fclose($inf);
        unlink($this->currentPath . "tmp/TRA.tmp");
        return $CMS;
    }

    private function CallWSAA($CMS, $urlWsaa)
    {
        $wsaaClient = new SoapClient(WSDLWSAA, array(
            'soap_version' => SOAP_1_2,
            'location' => $urlWsaa,
            'trace' => 1,
            'exceptions' => 0
        ));
        $results = $wsaaClient->loginCms(array('in0' => $CMS));
//        file_put_contents("request-loginCms.xml", $wsaaClient->__getLastRequest());
//        file_put_contents("response-loginCms.xml", $wsaaClient->__getLastResponse());
        if (is_soap_fault($results)) {
            exit("SOAP Fault: " . $results->faultcode . "\n" . $results->faultstring . "\n");
        }
        return $results->loginCmsReturn;
    }

    private function ProcesaErrores($Errors)
    {
        if (is_array($Errors->Err)){
            $this->ErrorCode = $Errors->Err[0]->Code;
            $this->ErrorDesc = utf8_decode($Errors->Err[0]->Msg);
        } else {
            $this->ErrorCode = $Errors->Err->Code;
            $this->ErrorDesc = utf8_decode($Errors->Err->Msg);
        }
    }

    function Login($certificado, $clave, $urlWsaa, $service = "wsfe")
    {
        $this->certificado = dirname($certificado) == "." ? $this->currentPath."certificados/".$certificado : $certificado;
        $this->clave = dirname($clave) == "." ? $this->currentPath."certificados/".$clave : $clave;
        $this->urlWsaa = $urlWsaa;

        if (!$this->loadCredentials($urlWsaa, $service)) {
            ini_set("soap.wsdl_cache_enabled", "1");
            if (!file_exists($this->certificado)) {
                exit("Failed to open " . $certificado . "\n");
            }
            if (!file_exists($this->clave)) {
                exit("Failed to open " . $clave . "\n");
            }
            if (!file_exists(WSDLWSAA)) {
                exit("Failed to open " . WSDLWSAA . "\n");
            }
            $SERVICE = $service;
            $this->CreateTRA($SERVICE);
            $CMS = $this->SignTRA($this->certificado, $this->clave);
            $TA = simplexml_load_string($this->CallWSAA($CMS, $urlWsaa));


            $this->Token = $TA->credentials->token;
            $this->Sign = $TA->credentials->sign;
            $this->saveCredentials($urlWsaa, $SERVICE);
        }
        return true;
    }

    function loadCredentials($urlWsaa, $service){

        $filename = $this->currentPath."cache/".$this->CUIT.".cache";

        if (file_exists($filename)){
            $key = hash("md5", $urlWsaa.$service);
            $fcontent = file_get_contents($filename);
            if ($fcontent){
                $config = json_decode($fcontent);
                if (isset($config->$key)) {
                    if ((time() - $config->$key->timeStamp) / 3600 < 10) {
                        $this->Token = $config->$key->token;
                        $this->Sign = $config->$key->sign;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function saveCredentials($urlWsaa, $service){

        $filename = $this->currentPath."cache/".$this->CUIT.".cache";
        $key = hash("md5", $urlWsaa.$service);
        if (file_exists($filename)) {
            $fcontent = file_get_contents($filename);
        } else {
            $fcontent = false;
        }

        if ($fcontent){
            $config = json_decode($fcontent);
        } else {
            $config = new \stdClass();
        }

        $config->$key = array("token"=> (string) $this->Token,
            "sign" => (string) $this->Sign,
            "timeStamp" => time());
        $file = fopen($filename, "w+");
        fwrite($file, json_encode($config));
        fclose($file);
    }

    function RecuperaLastCMP($PtoVta, $TipoComp)
    {
        $results = $this->client->FECompUltimoAutorizado(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT),
                'PtoVta' => $PtoVta,
                'CbteTipo' => $TipoComp));
        if (isset($results->FECompUltimoAutorizadoResult->Errors)) {
            $this->procesaErrores($results->FECompUltimoAutorizadoResult->Errors);
            return false;
        } else if (is_soap_fault($results)){
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }
        $this->RespUltNro = $results->FECompUltimoAutorizadoResult->CbteNro;

        return true;
    }

    function Reset()
    {
        $this->Request = array();
        $this->Lote = array();
        return;
    }

    function AgregaFactura($Concepto, $DocTipo, $DocNro, $CbteDesde, $CbteHasta, $CbteFch, $ImpTotal, $ImpTotalConc, $ImpNeto,
                           $ImpOpEx, $FchServDesde, $FchServHasta, $FchVtoPago, $MonId, $MonCotiz)
    {
        $Request = array();
        $Request['Concepto'] = $Concepto;
        $Request['DocTipo'] = $DocTipo;
        $Request['DocNro'] = $DocNro;
        $Request['CbteDesde'] = $CbteDesde;
        $Request['CbteHasta'] = $CbteHasta;
        $Request['CbteFch'] = $CbteFch;
        $Request['ImpTotal'] = $ImpTotal;
        $Request['ImpTotConc'] = $ImpTotalConc;
        $Request['ImpNeto'] = $ImpNeto;
        $Request['ImpOpEx'] = $ImpOpEx;
        $Request['ImpTrib'] = 0;
        $Request['ImpIVA'] = 0;
        $Request['FchServDesde'] = $FchServDesde;
        $Request['FchServHasta'] = $FchServHasta;
        $Request['FchVtoPago'] = $FchVtoPago;
        $Request['MonId'] = $MonId;
        $Request['MonCotiz'] = $MonCotiz;

        $this->Lote['FECAEDetRequest'][] = $Request;
        end($this->Lote['FECAEDetRequest']);
        $this->Request = &$this->Lote['FECAEDetRequest'][key($this->Lote['FECAEDetRequest'])];
    }

    function AgregaIVA($Id, $BaseImp, $Importe)
    {
        $AlicIva = array('Id' => $Id,
            'BaseImp' => $BaseImp,
            'Importe' => $Importe);
        if (!isset($this->Request['Iva'])) {
            $this->Request['Iva'] = array('AlicIva' => array());
        }

        $this->Request['Iva']['AlicIva'][] = $AlicIva;

        $this->Request['ImpIVA'] = 0;
        foreach ($this->Request['Iva']['AlicIva'] as $key => $value) {
            $this->Request['ImpIVA'] = $this->Request['ImpIVA'] + $value['Importe'];
        }
    }

    function AgregaTributo($Id, $Desc, $BaseImp, $Alic, $Importe)
    {
        $Tributo = array('Id' => $Id,
            'Desc' => $Desc,
            'BaseImp' => $BaseImp,
            'Alic' => $Alic,
            'Importe' => $Importe);

        if (!isset($this->Request['Tributos'])) {
            $this->Request['Tributos'] = array('Tributo' => array());
        }

        $this->Request['Tributos']['Tributo'][] = $Tributo;

        $this->Request['ImpTrib'] = 0;
        foreach ($this->Request['Tributos']['Tributo'] as $key => $value) {
            $this->Request['ImpTrib'] = $this->Request['ImpTrib'] + $value['Importe'];
        }
    }

    function AgregaCompAsoc($Tipo, $PtoVta, $Nro, $Cuit, $CbteFch)
    {
        $CbteAsoc = array('Tipo' => $Tipo,
            'PtoVta' => $PtoVta,
            'Nro' => $Nro,
            'Cuit' => $Cuit,
            'CbteFch' => $CbteFch);

        if (!isset($this->Request['CbtesAsoc'])) {
            $this->Request['CbtesAsoc'] = array('CbteAsoc' => array());
        }

        $this->Request['CbtesAsoc']['CbteAsoc'][] = $CbteAsoc;
    }

    function AgregaOpcional($Id, $Valor)
    {
        $Opcional = array('Id' => $Id,
            'Valor' => $Valor);

        if (!isset($this->Request['Opcionales'])) {
            $this->Request['Opcionales'] = array('Opcional' => array());
        }

        $this->Request['Opcionales']['Opcional'][] = $Opcional;
    }

    function Autorizar($PtoVta, $TipoComp)
    {

        $Request = array('Auth' => array(
            'Token' => $this->Token,
            'Sign' => $this->Sign,
            'Cuit' => $this->CUIT),
            'FeCAEReq' => array(
                'FeCabReq' => array(
                    'CantReg' => count($this->Lote['FECAEDetRequest']),
                    'PtoVta' => $PtoVta,
                    'CbteTipo' => $TipoComp),
                'FeDetReq' => $this->Lote
            )
        );
        $results = $this->client->FECAESolicitar($Request);
        if (isset($results->FECAESolicitarResult->Errors)) {
            $this->ProcesaErrores($results->FECAESolicitarResult->Errors);
            return;
        }
        if (is_soap_fault($results)){
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }

        $this->RespResultado = $results->FECAESolicitarResult->FeCabResp->Resultado;

        if ($this->RespResultado == "A") {
            $this->RespCAE = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
            $this->RespVencimiento = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto;
        }


        if (isset($results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones)){
            if (is_array($results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs)){
                $this->ErrorCode = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs[0]->Code;
                $this->ErrorDesc = utf8_decode($results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs[0]->Msg);
            } else {
                $this->ErrorCode = $results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code;
                $this->ErrorDesc = utf8_decode($results->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Msg);
            }
        }

        return $this->RespResultado == "A";
    }

    function CmpConsultar($TipoComp, $PtoVta, $nro, &$cbte)
    {
        $results = $this->client->FECompConsultar(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT),
                'FeCompConsReq' => array('PtoVta' => $PtoVta,
                    'CbteTipo' => $TipoComp,
                    'CbteNro' => $nro)
            )
        );
        if (isset($results->FECompConsultarResult->Errors)) {
            $this->procesaErrores($results->FECompConsultarResult->Errors);
            return false;
        }
        $cbte = $results->FECompConsultarResult->ResultGet;

        return true;
    }

    function getXMLRequest()
    {
        return $this->client->__getLastRequest();
    }

    function setURL($URL)
    {
        $context = stream_context_create(
            array(
                'ssl' => array(
                    'ciphers' => 'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
                )
            )
        );

        $this->client = new SoapClient(WSDLWSW, array(
                'soap_version'=> SOAP_1_2,
                'location' => $URL,
                'trace' => 1,
                'exceptions' => 0,
                'stream_context' => $context
            )
        );
    }

    function CAEASolicitar($Periodo, $Orden, &$CAE, &$FchVigDesde, &$FchVigHasta,
          &$FchTopeInf, &$FchProceso)
    {
        $results = $this->client->FECAEASolicitar(
            array('Auth' =>
                array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT
                ),
                'Periodo' => $Periodo,
                'Orden' => $Orden
            )
        );

        if (isset($results->FECAEASolicitarResult->Errors)) {
            $this->procesaErrores($results->FECAEASolicitarResult->Errors);
            return false;
        };

        $CAE = $results->FECAEASolicitarResult->ResultGet->CAEA;
        $FchVigDesde = $results->FECAEASolicitarResult->ResultGet->FchVigDesde;
        $FchVigHasta = $results->FECAEASolicitarResult->ResultGet->FchVigHasta;
        $FchTopeInf = $results->FECAEASolicitarResult->ResultGet->FchTopeInf;
        $FchProceso = $results->FECAEASolicitarResult->ResultGet->FchProceso;

        return true;
    }

    function CAEAConsultar($Periodo, $Orden, &$CAE, &$FchVigDesde, &$FchVigHasta,
                           &$FchTopeInf, &$FchProceso)
    {
        $results = $this->client->FECAEAConsultar(
            array('Auth' =>
                array('Token' => $this->Token,
                    'Sign' => $this->Sign,
                    'Cuit' => $this->CUIT
                ),
                'Periodo' => $Periodo,
                'Orden' => $Orden
            )
        );

        if (isset($results->FECAEAConsultarResult->Errors)) {
            $this->procesaErrores($results->FECAEAConsultarResult->Errors);
            return false;
        };
        $CAE = $results->FECAEAConsultarResult->ResultGet->CAEA;

        $FchVigDesde = $results->FECAEAConsultarResult->ResultGet->FchVigDesde;
        $FchVigHasta = $results->FECAEAConsultarResult->ResultGet->FchVigHasta;
        $FchTopeInf = $results->FECAEAConsultarResult->ResultGet->FchTopeInf;
        $FchProceso = $results->FECAEAConsultarResult->ResultGet->FchProceso;

        return true;
    }

    function CAEAInformar($ptoVenta, $CbteTipo, $CAE)
    {
        $this->Request['CAEA'] = $CAE;
        $request = array('Auth' =>
                        array('Token' => $this->Token,
                            'Sign' => $this->Sign,
                            'Cuit' => $this->CUIT
                        ),
                        'FeCAEARegInfReq' => array(
                                'FeCabReq' => array(
                                    'CantReg' => 1,
                                    'PtoVta' => $ptoVenta,
                                    'CbteTipo' => $CbteTipo
                                ),
                                'FeDetReq' => array($this->Request)
                            )

                    );

        $results = $this->client->FECAEARegInformativo($request);

        if (isset($results->FECAEARegInformativoResult->Errors)) {
            $this->procesaErrores($results->FECAEARegInformativoResult->Errors);
            return false;
        };
        return true;
    }

    function AddChecksum($Prefix, $DNI)
    {
        $DNIStr = $Prefix.$DNI;
        $Serie = 2;
        $Acc = 0;
        for ($i = strlen($DNIStr); $i > 0; $i--){
            $Acc = $Acc + intval(substr($DNIStr, $i-1,1)) * $Serie;
            if ($Serie == 7) {
                $Serie = 2;
            } else {
                $Serie = $Serie + 1;
            }
        }
        $Modulo = 11 - ($Acc % 11);
        if ($Modulo == 1) {
            $Modulo = 0;
        }
        return $DNIStr.$Modulo;
    }

    function InternoConsultarConstancia($CUIT, &$Constancia, $URL){

        if ($this->Login($this->certificado, $this->clave, $this->urlWsaa, "ws_sr_constancia_inscripcion")) {
            $context = stream_context_create(
                array(
                    'ssl' => array(
                        'ciphers' => 'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
                    )
                )
            );

            $consultacuit = new SoapClient(WSDLWSpersonaServiceA5, array(
                    'soap_version' => SOAP_1_1,
                    'location' => $URL,
                    'trace' => 1,
                    'exceptions' => 0,
                    'stream_context' => $context
                )
            );

            $Request = array(
                'token' => $this->Token,
                'sign' => $this->Sign,
                'cuitRepresentada' => $this->CUIT,
                'idPersona' => $CUIT
            );

            $results = $consultacuit->getPersona($Request);

            if (is_soap_fault($results)) {
                $this->ErrorCode = -1;
                $this->ErrorDesc = $results->faultstring;
                return false;
            }

            $Constancia = $results->personaReturn;

            return true;
        }



        return false;
    }

    function InternoConsultarCUIT($CUIT, &$DatosPersona,$URL){
        $this->ErrorCode = 0;
        $this->ErrorDesc = '';

//        $context = stream_context_create(
//            array(
//                'ssl' => array(
//                    'ciphers' => 'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
//                )
//            )
//        );
//
//        $consultacuit = new SoapClient(WSDLWSpersonaServiceA5, array(
//                'soap_version'=> SOAP_1_1,
//                'location' => $URL,
//                'trace' => 1,
//                'exceptions' => 0,
//                'stream_context' => $context
//            )
//        );
//
//        $Request = array(
//            'token' => $this->Token,
//            'sign' => $this->Sign,
//            'cuitRepresentada' => $this->CUIT,
//            'idPersona' => $CUIT
//        );
//
//        $results = $consultacuit->getPersona($Request);
//
//        if (is_soap_fault($results)){
//            $this->ErrorCode = -1;
//            $this->ErrorDesc = $results->faultstring;
//            return false;
//        }
//
//        $DatosPersona = $results->personaReturn->persona;

        if (!$this->InternoConsultarConstancia($CUIT, $constancia, $URL))
        {
            return false;
        } else {
            $DatosPersona = $constancia;
            $DatosPersona->Observaciones = array();
            if (isset($DatosPersona->errorConstancia) || isset($DatosPersona->errorMonotributo) or isset($DatosPersona->errorRegimenGeneral)) {

                if (isset($DatosPersona->errorConstancia))
                  array_push($DatosPersona->Observaciones, $DatosPersona->errorConstancia->error);
                if (isset($DatosPersona->errorMonotributo))
                    array_push($DatosPersona->Observaciones, $DatosPersona->errorMonotributo->error);
                if (isset($DatosPersona->errorRegimenGeneral))
                    array_push($DatosPersona->Observaciones, $DatosPersona->errorRegimenGeneral->error);
            };
        }


        $DatosPersona->CondicionIVADesc = 'Consumidor Final';
        if (isset($DatosPersona->datosMonotributo)) {
            $DatosPersona->CondicionIVADesc = 'Monotributo';
        } else if (isset($DatosPersona->datosRegimenGeneral)) {
            $impuestos = $DatosPersona->datosRegimenGeneral->impuesto;
            if (isset($impuestos)) {
                foreach ($impuestos as $impuesto) {
                    if ($impuesto->idImpuesto == 30) {
                        $DatosPersona->CondicionIVADesc = 'Responsable Inscripto';
                        break;
                    } elseif ($impuesto->idImpuesto == 32) {
                        $DatosPersona->CondicionIVADesc = 'Exento';
                        break;
                    }
                }
            }
        }
        return true;
    }

    function ConsultarCUIT($CUIT, &$DatosPersona,$URL)
    {
        $CuitStr = $CUIT;
        if (strlen($CuitStr) < 11) {

            if (strlen($CuitStr) < 8) {
               for ($i = strlen($CuitStr) + 1; $i < 9; $i++){
                    $CuitStr = '0' + $CuitStr;
               }
            }
            $Result = $this->InternoConsultarCUIT($this->AddChecksum(20, $CuitStr), $DatosPersona, $URL);
            if (!$Result)
                $Result = $this->InternoConsultarCUIT($this->AddChecksum(27, $CuitStr), $DatosPersona, $URL);
        if (!$Result)
          $Result = $this->InternoConsultarCUIT($this->AddChecksum(23, $CuitStr), $DatosPersona, $URL);
        if (!$Result)
          $Result = $this->InternoConsultarCUIT($this->AddChecksum(24, $CuitStr), $DatosPersona, $URL);
      }
      else {
          $Result = $this->InternoConsultarCUIT($CuitStr, $DatosPersona, $URL);
      }
        return $Result;
    }
    

    function ConsultaARBA($cuitConsulta, $fechaDesde, $fechaHasta, &$alicuotas){
        $target_url = 'http://dfe.arba.gov.ar/DomicilioElectronico/SeguridadCliente/dfeServicioConsulta.do';
        //This needs to be the full path to the file you want to send.
        $xmlFile = new SimpleXMLElement(ARBA_TEMPLATE_XML);
        $xmlFile->contribuyentes->contribuyente->cuitContribuyente = $cuitConsulta;
        $xmlFile->fechaDesde = $fechaDesde;
        $xmlFile->fechaHasta = $fechaHasta;
        $domxml = dom_import_simplexml($xmlFile);
        $fileContent = $domxml->ownerDocument->saveXML($domxml->ownerDocument->documentElement);
        $md5 = md5($fileContent);
        $filename = "DFEServicioConsulta_".$md5.".xml";
        file_put_contents($filename, $fileContent);
        $full_filename = realpath("./".$filename);

        if (function_exists('curl_file_create')) { // php 5.6+
            $cFile = curl_file_create($full_filename);
        } else { //
            $cFile = '@'.$full_filename;
        }
        $post = array('user' => $this->CUIT,'password'=> $this->PasswodArba, 'file' => $cFile);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$target_url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result=curl_exec ($ch);
        $response = new SimpleXMLElement($result);
        curl_close ($ch);
        unlink($filename);
        if ($response->getName() == "DFEError" || $response->getName() == "ERROR"){
            $this->ErrorCode = $response->codigoError;
            $this->ErrorDesc = $response->mensajeError;
            return false;
        } else {
            $alicuotas = new stdClass();
            $alicuotas->percepcion = floatval(str_replace(",", ".", $response->contribuyentes->contribuyente->alicuotaPercepcion));
            $alicuotas->retencion = floatval(str_replace(",", ".", $response->contribuyentes->contribuyente->alicuotaRetencion));
            return true;
        }
    }

    function ComprobanteConstatar($CbteModo, $CuitEmisor, $PtoVta, $CbteTipo, $CbteNro, $CbteFch, $ImpTotal,
                                  $CodAutorizacion, $DocTipoReceptor, $DocNroReceptor, &$FchProceso){

        $this->ErrorCode = 0;
        $this->ErrorDesc = '';

        $context = stream_context_create(
            array(
                'ssl' => array(
                    'ciphers' => 'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5',
                )
            )
        );

        $clientConstatar = new SoapClient(WSDLWSCDC, array(
                'soap_version'=> SOAP_1_2,
                'location' => URLWSCDC,
                'trace' => 1,
                'exceptions' => 0,
                'stream_context' => $context
            )
        );

        $Request = array('Auth' => array(
            'Token' => $this->Token,
            'Sign' => $this->Sign,
            'Cuit' => $this->CUIT),
            'CmpReq' => array(
                'CbteModo' => $CbteModo,
                'CuitEmisor' => $CuitEmisor,
                'PtoVta' => $PtoVta,
                'CbteTipo' => $CbteTipo,
                'CbteNro' => $CbteNro,
                'CbteFch' => $CbteFch,
                'ImpTotal' => $ImpTotal,
                'CodAutorizacion' => $CodAutorizacion,
                'DocTipoReceptor' => $DocTipoReceptor,
                'DocNroReceptor' => $DocNroReceptor
            )
        );

        $results = $clientConstatar->ComprobanteConstatar($Request);
        if (isset($results->ComprobanteConstatarResult->Errors)) {
            $this->ProcesaErrores($results->ComprobanteConstatarResult->Errors);
            return;
        }
        if (is_soap_fault($results)){
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }

        $RespResultado = $results->ComprobanteConstatarResult->Resultado;

        if (isset($results->ComprobanteConstatarResult->Observaciones)){
            if (is_array($results->ComprobanteConstatarResult->Observaciones->Obs)){
                $this->ErrorCode = $results->ComprobanteConstatarResult->Observaciones->Obs[0]->Code;
                $this->ErrorDesc = utf8_decode($results->ComprobanteConstatarResult->Observaciones->Obs[0]->Msg);
            } else {
                $this->ErrorCode = $results->ComprobanteConstatarResult->Observaciones->Obs->Code;
                $this->ErrorDesc = utf8_decode($results->ComprobanteConstatarResult->Observaciones->Obs->Msg);
            }
        }

        if ($RespResultado == "A"){
            $FchProceso = sprintf("%s-%s-%s %s:%s:%s",
                substr($results->ComprobanteConstatarResult->FchProceso, 0, 4),
                substr($results->ComprobanteConstatarResult->FchProceso, 4, 2),
                substr($results->ComprobanteConstatarResult->FchProceso, 6, 2),
                substr($results->ComprobanteConstatarResult->FchProceso, 8, 2),
                substr($results->ComprobanteConstatarResult->FchProceso, 10, 2),
                substr($results->ComprobanteConstatarResult->FchProceso, 12, 2));
        }

        return $RespResultado == "A";

    }

    function setUrlCdc($url){
        $this->urlCdc = $url;
    }

    function ConsultarPtosVta(&$ptosVta)
    {
        $results = $this->client->FEParamGetPtosVenta(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT)
            )
        );
        if (isset($results->FEParamGetPtosVentaResult->Errors)) {
            $this->procesaErrores($results->FEParamGetPtosVentaResult->Errors);
            return false;
        }
        $ptosVta = $results->FEParamGetPtosVentaResult->ResultGet;

        return true;
    }

}


?>