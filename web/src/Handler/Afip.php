<?php
namespace App\Handler;

use App\Entity\Ventas;
use App\Service\Afip\WsFE;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class Afip
{  
    private $em;

    public function __construct(EntityManagerInterface $em)
    {       
        $this->em = $em;    
    }   
   
    public function facturaElectronica(Ventas $venta){     
        //SI LA FACTURA YA FUE EMITIDA ENVIO LOS DATOS PARA REIMPRIMIR        
        if ($venta->getCaeVenc() != null ){
            $data['invoice_num'] = sprintf('%05d-', '00004') . sprintf('%08d', $venta->getNumero());
            $data['CAE'] = $venta->getCae(); 
            $venCae = $venta->getCaeVenc();            

            $data['Vto'] = $venCae;
                                   
            return $data;
        }
        
        //INICIO FACTURA ELECTRONICA               

        $cuit_cliente= $venta->getIdCliente()->getDocumento();

        ($cuit_cliente == 0) ? $cuit_cliente = '99999999' : '';

        if ($venta->getIdCliente()->getTipoIva() == 'final') {
            $cust_doc_type=96;            
        }else{
            $cust_doc_type=80;            
        }               

        (($venta->getIdCliente()->getTipoIva() == 'responsable')) ? $TipoComp=1 : $TipoComp=6;                
        
        $PtoVta=4;
        $mi_cuit=  '20-30391056-6';               

        $cust_cuit = floatval(str_replace('-', '', $cuit_cliente)); 
        $subtotal = floatval(round($venta->getTotal() / 1.21,2)); 
        $sum_tax = floatval(round($venta->getTotal() - $subtotal,2)); 
        $total = floatval(round($venta->getTotal(),2)); 
        
        $nro = 0;                         
        $FechaComp = date("Ymd");
        $certificado = "JessyV2_6038f9296162f8d7.crt";
        $clave = "ClavePrivadaLucho.key";
        $cuit = str_replace('-', '', $mi_cuit);
        $urlwsaa = "https://wsaa.afip.gov.ar/ws/services/LoginCms";

        // Los parametros de metodos y propiedades estan en www.bitingenieria.com.ar/webhelp
        $wsfe = new WsFE();
        $wsfe->CUIT = floatval($cuit);
        $wsfe->setURL("https://servicios1.afip.gov.ar/wsfev1/service.asmx");
        if ($wsfe->Login($certificado, $clave, $urlwsaa)) {
            if (!$wsfe->RecuperaLastCMP($PtoVta, $TipoComp)) {
                echo $wsfe->ErrorDesc;
            } else {                    
                $wsfe->Reset();
                $nro = $wsfe->RespUltNro + 1;
                $wsfe->AgregaFactura(1, $cust_doc_type, $cust_cuit, $nro, $nro, $FechaComp, $total, 0.0, $subtotal, 0.0, "", "", "", "PES", 1);
                $wsfe->AgregaIVA(5, $subtotal, $sum_tax); //5 es 21% y 3 es 0%
                
                $auth = false;
                                    
                try {
                    if ($wsfe->Autorizar($PtoVta, $TipoComp)) {                            
                        $auth = true;                                        
                    } else {                                       
                        echo $wsfe->ErrorDesc;
                    }                       
                } catch (\Exception $e) {
                    if ($wsfe->CmpConsultar($TipoComp, $PtoVta, $nro, $cbte)) {

                        $auth = true;
                    } else{
                        echo $wsfe->ErrorDesc;
                    }
                }
                
                if ($auth) {                        
                    $data['invoice_num'] = sprintf('%05d-', $PtoVta) . sprintf('%08d', $nro);
                    $data['CAE'] = $wsfe->RespCAE;   
                    $venCae = date("Y-m-d", strtotime($wsfe->RespVencimiento));
                    $data['Vto'] = new DateTime($venCae);
                    
                    $venta->setNumero($nro);
                    $venta->setCae($data['CAE']);
                    $venta->setCaeVenc($data['Vto']);
                    ($TipoComp == 1) ? $venta->setTipo('A') : $venta->setTipo('B');                                                
                                                                    
                    $this->em->persist($venta);
                    $this->em->flush();                      
                } 
            }

            return $data;
        } else {
            echo $wsfe->ErrorDesc;
        }
    //FIN FACTURA ELECTRONICA
    }

    public function imprimirFactura($html){
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);

        $dompdf->loadHtml($html); 
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('A4', 'portrait');
    
        $dompdf->render();
        
        $dompdf->stream("factura.pdf", array("Attachment" => false));

        exit(0);
    }
}