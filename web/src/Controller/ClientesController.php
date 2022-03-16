<?php

namespace App\Controller;

use App\Entity\ListaDeUsuarios;
use App\Entity\User;
use App\Form\Filter\ClientesFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveClienteType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use App\Zennovia\Common\EntityManagerHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;


class ClientesController extends BaseController
{
    /**
     * @Route(path="/admin/clientes/list", name="clientes_index")
     * @Security("user.hasRole(['ROLE_CLIENTES_LIST'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(ClientesFilterType::class, null, ['csrf_protection' => false]);
        $repo = $this->getDoctrine()->getRepository('App:ListaDeUsuarios');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();                

        return $this->render('clientes/index.html.twig', $dataResult);       
    }

    /**
     * @Route(path="/admin/clientes/new", name="clientes_new")
     * @Security("user.hasRole(['ROLE_CLIENTES_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler){
        $clientes = new ListaDeUsuarios();       

        $handler->setClassFormType(SaveClienteType::class);
        $handler->createForm($clientes);
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.clientes.new.success');
    
                    return $this->redirectToRoute('clientes_index');
                }               
                
            }catch (\Exception $e) {
                $this->addFlashError('flash.clientes.new.error');
                $this->addFlashError($e->getMessage());
            }                           
        }

        return $this->render('clientes/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

    /**
     * @Route(path="/admin/clientes/{id}/view", name="clientes_show")
     * @Security("user.hasRole(['ROLE_CLIENTES_VIEW'])")
     * @param ListaDeUsuarios $cliente
     * @return Response
     */
    public function viewAction(ListaDeUsuarios $cliente)
    {
        return $this->render('clientes/show.html.twig', ['cliente' => $cliente]);
    }

    /**
     * @Route(path="/admin/clientes/{id}/edit", name="clientes_edit")
     * @Security("user.hasRole(['ROLE_CLIENTES_EDIT'])")
     * @param ListaDeUsuarios $entity
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function editAction(ListaDeUsuarios $entity, Request $request, SaveCommonFormHandler $handler)
    {        
        $params = [];
        $route = 'clientes_index';

        $handler->setClassFormType(SaveClienteType::class);
        $handler->createForm($entity);

        //dump($request->request->all()); die;
        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processTransactionForm()) {
                    $this->addFlashSuccess('flash.clientes.edit.success', '' , 'flashes');                                         

                    return $this->redirectToRoute($route, $params);
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.clientes.edit.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('clientes/edit.html.twig', ['form' => $handler->getForm()->createView(), 'entity' => $entity]);
    }

     /**
     * Eliminar una entidad Empleados.
     *
     * @Route(path="/admin/clientes/{id}/delete", name="clientes_delete")
     * @Security("user.hasRole(['ROLE_CLIENTES_DELETE'])")
     * @param ListaDeUsuarios $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(ListaDeUsuarios $entity, EntityManagerHelper $helper)
    {
        try {
            $helper->doDelete($entity);
            $this->addFlashSuccess('flash.clientes.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.clientes.delete.error');
        }

        return $this->redirectToRoute('clientes_index');
    }     
}
