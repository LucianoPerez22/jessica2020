<?php
namespace App\Service\Afip;

#==============================================================================
define ("WSDLWSAA", dirname(__FILE__)."/wsaa.wsdl");
define ("WSDLWSW", dirname(__FILE__)."/wsmtxca.wsdl");
define ("URLWSAA", "https://wsaahomo.afip.gov.ar/ws/services/LoginCms");
define ("URLWSW", "https://fwshomo.afip.gov.ar/wsmtxca/services/MTXCAService");
# Cambiar para produccion
#define ("URLWSAA", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
#define ("URLWSW", "https://serviciosjava.afip.gob.ar/wsmtxca/services/MTXCAService");
#==============================================================================

date_default_timezone_set('America/Buenos_Aires');

class WsMTXCA
{

    private $Token;
    private $Sign;
    public $CUIT;
    public $ErrorCode;
    public $ErrorDesc;

    public $RespCAE;
    public $RespVencimiento;
    public $RespResultado;
    public $RespUltNro;

    private $client;
    private $Request;
    private $Response;

    private $currentPath;

    function __construct(){
        $this->currentPath = dirname(__FILE__)."/";
    }

    private function CreateTRA($SERVICE)
    {
        $TRA = new SimpleXMLElement(
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
        if (is_array($Errors->codigoDescripcion)) {
            $this->ErrorCode = utf8_decode($Errors->codigoDescripcion[0]->codigo);
            $this->ErrorDesc = utf8_decode($Errors->codigoDescripcion[0]->descripcion);
        } else {
            $this->ErrorCode = utf8_decode($Errors->codigoDescripcion->codigo);
            $this->ErrorDesc = utf8_decode($Errors->codigoDescripcion->descripcion);
        }
    }

    function Login($certificado, $clave, $urlWsaa)
    {
        $this->certificado = dirname($certificado) == "." ? $this->currentPath."certificados/".$certificado : $certificado;
        $this->clave = dirname($clave) == "." ? $this->currentPath."certificados/".$clave : $clave;
        $this->urlWsaa = $urlWsaa;

        if (!$this->loadCredentials($urlWsaa, "wsmtxca")) {
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
            $SERVICE = "wsmtxca";
            $this->CreateTRA($SERVICE);
            $CMS = $this->SignTRA($this->certificado, $this->clave);
            $TA = simplexml_load_string($this->CallWSAA($CMS, $urlWsaa));


            $this->Token = $TA->credentials->token;
            $this->Sign = $TA->credentials->sign;
            $this->saveCredentials($urlWsaa, $SERVICE);
        }
        return true;
    }

    function loadCredentials($urlWsaa, $service)
    {
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

    function AgregaFactura($codigoTipoComprobante, $numeroPuntoVenta, $numeroComprobante, $fechaEmision, $codigoTipoDocumento,
                           $numeroDocumento, $importeGravado, $importeNoGravado, $importeExento, $importeSubtotal, $importeOtrosTributos,
                           $importeTotal, $codigoMoneda, $cotizacionMoneda, $observaciones, $codigoConcepto, $fechaServicioDesde,
                           $fechaServicioHasta, $fechaVencimientoPago)
    {
        $this->Request['codigoTipoComprobante'] = $codigoTipoComprobante;
        $this->Request['numeroPuntoVenta'] = $numeroPuntoVenta;
        $this->Request['numeroComprobante'] = $numeroComprobante;
        $this->Request['fechaEmision'] = $fechaEmision;
        $this->Request['codigoTipoDocumento'] = $codigoTipoDocumento;
        $this->Request['numeroDocumento'] = $numeroDocumento;
        $this->Request['importeGravado'] = $importeGravado;
        $this->Request['importeNoGravado'] = $importeNoGravado;
        $this->Request['importeExento'] = $importeExento;
        $this->Request['importeSubtotal'] = $importeSubtotal;
        if ($importeOtrosTributos > 0) {
            $this->Request['importeOtrosTributos'] = $importeOtrosTributos;
        }
        $this->Request['importeTotal'] = $importeTotal;
        $this->Request['codigoMoneda'] = $codigoMoneda;
        $this->Request['cotizacionMoneda'] = $cotizacionMoneda;
        $this->Request['observaciones'] = $observaciones;
        $this->Request['codigoConcepto'] = $codigoConcepto;
        if ($fechaServicioDesde != "") {
            $this->Request['fechaServicioDesde'] = $fechaServicioDesde;
        };
        if ($fechaServicioHasta != "") {
            $this->Request['fechaServicioHasta'] = $fechaServicioHasta;
        };
        if ($fechaVencimientoPago != "") {
            $this->Request['fechaVencimientoPago'] = $fechaVencimientoPago;
        };
    }

    function saveCredentials($urlWsaa, $service)
    {
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
            $config = new stdClass();
        }

        $config->$key = array("token"=> (string) $this->Token,
            "sign" => (string) $this->Sign,
            "timeStamp" => time());
        $file = fopen($filename, "w+");
        fwrite($file, json_encode($config));
        fclose($file);
    }
    function RecuperaLastCMP($codigoTipoComprobante, $numeroPuntoVenta)
    {
        $results = $this->client->consultarUltimoComprobanteAutorizado(
            array('authRequest' => array('token' => $this->Token,
                'sign' => $this->Sign,
                'cuitRepresentada' => $this->CUIT),
                'consultaUltimoComprobanteAutorizadoRequest' => array(
                    'codigoTipoComprobante' => $codigoTipoComprobante,
                    'numeroPuntoVenta' => $numeroPuntoVenta)
            )
        );
        if (isset($results->arrayErrores)) {
            $this->procesaErrores($results->arrayErrores);
            return false;
        } else if (is_soap_fault($results)) {
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }
        $this->RespUltNro = $results->numeroComprobante;

        return true;

    }

    function Reset()
    {
        $this->Request = array();
        return;
    }

    function AgregaIVA($codigo, $importe)
    {
        $AlicIva = array('codigo' => $codigo,
            'importe' => $importe);

        if (!isset($this->Request['arraySubtotalesIVA'])) {
            $this->Request['arraySubtotalesIVA'] = array('subtotalIVA' => array());
        }

        $this->Request['arraySubtotalesIVA']['subtotalIVA'][] = $AlicIva;

//        $this->Request['importeGravado'] = 0;
//        foreach ($this->Request['arraySubtotalesIVA']['subtotalIVA'] as $key => $value) {
//            $this->Request['importeGravado'] = $this->Request['importeGravado'] + $value['importe'];
//        }
    }

    function AgregaItem($unidadesMtx, $codigoMtx, $codigo, $descripcion, $cantidad,$codigoUnidadMedida, $precioUnitario,
          $importeBonificacion, $codigoCondicionIVA, $importeIVA, $importeItem){

        if (in_array($this->Request['codigoTipoComprobante'], array(6, 7, 8))){
            $Item = array(
                'unidadesMtx' => $unidadesMtx,
                'codigoMtx' => $codigoMtx,
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'cantidad' => $cantidad,
                'codigoUnidadMedida' => $codigoUnidadMedida,
                'precioUnitario' => $precioUnitario,
                'importeBonificacion' => $importeBonificacion,
                'codigoCondicionIVA' => $codigoCondicionIVA,
                'importeIVA' => $importeIVA,
                'importeItem' => $importeItem
            );
        } else {
            $Item = array(
                'unidadesMtx' => $unidadesMtx,
                'codigoMtx' => $codigoMtx,
                'codigo' => $codigo,
                'descripcion' => $descripcion,
                'cantidad' => $cantidad,
                'codigoUnidadMedida' => $codigoUnidadMedida,
                'precioUnitario' => $precioUnitario,
                'importeBonificacion' => $importeBonificacion,
                'codigoCondicionIVA' => $codigoCondicionIVA,
                'importeIVA' => $importeIVA,
                'importeItem' => $importeItem
            );
        }
        if (!isset($this->Request['arrayItems'])) {
            $this->Request['arrayItems'] = array('item' => array());
        }

        $this->Request['arrayItems']['item'][] = $Item;
    }

    function AgregaTributo($codigo, $descripcion, $baseImponible, $importe)
    {
        $Tributo = array('codigo' => $codigo,
            'descripcion' => $descripcion,
            'baseImponible' => $baseImponible,
            'importe' => $importe);

        if (!isset($this->Request['arrayOtrosTributos'])) {
            $this->Request['arrayOtrosTributos'] = array('otroTributo' => array());
        }

        $this->Request['arrayOtrosTributos']['otroTributo'][] = $Tributo;

//        $this->Request['importeGravado'] = 0;
//        foreach ($this->Request['arrayOtrosTributos']['otroTributo'] as $key => $value) {
//            $this->Request['importeGravado'] = $this->Request['importeGravado'] + $value['importe'];
//        }
    }

    function AgregaCompAsoc($Tipo, $PtoVta, $Nro)
    {
        $CbteAsoc = array('Tipo' => $Tipo,
            'PtoVta' => $PtoVta,
            'Nro' => $Nro);

        if (!isset($this->Request['CbtesAsoc'])) {
            $this->Request['CbtesAsoc'] = array('CbteAsoc' => array());
        }

        $this->Request['CbtesAsoc']['CbteAsoc'][] = $CbteAsoc;
    }

    function Autorizar()
    {
        $Request = array('authRequest' => array(
            'token' => $this->Token,
            'sign' => $this->Sign,
            'cuitRepresentada' => $this->CUIT),
            'comprobanteCAERequest' => $this->Request
        );
        $results = $this->client->autorizarComprobante($Request);
        if (isset($results->arrayErrores)) {
            $this->ProcesaErrores($results->arrayErrores);
            return;
        }
        if (is_soap_fault($results)){
            $this->ErrorCode = -1;
            $this->ErrorDesc = utf8_decode($results->faultstring);
            return false;
        }

        $this->RespResultado = $results->resultado;

        if ($this->RespResultado == "A") {
            $this->RespCAE = $results->comprobanteResponse->CAE;
            $this->RespVencimiento = $results->comprobanteResponse->fechaVencimientoCAE;
        }


        if (isset($results->comprobanteResponse->arrayObservaciones)){
            if (is_array($results->comprobanteResponse->arrayObservaciones->codigoDescripcion)){
                $this->ErrorCode = $results->comprobanteResponse->arrayObservaciones->codigoDescripcion[0]->codigo;
                $this->ErrorDesc = $results->comprobanteResponse->arrayObservaciones->codigoDescripcion[0]->descripcion;
            } else {
                $this->ErrorCode = $results->comprobanteResponse->arrayObservaciones->codigoDescripcion->codigo;
                $this->ErrorDesc = $results->comprobanteResponse->arrayObservaciones->codigoDescripcion->descripcion;
            }
        }

        return $this->RespResultado == "A";
    }

    function CmpConsultar($TipoComp, $PtoVta, $nro, &$cbte)
    {
        $results = $this->client->autorizarComprobanteRequest(
            array('authRequest' => array('token' => $this->Token,
                'Sign' => $this->Sign,
                'cuitRepresentada' => $this->CUIT),
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
        $this->client = new SoapClient(WSDLWSW, array(
                'soap_version' => SOAP_1_2,
                'location' => $URL,
                'trace' => 1,
                'exceptions' => 0
            )
        );
    }

}

?>