<?php


namespace App\Controller;

use App\Form\Type\SettingType;
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

class SettingController extends BaseController
{
    /**
     * @Route(path="/setting", name="setting_index")
     * @Route(path="/setting")
     * @return Response
     */
    public function indexAction(Request $request, SaveCommonFormHandler $handler)
    {
        $handler->setClassFormType(SettingType::class);
        $handler->createForm();

        if($handler->isSubmittedAndIsValidForm($request)){
            try {
                //TODO AGREGAR LOGICA
                var_dump($request->request->all());
                die;

            }catch (\Exception $e) {
                $this->addFlashError('flash.marcas.new.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('setting/prices.html.twig', array('form' => $handler->getForm()->createView()));

    }

}
