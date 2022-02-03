<?php

namespace App\Controller;

use App\Entity\Articulos;
use App\Form\Filter\ArticulosFilterType;
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
 * @Route("/articulos")
 */
class ArticulosController extends BaseController
{
    /**
     * @Route(path="/admin/articulos/list", name="articulos_index")
     * @Security("user.hasRole(['ROLE_ARTICULOS_LIST'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(ArticulosFilterType::class, null, ['csrf_protection' => false]);
        $repo = $this->getDoctrine()->getRepository('App:Articulos');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();       
        
        return $this->render('articulos/index.html.twig', $dataResult);       
    }

    /**
     * @Route(path="/admin/articulos/new", name="articulos_new")
     * @Security("user.hasRole(['ROLE_ARTICULOS_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler){
        $articulo = new Articulos();       

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
     * @Route(path="/admin/articulos/view/{id}", name="articulos_show")
     * @Security("user.hasRole(['ROLE_ARTICULOS_VIEW'])")
     * @param Articulos $articulo
     * @return Response
     */
    public function viewAction(Articulos $articulo)
    {
        return $this->render('articulos/show.html.twig', ['articulo' => $articulo]);
    }

    /**
     * @Route(path="/admin/articulos/{id}/edit", name="articulos_edit")
     * @Security("user.hasRole(['ROLE_ARTICULOS_EDIT'])")
     * @param Articulos $entity
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function editAction(Articulos $entity, Request $request, SaveCommonFormHandler $handler)
    {        
        $params = [];
        $route = 'articulos_index';

        $handler->setClassFormType(SaveArticuloType::class);
        $handler->createForm($entity);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processTransactionForm()) {
                    $this->addFlashSuccess('flash.articulos.edit.success', '' , 'flashes');                                         

                    return $this->redirectToRoute($route, $params);
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.articulos.edit.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('articulos/edit.html.twig', ['form' => $handler->getForm()->createView(), 'entity' => $entity]);
    }
     /**
     * Eliminar una entidad Empleados.
     *
     * @Route(path="/admin/articulos/{id}/delete", name="articulos_delete")
     * @Security("user.hasRole(['ROLE_ARTICULOS_DELETE'])")
     * @param Articulos $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Articulos $entity, EntityManagerHelper $helper)
    {
        try {
            $helper->doDelete($entity);
            $this->addFlashSuccess('flash.articulos.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.articulos.delete.error');
        }

        return $this->redirectToRoute('articulos_index');
    }     

    /**
     * @Route(path="/venta/art/get/{articulo}", name="ajax_venta_art_get")
     * @Security("user.hasRole(['ROLE_ARTICULOS_VIEW'])")
     * @param Articulos $articulo
     * @param Request $request
     * @return Response
     */
    public function ajaxAction(Articulos $articulo = null, Request $request)
    {                              
        if ($request->isXmlHttpRequest()) {
            $precio = $articulo->getPrecio() * 1.21;
            return new Response($precio);
        }
    }   
}
