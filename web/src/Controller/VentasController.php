<?php

namespace App\Controller;

use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveArticuloType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;


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
        $articulo = new Ventas();       

        $handler->setClassFormType(SaveArticuloType::class);
        $handler->createForm($articulo);
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.articulos.new.success');
    
                    return $this->redirectToRoute('articulos_index');
                }               
                
            }catch (\Exception $e) {
                $this->addFlashError('flash.marcas.new.error');
                $this->addFlashError($e->getMessage());
            }                           
        }

        return $this->render('articulos/new.html.twig', array('form' => $handler->getForm()->createView()));
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

        // foreach ($info as $key => $value) {            
        //     dump($value->getArticulo()->getDescripcion());
        // }
        
        return $this->render('ventas/show.html.twig', ['venta' => $venta, 'articulos' => $info]);
    }   
}
