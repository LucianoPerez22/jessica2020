<?php

namespace App\Controller;

use App\Entity\Articulos;
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
     * @Security("user.hasRole(['ROLE_INFORMES'])")
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
     * @Security("user.hasRole(['ROLE_INFORMES'])")
     * @return Response
     */
    public function informesVentasAction(DateTime $desde = null, DateTime $hasta = null)
    {   
        $ventas = []; 
        $ventas = $this->getDoctrine()->getRepository('App:Ventas')
                                                ->findByDate($desde, $hasta);
        
        return $this->render('informes/ventas.html.twig', [
                'ventas' => $ventas,
                'desde'     => $desde->format('Y-m-d'),
                'hasta'     => $hasta->format('Y-m-d')
            ]);
    }

    /**
     * @Route(path="admin/informes/ventasshow/{id}/{desde}/{hasta}", name="informeVentas_show")
     * @Security("user.hasRole(['ROLE_INFORMES'])")
     * @param Ventas $venta
     * @return Response
     */
    public function informeVentasShowAction(Ventas $venta, DateTime $desde = null, DateTime $hasta = null)
    {               
        return $this->render('informes/ventasShow.html.twig', [
                'ventas' => $venta,
                'desde'     => $desde->format('Y-m-d'),
                'hasta'     => $hasta->format('Y-m-d')
            ]);
    }

     /**
     * @Route(path="admin/informes/articulos/{desde}/{hasta}", name="informes_articulos")
     * @Security("user.hasRole(['ROLE_INFORMES'])")
     * @return Response
     */
    public function informesArticulosAction(DateTime $desde = null, DateTime $hasta = null)
    {   
        $ventasArt = []; 
        $ventasArt = $this->getDoctrine()->getRepository('App:VentasArt')
                                                ->findByDate($desde, $hasta);               
        
        return $this->render('informes/articulos.html.twig', [
            'ventasArt' => $ventasArt,
            'desde'     => $desde->format('Y-m-d'),
            'hasta'     => $hasta->format('Y-m-d')
        ]);
    }

     /**
     * @Route(path="admin/informes/articulosshow/{id}/{desde}/{hasta}", name="informeArticulos_show")
     * @Security("user.hasRole(['ROLE_INFORMES'])")
     * @param Articulos $articulo
     * @return Response
     */
    public function informeArticulosShowAction(Articulos $articulo, DateTime $desde, DateTime $hasta)
    {                       
        $ventasArt = $this->getDoctrine()->getRepository('App:VentasArt')
                                                ->findByDateAndProduct($articulo, $desde, $hasta); 

        return $this->render('informes/articulosShow.html.twig', [
                'ventas' => $ventasArt,
                'desde'  => $desde->format('d-m-Y'),
                'hasta'  => $hasta->format('d-m-Y'),
            ]);
    }
}
