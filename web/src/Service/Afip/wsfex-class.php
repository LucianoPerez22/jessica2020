<?php
namespace App\Service\Afip;

#==============================================================================
define ("WSDLWSAA", dirname(__FILE__)."/wsaa.wsdl");
define ("WSDLWSW", dirname(__FILE__)."/wsfex.wsdl");
# Cambiar para prueba
//define ("URLWSAA", "https://wsaahomo.afip.gov.ar/ws/services/LoginCms");
//define ("URLWSW", "https://wswhomo.afip.gov.ar/wsfexv1/service.asmx");
# Cambiar para produccion
define ("URLWSAA", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
define ("URLWSW", "https://servicios1.afip.gov.ar/wsfexv1/service.asmx");
#==============================================================================

date_default_timezone_set('America/Buenos_Aires');

class WsFEX
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

    private $certificado;
    private $clave;

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
        $this->ErrorCode = $Errors->ErrCode;
        $this->ErrorDesc = $Errors->ErrMsg;
    }

    function Login($certificado, $clave, $urlWsaa)
    {
        $this->certificado = dirname($certificado) == "." ? $this->currentPath."certificados/".$certificado : $certificado;
        $this->clave = dirname($clave) == "." ? $this->currentPath."certificados/".$clave : $clave;
        $this->urlWsaa = $urlWsaa;

        if (!$this->loadCredentials($urlWsaa, "wsfex")) {
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
            $SERVICE = "wsfex";
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
            $config = new stdClass();
        }

        $config->$key = array("token"=> (string) $this->Token,
            "sign" => (string) $this->Sign,
            "timeStamp" => time());
        $file = fopen($filename, "w+");
        fwrite($file, json_encode($config));
        fclose($file);
    }

    function RecuperaLastCMP($Pto_venta, $Cbte_Tipo)
    {
        $results = $this->client->FEXGetLast_CMP(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT,
                'Pto_venta' => $Pto_venta,
                'Cbte_Tipo' => $Cbte_Tipo)));
        if ($results->FEXGetLast_CMPResult->FEXErr->ErrCode != 0) {
            $this->procesaErrores($results->FEXGetLast_CMPResult->FEXErr);
            return false;
        } else if (is_soap_fault($results)) {
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }
        $this->RespUltNro = $results->FEXGetLast_CMPResult->FEXResult_LastCMP->Cbte_nro;

        return true;
    }

    function UltimoIdTrans(&$IdTrans)
    {
        $results = $this->client->FEXGetLast_ID(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT)));
        if ($results->FEXGetLast_IDResult->FEXErr->ErrCode != 0) {
            $this->procesaErrores($results->FEXGetLast_IDResult->FEXErr);
            return false;
        } else if (is_soap_fault($results)) {
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }
        $IdTrans = $results->FEXGetLast_IDResult->FEXResultGet->Id;

        return true;
    }

    function Reset()
    {
        $this->Request = array();
        return;
    }



    function AgregaFactura($Id, $Fecha_cbte, $Cbte_Tipo, $Punto_vta, $Cbte_nro, $Tipo_expo, $Permiso_existente, $Dst_cmp, $Cliente, $Cuit_pais_cliente,
                           $Domicilio_cliente, $Id_impositivo, $Moneda_Id, $Moneda_ctz, $Obs_comerciales, $Imp_total, $Obs, $Forma_pago, $Incoterms,
                           $Incoterms_ds, $Idioma_cbte, $Fecha_pago)
    {
        $this->Request['Id'] = $Id;
        $this->Request['Fecha_cbte'] = $Fecha_cbte;
        $this->Request['Cbte_Tipo'] = $Cbte_Tipo;
        $this->Request['Punto_vta'] = $Punto_vta;
        $this->Request['Cbte_nro'] = $Cbte_nro;
        $this->Request['Tipo_expo'] = $Tipo_expo;
        $this->Request['Permiso_existente'] = $Permiso_existente;
        $this->Request['Dst_cmp'] = $Dst_cmp;
        $this->Request['Cliente'] = $Cliente;
        $this->Request['Cuit_pais_cliente'] = $Cuit_pais_cliente;
        $this->Request['Domicilio_cliente'] = $Domicilio_cliente;
        $this->Request['Id_impositivo'] = $Id_impositivo;
        $this->Request['Moneda_Id'] = $Moneda_Id;
        $this->Request['Moneda_ctz'] = $Moneda_ctz;
        $this->Request['Obs_comerciales'] = $Obs_comerciales;
        $this->Request['Imp_total'] = $Imp_total;
        $this->Request['Obs'] = $Obs;
        $this->Request['Forma_pago'] = $Forma_pago;
        $this->Request['Incoterms'] = $Incoterms;
        $this->Request['Incoterms_ds'] = $Incoterms_ds;
        $this->Request['Idioma_cbte'] = $Idioma_cbte;
        if (isset($Fecha_pago)) {
            $this->Request['Fecha_pago'] = $Fecha_pago;
        }
    }

    function AgregaItem($Pro_codigo, $Pro_ds, $Pro_qty, $Pro_umed, $Pro_precio_uni, $Pro_total_item, $Pro_bonificacion)
    {
        $Item = array('Pro_codigo' => $Pro_codigo,
            'Pro_ds' => $Pro_ds,
            'Pro_qty' => $Pro_qty,
            'Pro_umed' => $Pro_umed,
            'Pro_precio_uni' => $Pro_precio_uni,
            'Pro_total_item' => $Pro_total_item,
            'Pro_bonificacion' => $Pro_bonificacion);

        if (!isset($this->Request['Items'])) {
            $this->Request['Items'] = array('Item' => array());
        }

        $this->Request['Items']['Item'][] = $Item;
    }

    function AgregaCompAsoc($Cbte_tipo, $Cbte_punto_vta, $Cbte_nro, $Cbte_cuit)
    {
        $CbteAsoc = array('Cbte_tipo' => $Cbte_tipo,
            'Cbte_punto_vta' => $Cbte_punto_vta,
            'Cbte_nro' => $Cbte_nro,
            'Cbte_cuit' => $Cbte_cuit);

        if (!isset($this->Request['Cmps_asoc'])) {
            $this->Request['Cmps_asoc'] = array('Cmp_asoc' => array());
        }

        $this->Request['Cmps_asoc']['Cmp_asoc'][] = $CbteAsoc;
    }

    function AgregaPermiso($Id_permiso, $Dst_merc)
    {
        $Permiso = array('Id_permiso' => $Id_permiso,
            'Dst_merc' => $Dst_merc);

        if (!isset($this->Request['Permisos'])) {
            $this->Request['Permisos'] = array('Permiso' => array());
        }

        $this->Request['Permisos']['Permiso'][] = $Permiso;
    }

    function Autorizar()
    {
        $Request = array('Auth' => array(
            'Token' => $this->Token,
            'Sign' => $this->Sign,
            'Cuit' => $this->CUIT),
            'Cmp' => $this->Request
        );
        $results = $this->client->FEXAuthorize($Request);
        if ($results->FEXAuthorizeResult->FEXErr->ErrCode != 0) {
            $this->ProcesaErrores($results->FEXAuthorizeResult->FEXErr);
            return;
        }
        if (is_soap_fault($results)){
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->faultstring;
            return false;
        }

        $this->RespResultado = $results->FEXAuthorizeResult->FEXResultAuth->Resultado;

        if ($this->RespResultado == "A") {
            $this->RespCAE = $results->FEXAuthorizeResult->FEXResultAuth->Cae;
            $this->RespVencimiento = $results->FEXAuthorizeResult->FEXResultAuth->Fch_venc_Cae;
        } elseif ($this->ErrorCode == 0){
            $this->ErrorCode = -1;
            $this->ErrorDesc = $results->FEXAuthorizeResult->FEXResultAuth->Motivos_Obs;
        }

        return $this->RespResultado == "A";
    }

    function CmpConsultar($Cbte_tipo, $Punto_vta, $Cbte_nro, &$cbte) //cbte variable por referencia
    {
        $results = $this->client->FEXGetCMP(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT),
                'Cmp' => array('Cbte_tipo' => $Cbte_tipo,
                    'Punto_vta' => $Punto_vta,
                    'Cbte_nro' => $Cbte_nro)
            )
        );
        if ($results->FEXGetCMPResult->FEXErr->ErrCode != 0) {
            $this->procesaErrores($results->FEXGetCMPResult->FEXErr);
            return false;
        }
        $cbte = $results->FEXGetCMPResult->FEXResultGet;

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
                'exceptions' => 1
            )
        );
    }

    function FEXGetPARAM_Ctz($Mon_id, &$FEXGetPARAM_CtzResponse) //$FEXGetPARAM_CtzResponse variable por referencia
    {
        $results = $this->client->FEXGetPARAM_Ctz(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT),
                'Mon_id' => $Mon_id
            )
        );
        if ($results->FEXGetPARAM_CtzResult->FEXErr->ErrCode != 0) {
            $this->procesaErrores($results->FEXGetPARAM_CtzResult->FEXErr);
            return false;
        }
        $FEXGetPARAM_CtzResponse = $results->FEXGetPARAM_CtzResult->FEXResultGet;

        return true;
    }

    function FEXGetPARAM_MON_CON_COTIZACION($Fecha_CTZ, &$FEXGetPARAM_MON_CON_COTIZACIONResponse) //$FEXGetPARAM_MON_CON_COTIZACIONResponse variable por referencia
    {
        $results = $this->client->FEXGetPARAM_MON_CON_COTIZACION(
            array('Auth' => array('Token' => $this->Token,
                'Sign' => $this->Sign,
                'Cuit' => $this->CUIT),
                'Fecha_CTZ' => $Fecha_CTZ
            )
        );
        if ($results->FEXGetPARAM_MON_CON_COTIZACIONResult->FEXErr->ErrCode != 0) {
            $this->procesaErrores($results->FEXGetPARAM_MON_CON_COTIZACIONResult->FEXErr);
            return false;
        }
        $FEXGetPARAM_MON_CON_COTIZACIONResponse = $results->FEXGetPARAM_MON_CON_COTIZACIONResult->FEXResultGet;

        return true;
    }
}

?>