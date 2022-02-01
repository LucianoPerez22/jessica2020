<?php

namespace App\Controller;

use App\Service\Afip\WsFE;
use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;


/**
 * @Route("/")
 */
class VentasController extends BaseController
{
    /**
     * @Route(path="/ventas/list", name="ventas_index")
     * @Security("user.hasRole(['ROLE_VENTAS_LIST'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(VentasFilterType::class, null, ['csrf_protection' => false]);        
        $repo = $this->getDoctrine()->getRepository('App:Ventas');
       
        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();       

        return $this->render('ventas/index.html.twig', $dataResult);       
    }

    /**
     * @Route(path="/venta/new", name="venta_new")
     * @Security("user.hasRole(['ROLE_VENTAS_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler, UserInterface $user){
        $venta = new Ventas();       

        $handler->setClassFormType(SaveVentasType::class);
        $handler->createForm($venta);
        
        //Obtengo ultimo numero remito
        $numero = $this->getDoctrine()->getRepository('App:Ventas')->findLastNumber();
        
        //Recibo request y chequeo si existen los datos 
        //de articulos para guardar la venta
        $data = $request->request->all();           

        try {
            if (array_key_exists('art', $data)){   
                if ($data['art']['total0'] == 0){
                    $this->addFlashError('flash.ventas.new.error');
                    return $this->render('ventas/new.html.twig', array('form' => $handler->getForm()->createView()));
                }                                 
                $venta->setFecha(new \DateTime());        
                $venta->setHora(date("h:i:sa"));                       
                $venta->setEstado('ok');
    
                $numeroR = $numero->getResult();           
                $venta->setNumero(intval($numeroR[0][1])+1);
    
                $venta->setCae('');
                $venta->setTipo('R');
                $total = 0;
                foreach ($data['art'] as $key => $value) {
                    (substr($key, 0, 5) == 'total') ? $total += floatval($value) : '';                         
                }
               
                $venta->setTotal($total);
    
                if($handler->isSubmittedAndIsValidForm($request)){                                                                                          
                        if ($handler->processForm()) {                     
                            $ventaRepo = $this->getDoctrine()->getRepository('App:Ventas')->findOneBy([], ['id' => 'desc']);                                                            
                            
                            $manager = $this->getDoctrine()->getManager();                                        
                            
                            foreach ($data['art'] as $key => $value) {                                                                    
                                if (substr($key, 0, 4) == 'cant') {
                                    $articulos = new VentasArt();
                                    $stock = new Stock();
        
                                    $articulos->setCant($value);    
                                    $stock->setCantidad($value * -1);
                                    $stock->setFecha(new \DateTime());
                                    $stock->setUsuario("Venta: " . $user);
                                } 
                                
                                if (substr($key, 0, 5) == 'idArt') {                           
                                    $artRepo = $this->getDoctrine()->getRepository('App:Articulos');
                                    $articulo  = $artRepo->findOneBy(["id" => intval($value)]);
                                   
                                    $articulos->setIdArt($articulo);
                                    $stock->setIdArticulo($articulo);
                                }                         
                                (substr($key, 0, 6) == 'precio') ? $articulos->setPrecio(floatval($value)) : '';
                                if (substr($key, 0, 5) == 'total') {
                                    $articulos->setIdVentas($ventaRepo);
                                    $articulos->setTotal(floatval($value));
        
                                    $manager->persist($articulos);
                                    $manager->flush($articulos);  
        
                                    $manager->persist($stock);
                                    $manager->flush($stock);
                                }                                                                      
                            }                                                                    
                            $this->addFlashSuccess('flash.ventas.new.success');
            
                            return $this->redirectToRoute('venta_show', ['id'=> $venta->getId()]);
                        }                                                                              
                }else{
                        $this->addFlashError('flash.ventas.new.error');
                     }   
            }   
        } catch (\Exception $e) {
            $this->addFlashError('flash.ventas.new.error');
            $this->addFlashError($e->getMessage());
        }                  
       
        return $this->render('ventas/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

    /**
     * @Route(path="/venta/view/{id}", name="venta_show")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")
     * @param Ventas $venta
     * @return Response
     */
    public function viewAction(Ventas $venta)
    {            
        return $this->render('ventas/show.html.twig', ['venta' => $venta]);
    }   

     /**
     * @Route(path="/venta/art/{num_control}", name="ajax_venta_art")
     * @Security("user.hasRole(['ROLE_VENTAS_NEW'])")
     * @param Ventas $venta
     * @param Request $request
     * @return Response
     */
    public function ajaxAction($num_control = null, Request $request)
    {           
        if ($request->isXmlHttpRequest()) {                
            $dato = [
                0 => $num_control
            ];                                                 
            $form = $this->createForm(SaveVentasArtType::class, null, ['algo' => $dato]);              
            
            return $this->render('ventas/articulos.html.twig', [
                'form'          => $form->createView(),
                'num_control'          => $num_control,                                    
            ]);
        }
    }   

    /**
     * @Route(path="/venta/factura/{id}", name="venta_factura")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])") 
     * @param Ventas $venta    
     * @return Response
     */
    public function facturaAction(Ventas $venta)
    {      
        $data = $this->afipAction($venta);  

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('ventas/factura.html.twig', ['venta' => $venta, 'data' =>$data]);        
        
        $dompdf->loadHtml($html); 
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('A4', 'portrait');
        
        $dompdf->render();
        
        $dompdf->stream("factura.pdf", array("Attachment" => false));

        exit(0);
    }   

     /** 
     * @param Ventas $venta
     * @return Response
     */
    public function afipAction(Ventas $venta){     
        
        if ($venta->getCaeVenc() != null ){
            $data['invoice_num'] = sprintf('%05d-', '00006') . sprintf('%08d', $venta->getNumero());
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
            $subtotal = floatval(number_format($venta->getTotal() / 1.21, 2)); 
            $sum_tax = floatval(number_format($venta->getTotal() - ($venta->getTotal() / 1.21), 2)); 
            $total = floatval(number_format($venta->getTotal(), 2)); 
          
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
                        
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($venta);
                        $em->flush();


                        return $data;
                    } 
                }
            } else {
                echo $wsfe->ErrorDesc;
            }
        //FIN FACTURA ELECTRONICA
    }

     /** 
     * @Route(path="/venta/recibo/{id}", name="venta_recibo")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")  
     * @param Ventas $venta
     * @return Response
     */
    public function reciboAction(Ventas $venta){                                            
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            $pdfOptions->set('isRemoteEnabled', TRUE);
            
            $dompdf = new Dompdf($pdfOptions);
            
            // Recupere el HTML generado en nuestro archivo twig
            $html = $this->renderView('ventas/recibo.html.twig', ['venta' => $venta]);        
            
            $dompdf->loadHtml($html); 
            
            // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'           
            $dompdf->setPaper(array(0,0,720,600), 'portrait');
            
            $dompdf->render();
            
            $dompdf->stream("recibo.pdf", array("Attachment" => false));
    
            exit(0);               
    }

     /**
     * Eliminar una entidad Empleados.
     *
     * @Route(path="/admin/ventas/{id}/delete", name="ventas_delete")
     * @Security("user.hasRole(['ROLE_VENTAS_DELETE'])")
     * @param Ventas $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Ventas $entity, EntityManagerHelper $helper, UserInterface $user)
    {
        try {
            $ventasArtRepo = $this->getDoctrine()->getRepository(VentasArt::class);
            $ventasArt     = $ventasArtRepo->findBy(['idVentas' => $entity]);
            
            $em = $this->getDoctrine()->getManager();

            foreach ($ventasArt as $key) {
                $stock = new Stock();
                
                $stock->setIdArticulo(($key->getidArt())); 
                $stock->setCantidad($key->getCant());
                $stock->setFecha(new \DateTime());
                $stock->setUsuario("Stock: " . $user);
                
                $em->persist($stock);
                $em->flush();
            
                $em->remove($key);
                $em->flush();
            }
                      
             $helper->doDelete($entity);
             $this->addFlashSuccess('flash.ventas.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.ventas.delete.error');
        }

        return $this->redirectToRoute('ventas_index');
    }     
}
