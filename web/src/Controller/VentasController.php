<?php

namespace App\Controller;

use App\Entity\Articulos;
use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Handler\Afip;
use App\Handler\Recibos;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * @Route("/admin")
 */
class VentasController extends BaseController
{
    /**
     * @Route(path="/venta/list", name="ventas_index")
     * @Security("user.hasRole(['ROLE_VENTAS_LIST'])")
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form=$this->createForm(VentasFilterType::class, null, ['csrf_protection'=>false]);
        $repo=$this->getDoctrine()->getRepository(Ventas::class);
        $dataResult=$helper->getDataResultFiltered($repo, $form);
        $dataResult['form']=$form->createView();
        return $this->render('ventas/index.html.twig', $dataResult);
    }

    /**
     * @Route(path="/venta/new", name="venta_new")
     * @Security("is_granted('ROLE_VENTAS_NEW')")
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler, UserInterface $user)
    {
        $venta = new Ventas();
        $handler->setClassFormType(SaveVentasType::class);
        $handler->createForm($venta);

        $data = $request->request->all();

        // Validate form first
        if (!$handler->isSubmittedAndIsValidForm($request)) {
            return $this->render('ventas/new.html.twig', ['form' => $handler->getForm()->createView()]);
        }

        $venta->setEstado('ok');

        // Process form if valid
        if ($handler->processForm()) {
            $venta->setUser($user);
            $venta->setFecha(new \DateTime());
            $venta->setHora(date("h:i:sa"));
            $venta->setEstado('ok');

            $numero = $this->getDoctrine()->getRepository(Ventas::class)->findLastNumber();
            $numeroR = $numero->getResult();
            $venta->setNumero(intval($numeroR[0][1]) + 1);
            $venta->setCae(null);
            $venta->setTipo('R');
            $venta->setCaeVenc(null);

            $total = 0;
            $hasTotal = false;
            foreach ($data['save_ventas_art'] as $key => $value) {
                // Check if the key starts with 'total'
                if (substr($key, 0, 5) == 'total') {
                    // Accumulate the total value
                    $total += floatval($value);
                    $hasTotal = $hasTotal || $value > 0;
                }
            }

            if (!$hasTotal) {
                // Handle the case where total is not greater than 0
                $this->addFlashError('flash.ventas.new.error.no_total');
                return $this->render('ventas/new.html.twig', ['form' => $handler->getForm()->createView()]);
            }

            $venta->setTotal($total);

            $ventaRepo = $this->getDoctrine()->getRepository(Ventas::class)->findOneBy([], ['id' => 'desc']);
            $manager = $this->getDoctrine()->getManager();

            try {
                foreach ($data['save_ventas_art'] as $key => $value) {
                    if (substr($key, 0, 4) == 'cant') {
                        $articulos = new VentasArt();
                        $stock = new Stock();
                        $articulos->setCant($value);
                        $stock->setCantidad($value * -1);
                        $stock->setFecha(new \DateTime());
                        $stock->setUsuario("Venta: " . $user);
                    }
                    if (substr($key, 0, 5) == 'idArt') {
                        $artRepo = $this->getDoctrine()->getRepository(Articulos::class);
                        $articulo = $artRepo->findOneBy(["id" => intval($value)]);
                        $articulos->setIdArt($articulo);
                        $stock->setIdArticulo($articulo);
                    }
                    (substr($key, 0, 6) == 'precio') ? $articulos->setPrecio(floatval($value)) : '';
                    if (substr($key, 0, 5) == 'total') {
                        $articulos->setIdVentas($ventaRepo);
                        $articulos->setTotal(floatval($value));
                        $manager->persist($articulos);
                        $manager->flush();
                        $manager->persist($stock);
                        $manager->flush();
                    }
                }

                $this->addFlashSuccess('flash.ventas.new.success');
                return $this->redirectToRoute('venta_show', ['id' => $venta->getId()]);
            } catch (\Exception $e) {
                $this->addFlashError('flash.ventas.new.error');
                $this->addFlashError($e->getMessage());
            }
        }

        return $this->render('ventas/new.html.twig', ['form' => $handler->getForm()->createView()]);
    }



    /**
     * @Route(path="/venta/view/{id}", name="venta_show")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")
     */
    public function viewAction(Ventas $venta)
    {            
        return $this->render('ventas/show.html.twig', ['venta' => $venta]);
    }

    /**
     * @Route("/venta/art/{num_control}", name="ajax_venta_art")
     */
    public function ajaxVentaArt(Request $request, int $num_control): Response
    {
        $form = $this->createForm(SaveVentasArtType::class, null, ['num_control' => $num_control]);

        return $this->render('ventas/articulos.html.twig', [
            'form' => $form->createView(),
            'num_control' => $num_control
        ]);
    }

    /**
     * @Route("/venta/articulo/precio/{id}", name="ajax_get_articulo_precio", methods={"GET"})
     */
    public function ajaxGetArticuloPrecioAction(Articulos $articulo)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['precio' => $articulo->getPrecio()]);
        }

        throw $this->createNotFoundException('This is not an AJAX request');
    }


    /**
     * @Route(path="/venta/factura/{id}", name="venta_factura")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")
     */
    public function facturaAction(Ventas $venta, Afip $afip)
    {              
        $data = $afip->facturaElectronica($venta);        
               
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('ventas/factura.html.twig', ['venta' => $venta, 'data' =>$data]);                                       

        $afip->imprimirFactura($html);
    }   
    
     /** 
     * @Route(path="/venta/recibo/{id}", name="venta_recibo")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")
     */
    public function reciboAction(Ventas $venta){  
        $recibo = new Recibos;                                          
        
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('ventas/recibo.html.twig', ['venta' => $venta]);        
        
        $recibo->imprimirRecibo($html);         
    }

     /**
     * Eliminar una entidad Empleados.
     *
     * @Route(path="/admin/venta/{id}/delete", name="ventas_delete")
     * @Security("user.hasRole(['ROLE_VENTAS_DELETE'])")
     */
    public function deleteAction(Ventas $entity, EntityManagerHelper $helper, UserInterface $user, Afip $afip)
    {
        try {
            $ventasArtRepo = $this->getDoctrine()->getRepository(VentasArt::class);
            $ventasArt     = $ventasArtRepo->findBy(['idVentas' => $entity]);
                        
            $em = $this->getDoctrine()->getManager();

            foreach ($ventasArt as $key) {
                $stock = new Stock();
                
                $stock->setIdArticulo(($key->getidArt())); 
                $stock->setCantidad($key->getCant());
                $stock->setFecha(new \DateTime());
                $stock->setUsuario("Compra Cancelada: " . $user);
                
                $em->persist($stock);
                $em->flush();
            
                $em->remove($key);
                $em->flush();
            }
                      
             $helper->doDelete($entity);

             $data = $afip->facturaElectronica($entity);        
               
            $html = $this->renderView('ventas/notaCredito.html.twig', ['venta' => $entity, 'data' =>$data]);

            $afip->imprimirNotaCredito($html);

             $this->addFlashSuccess('flash.ventas.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.ventas.delete.error');
        }

        return $this->redirectToRoute('ventas_index');
    }     

     /**
     * @Route(path="/admin/venta/obtener", name="obtener_comprobantes")
     * @Security("user.hasRole(['ROLE_VENTAS_VIEW'])")      
     */
    public function obtenerComprobantes(Afip $afip){
        $data = $afip->obtenerComprobantes();
        dump($data);
        die;
    }
}
