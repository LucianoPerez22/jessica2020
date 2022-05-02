<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Handler\Afip;
use App\Handler\NoAfip;
use App\Handler\Recibos;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
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
        $repo = $this->getDoctrine()->getRepository(Ventas::class);
       
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

        $venta->setUser($user);                                                                                       
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
     * @param Afip $afip    
     * @return Response
     */
    public function facturaAction(Ventas $venta, Afip $afip)
    {              
        $data = $afip->facturaElectronica($venta);        
               
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('ventas/factura.html.twig', ['venta' => $venta, 'data' =>$data]);                                       

        $afip->imprimirFactura($html);
    }   
    
     /** 
     * @Route(path="/venta/recibo/{id}", name="venta_recibo")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")  
     * @param Ventas $venta
     * @return Response
     */
    public function reciboAction(Ventas $venta){  
        $recibo = new Recibos;                                          
        
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('ventas/recibo.html.twig', ['venta' => $venta]);        
        
        $recibo->imprimirRecibo($html);         
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
    public function deleteAction(Ventas $entity, EntityManagerHelper $helper, UserInterface $user, Afip $afip)
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
                $stock->setUsuario("Compra Cancelada: " . $user);
                
                $em->persist($stock);
                $em->flush();
            
                $em->remove($key);
                $em->flush();
            }
                      
             $helper->doDelete($entity);

             $data = $afip->facturaElectronica($entity);        
               
            // Recupere el HTML generado en nuestro archivo twig
            $html = $this->renderView('ventas/notaCredito.html.twig', ['venta' => $entity, 'data' =>$data]);                                       

            $afip->imprimirNotaCredito($html);

             $this->addFlashSuccess('flash.ventas.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.ventas.delete.error');
        }

        return $this->redirectToRoute('ventas_index');
    }     

      /** 
     * @Route(path="/admin/ventas/obtener", name="obtener_comprobantes")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")      
     */
    public function obtenerComprobantes(Afip $afip){
        
        $data = $afip->obtenerComprobantes();
        dump($data);
        die;
    }
}
