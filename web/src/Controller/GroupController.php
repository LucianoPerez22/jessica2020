<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use App\Zennovia\Common\BaseController;
use App\Entity\Group;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveGroupType;
use App\Form\Filter\GroupFilterType;

/**
 * Group controller.
 */
class GroupController extends BaseController
{
    /**
     * Listar todas las entidades Group
     * @Route(path="/admin/group/list", name="group_list")
     * @Route(path="/admin/group/")
     * @Security("user.hasRole(['ROLE_GROUP_LIST'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function listAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(GroupFilterType::class, null, array('csrf_protection' => false));
        $repo = $this->getDoctrine()->getRepository('App:Group');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();

        return $this->render('group/list.html.twig', $dataResult);
    }

    /**
     * Add new group
     * @Route(path="/admin/group/new", name="group_new")
     * @Security("user.hasRole(['ROLE_GROUP_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler)
    {
        $handler->setClassFormType(SaveGroupType::class);
        $handler->createForm(new Group());

        if ($handler->isSubmittedAndIsValidForm($request)) {
            if ($handler->processForm()) {
                $this->addFlashSuccess('flash.group.new.success');

                return $this->redirectToRoute('group_list');
            }
        }

        return $this->render('group/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

    /**
     *  Edit a group
     * @Route(path="/admin/group/{id}/edit", name="group_edit")
     * @Security("user.hasRole(['ROLE_GROUP_EDIT'])")
     * @param Group $entity
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(Group $entity, Request $request, SaveCommonFormHandler $handler)
    {
        $handler->setClassFormType(SaveGroupType::class);
        $handler->createForm($entity);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            if ($handler->processForm()) {
                $translator = $this->get('translator');
                $text = '<a href="' . $this->generateUrl('group_view', ['id' => $entity->getId()]) . '">' .
                    $translator->trans('page.group.labels.group') . '</a>';

                $this->addFlashSuccess($translator->trans('flash.group.edit.success', ['%linkEntity%' => $text], 'flashes'));

                return $this->redirectToRoute('group_list');
            }
        }

        return $this->render('group/edit.html.twig', array('form' => $handler->getForm()->createView(), 'entity' => $entity));
    }

    /**
     * Visualizar una entidad  Group.
     *
     * @Route(path="/admin/group/{id}/view", name="group_view")
     * @Security("user.hasRole(['ROLE_GROUP_VIEW'])")
     * @param Group $entity
     * @return Response
     */
    public function viewAction(Group $entity)
    {
        return $this->render('group/view.html.twig', array('group' => $entity));
    }

    /**
     * Eliminar una entidad Group.
     *
     * @Route(path="/admin/group/{id}/delete", name="group_delete")
     * @Security("user.hasRole(['ROLE_GROUP_DELETE'])")
     * @param Group $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Group $entity, EntityManagerHelper $helper)
    {
        try {
            $helper->doDelete($entity);
            $this->addFlashSuccess('flash.group.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.group.delete.error');
        }

        return $this->redirectToRoute('group_list');
    }
}
