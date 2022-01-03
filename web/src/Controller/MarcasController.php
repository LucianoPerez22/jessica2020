<?php

namespace App\Controller;

use App\Entity\Marcas;
use App\Form\Filter\MarcasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\MarcasType;
use App\Form\Type\SaveMarcasType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use App\Zennovia\Common\EntityManagerHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;


class MarcasController extends BaseController
{
    /**
     * @Route(path="/admin/marcas/list", name="marcas_index")
     * @Security("user.hasRole(['ROLE_USER'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(MarcasFilterType::class, null, ['csrf_protection' => false]);
        $repo = $this->getDoctrine()->getRepository('App:Marcas');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();
       
        return $this->render('marcas/index.html.twig', $dataResult);       
    }

    /**
     * @Route(path="/admin/marcas/new", name="marcas_new")
     * @Security("user.hasRole(['ROLE_MARCAS_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler){
        $marca = new Marcas();       

        $handler->setClassFormType(SaveMarcasType::class);
        $handler->createForm($marca);
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.marcas.new.success');
    
                    return $this->redirectToRoute('marcas_index');
                }               
                
            }catch (\Exception $e) {
                $this->addFlashError('flash.marcas.new.error');
                $this->addFlashError($e->getMessage());
            }                           
        }

        return $this->render('marcas/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

    /**
     * @Route(path="/admin/marcas/{id}/view", name="marcas_show")
     * @Security("user.hasRole(['ROLE_MARCAS_VIEW'])")
     * @param Marcas $marcas
     * @return Response
     */
    public function viewAction(Marcas $marca)
    {
        return $this->render('marcas/show.html.twig', ['marcas' => $marca]);
    }

    /**
     * @Route(path="/admin/marcas/{id}/edit", name="marcas_edit")
     * @Security("user.hasRole(['ROLE_EMPLEADOS_EDIT'])")
     * @param Marcas $entity
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function editAction(Marcas $entity, Request $request, SaveCommonFormHandler $handler)
    {        
        $params = [];
        $route = 'marcas_index';

        $handler->setClassFormType(SaveMarcasType::class);
        $handler->createForm($entity);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processTransactionForm()) {
                    $this->addFlashSuccess('flash.marcas.edit.success', '' , 'flashes');                                         

                    return $this->redirectToRoute($route, $params);
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.marcas.edit.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('marcas/edit.html.twig', ['form' => $handler->getForm()->createView(), 'entity' => $entity]);
    }

     /**
     * Eliminar una entidad Empleados.
     *
     * @Route(path="/admin/marcas/{id}/delete", name="marcas_delete")
     * @Security("user.hasRole(['ROLE_MARCAS_DELETE'])")
     * @param Marcas $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Marcas $entity, EntityManagerHelper $helper)
    {
        try {
            $helper->doDelete($entity);
            $this->addFlashSuccess('flash.marcas.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.marcas.delete.error');
        }

        return $this->redirectToRoute('marcas_index');
    }     
}
