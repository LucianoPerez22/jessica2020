<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;

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
              
        if (array_key_exists('art', $data)){          
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
        }            
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
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
    
                    return $this->redirectToRoute('ventas_index');
                }               
                
            }catch (\Exception $e) {
                $this->addFlashError('flash.ventas.new.error');
                $this->addFlashError($e->getMessage());
            }                           
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
}
