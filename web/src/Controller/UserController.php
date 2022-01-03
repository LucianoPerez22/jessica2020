<?php


namespace App\Controller;

use App\Service\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use App\Zennovia\Common\BaseController;
use App\Form\Handler\ChangePasswordFormHandler;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Handler\SaveUserFormHandler;
use App\Form\Handler\RecoverPasswordFormHandler;
use App\Form\Filter\UserFilterType;
use App\Form\Type\SaveUserType;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\RegistrationType;
use App\Form\Type\RecoverPasswordType;
use App\Form\Type\ResetPasswordType;
use App\Form\Model\ChangePasswordModel;
use App\Form\Model\RegistrationModel;
use App\Form\Model\ResetPasswordModel;
use App\Entity\User;

class UserController extends BaseController
{
    /**
     * @Route(path="/admin/user/list", name="user_list")
     * @Route(path="/admin/user/")
     * @Security("user.hasRole(['ROLE_USER_LIST'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function listAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(UserFilterType::class, null, ['csrf_protection' => false, 'admin' => true]);
        $repo = $this->getDoctrine()->getRepository('App:User');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();

        return $this->render('user/list.html.twig', $dataResult);
    }

    /**
     * @Route(path="/admin/user/{id}/view", name="user_view")
     * @Security("user.hasRole(['ROLE_USER_VIEW'])")
     * @param User $user
     * @return Response
     */
    public function viewAction(User $user)
    {
        return $this->render('user/view.html.twig', ['user' => $user]);
    }


    /**
     * @Route(path="/admin/user/new", name="user_new")
     * @Security("user.hasRole(['ROLE_USER_NEW'])")
     * @param Request $request
     * @param SaveUserFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveUserFormHandler $handler){
        $route = 'user_list';
        $params = [];

        $handler->setClassFormType(SaveUserType::class);
        $handler->createForm(
            (new User()),
            [
                'admin'    => $this->getUser()->getSuperAdmin()
            ]
        );

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processTransactionForm()) {
                    $this->addFlashSuccess('flash.user.new.success');

                    return $this->redirectToRoute($route, $params);
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.user.new.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('user/new.html.twig', [
            'form'           => $handler->getForm()->createView()
        ]);
    }

    /**
     * @Route(path="/admin/user/{id}/edit", name="user_edit")
     * @Security("user.hasRole(['ROLE_USER_EDIT'])")
     * @param User $entity
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function editAction(User $entity, Request $request, SaveCommonFormHandler $handler)
    {

        $userMenu = $request->get('_route') == 'user_edit';
        $params = [];
        $route = 'user_list';

        $handler->setClassFormType(SaveUserType::class);
        $handler->createForm($entity, [
            'edit'     => true, 'admin' => $this->getUser()->getSuperAdmin()
        ]);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processTransactionForm()) {
                    $translator = $this->get('translator');
                    $text = '<a href="' . $this->generateUrl('user_view', ['id' => $entity->getId()]) . '">' .
                        $translator->trans('page.user.labels.user') . '</a>';

                    $this->addFlashSuccess($translator->trans('flash.user.edit.success', ['%linkEntity%' => $text], 'flashes'));

                    return $this->redirectToRoute($route, $params);
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.user.edit.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('user/edit.html.twig', ['form' => $handler->getForm()->createView(), 'entity' => $entity, 'userMenu' => $userMenu]);
    }

    /**
     * @Route(path="/admin/user/{id}/enable-disable/{value}", name="user_enable_disable")
     * @Security("user.hasRole(['ROLE_USER_EDIT'])")
     * @param User $user
     * @param EntityManagerHelper $helper
     * @param $value
     * @return RedirectResponse
     */
    public function enableDisableAction(User $user, EntityManagerHelper $helper, $value)
    {
        try {
            if ($user == $this->getUser()) {
                $this->addFlashError('flash.user.enable_disable.not_disabled');
            } else {
                $user->setEnabled($value);
                $helper->doSave($user);
                $txt = $value ? 'enabled' : 'disabled';
                $this->addFlashSuccess('flash.user.enable_disable.' . $txt);
            }
        } catch (\Exception $e) {
            $this->addFlashError('flash.user.edit.error');
        }

        return $this->redirectToRoute('user_list');
    }

    /**
     * @Route(path="/change-password", name="user_change_password")
     * @Security("user.hasRole(['ROLE_USER'])")
     * @param Request $request
     * @param ChangePasswordFormHandler $handler
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function changePasswordAction(Request $request, ChangePasswordFormHandler $handler)
    {
        $handler->setClassFormType(ChangePasswordType::class);
        $handler->createForm(new ChangePasswordModel());
        $handler->setEntity($this->getUser());

        if ($handler->isSubmittedAndIsValidForm($request)) {
            if ($handler->processForm()) {
                $this->addFlashSuccess('flash.user.change_password.success');

                return $this->redirectToRoute('user_change_password');
            }
        }

        return $this->render('user/change_password.html.twig', ['form' => $handler->getForm()->createView()]);
    }

    /**
     * @Route(path="/registration/{hash}", name="user_registration")
     * @ParamConverter("user", options={"mapping": {"hash": "hash"}})
     * @param Request $request
     * @param ChangePasswordFormHandler $handler
     * @param User $user
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function registrationAction(Request $request, ChangePasswordFormHandler $handler, User $user = null)
    {
        if (!$user) {
            $this->addFlashError('flash.user.common.not_found');

            return $this->redirectToRoute('login');
        }

        $handler->setClassFormType(RegistrationType::class);
        $handler->createForm(new RegistrationModel());
        $handler->setEntity($user);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            if ($handler->processForm()) {
                $this->addFlashSuccess('flash.user.registration.success');

                return $this->redirectToRoute('login');
            }
        }

        return $this->render('user/registration.html.twig', [
            'form' => $handler->getForm()->createView(),
            'hash' => $user->getHash(),
        ]);
    }

    /**
     * @Route(path="/recover-password", name="user_recover_password")
     * @param Request $request
     * @param RecoverPasswordFormHandler $handler
     * @return Response
     */
    public function recoverPasswordAction(Request $request, RecoverPasswordFormHandler $handler)
    {
        $handler->setClassFormType(RecoverPasswordType::class);
        $handler->createForm();

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.user.recover_password.success');
                }
            } catch (\Exception $e) {
                $this->addFlashError($e->getMessage());
            }

            return $this->redirectToRoute('user_recover_password');
        }

        return $this->render('user/recover_password.html.twig', ['form' => $handler->getForm()->createView()]);
    }

    /**
     * @Route(path="/reset-password/{hash}", name="user_reset_password")
     * @ParamConverter("user", options={"mapping": {"hash": "hash"}})
     * @param Request $request
     * @param ChangePasswordFormHandler $handler
     * @param User $user
     * @return RedirectResponse|Response
     */
    public function resetPasswordAction(Request $request, ChangePasswordFormHandler $handler, User $user = null)
    {
        if (!$user) {
            $this->addFlashError('flash.user.common.not_found');

            return $this->redirectToRoute('user_recover_password');
        }

        $handler->setClassFormType(ResetPasswordType::class);
        $handler->createForm(new ResetPasswordModel());
        $handler->setEntity($user);

        if ($handler->isSubmittedAndIsValidForm($request)) {
            try {
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.user.reset_password.success');

                    return $this->redirectToRoute('home');
                }
            } catch (\Exception $e) {
                $this->addFlashError('flash.user.change_password.error');
            }
        }

        return $this->render('user/reset_password.html.twig', [
            'form' => $handler->getForm()->createView(),
            'hash' => $user->getHash(),
        ]);
    }
}
