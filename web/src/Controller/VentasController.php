<?php

namespace App\Controller;

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

/**
 * @Route("/")
 */
class VentasController extends BaseController
{
    /**
     * @Route(path="/ventas/list", name="ventas_index")
     * @Security("user.hasRole(['ROLE_USER'])")
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
     * @Security("user.hasRole(['ROLE_MARCAS_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler){
        $venta = new Ventas();       

        $handler->setClassFormType(SaveVentasType::class);
        $handler->createForm($venta);
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
                if ($handler->processForm()) {
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
     * @Security("user.hasRole(['ROLE_ARTICULOS_VIEW'])")
     * @param Ventas $venta
     * @return Response
     */
    public function viewAction(Ventas $venta)
    {      
        $artRepo = $this->getDoctrine()->getRepository(VentasArt::class);
        $info    = $artRepo->findBy(['idVentas' => $venta->getId()]);        
        
        return $this->render('ventas/show.html.twig', ['venta' => $venta, 'articulos' => $info]);
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
