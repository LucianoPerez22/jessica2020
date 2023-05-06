<?php

namespace App\Controller;

use App\Entity\ListaDeUsuarios;
use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Entity\VentasRecurrentes;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasRecurrentesType;
use App\Form\Type\SaveVentasType;
use App\Handler\Afip;
use App\Handler\NoAfip;
use App\Handler\Recibos;
use App\Repository\ClientesRepository;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
class VentasRecurrentesController extends BaseController
{
    /**
     * @Route(path="/ventas-recurrentes/list", name="ventas_recurrentes_index")
     * @Security("user.hasRole(['ROLE_VENTAS_RECURRENTES'])")
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
     * @Route(path="/venta-recurrentes/new", name="venta_recurrentes_new")
     * @Security("user.hasRole(['ROLE_VENTAS_RECURRENTES'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler, UserInterface $user, Afip $afip){
        $ventasRecurrentes = new VentasRecurrentes();

        $handler->setClassFormType(SaveVentasRecurrentesType::class);
        $handler->createForm(null);

        $data = $request->request->all();

        if($handler->isSubmittedAndIsValidForm($request)){
            /* @var ClientesRepository $clientsRepository */
            $clientsRepository = $this->getDoctrine()->getRepository(ListaDeUsuarios::class);

            $clients = $clientsRepository->findAllByDocumenteAndFinal();

            $val = array_rand($clients);

           dd($clients[$val]->getNombre());

            //TODO: Recibir datos y generar facturas
            for ($i = 0; $i < intval($data['save_ventas_recurrentes']['cantidad']); $i++) {

            }
            dd($data['save_ventas_recurrentes']['cantidad']);
            //Llamar Afip enviar params
            //$afip->facturaElectronica();


            //    $this->addFlashError('flash.ventas.new.error');

        }

        return $this->render('ventas_recurrentes/new.html.twig', array('form' => $handler->getForm()->createView()));
    }


}
