<?php

namespace App\Controller;

use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\InformesTipoType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/")
 */
class InformesController extends BaseController
{
    /**
     * @Route(path="admin/informes/tipo", name="informes_tipo")
     * @Security("user.hasRole(['ROLE_INFORME_TIPO'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function informesAction(FindEntitiesHelper $helper)
    {    
        $form = $this->createForm(InformesTipoType::class, null);    
        return $this->render('informes/tipoInformes.html.twig', ['form' => $form->createView()] );       
    }

    /**
     * @Route(path="admin/informes/ventas/{desde}/{hasta}", name="informes_ventas")
     * @Security("user.hasRole(['ROLE_INFORME_VENTAS'])")
     * @return Response
     */
    public function informesVentasAction(DateTime $desde = null, DateTime $hasta = null)
    {   
        $ventas = []; 
        $ventas = $this->getDoctrine()->getRepository('App:Ventas')
                                                ->findByDate($desde, $hasta);
        
        return $this->render('informes/ventas.html.twig', ['ventas' => $ventas]);
    }
          
}
