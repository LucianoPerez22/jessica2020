<?php

namespace App\Controller;

use App\Entity\Presupuestos;
use App\Service\Afip\WsFE;
use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\PresupuestosFilterType;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use DateTime;
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
class PresupuestosController extends BaseController
{
    /**
     * @Route(path="/presupuestos/list", name="presupuestos_index")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_LIST'])")    
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
         $form = $this->createForm(PresupuestosFilterType::class, null, ['csrf_protection' => false]);        
         $repo = $this->getDoctrine()->getRepository(Presupuestos::class);
       
         $dataResult = $helper->getDataResultFiltered($repo, $form);
         $dataResult['form'] = $form->createView();       

        return $this->render('presupuestos/index.html.twig', $dataResult);       
    }

     /**
     * @Route(path="/presupuestos/new", name="presupuestos_new")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_NEW'])")    
     */
    public function newAction(FindEntitiesHelper $helper)
    {
        //  $form = $this->createForm(PresupuestosFilterType::class, null, ['csrf_protection' => false]);        
        //  $repo = $this->getDoctrine()->getRepository(Presupuestos::class);
       
        //  $dataResult = $helper->getDataResultFiltered($repo, $form);
        //  $dataResult['form'] = $form->createView();       

        // return $this->render('presupuestos/index.html.twig', $dataResult);       
    }

}
