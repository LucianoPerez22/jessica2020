<?php

namespace App\Controller;

use App\Entity\Presupuestos;
use App\Entity\PresupuestosArt;
use App\Form\Type\SavePresupuestosArtType;
use App\Service\Afip\WsFE;
use App\Entity\Stock;
use App\Entity\Ventas;
use App\Entity\VentasArt;
use App\Form\Filter\PresupuestosFilterType;
use App\Form\Filter\VentasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Form\Type\SavePresupuestosType;
use App\Form\Type\SaveVentasArtType;
use App\Form\Type\SaveVentasType;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use DateTime;
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
    public function newAction(Request $request, SaveCommonFormHandler $handler, UserInterface $user)
    {
        $presupuesto = new Presupuestos();       

        $handler->setClassFormType(SavePresupuestosType::class);
        $handler->createForm($presupuesto);

        // Validate form first
        if (!$handler->isSubmittedAndIsValidForm($request)) {
            return $this->render('presupuestos/new.html.twig', ['form' => $handler->getForm()->createView()]);
        }
        //Obtengo Ultimo id presupuesto
        $numero = $this->getDoctrine()->getRepository('App:Presupuestos')->findLastNumber();
        $numId  = $numero->getResult();
        ($numId[0][1] === null) ? $numId[0][1] = 1 : $numId[0][1] = intval($numId[0][1]) + 1;
        
        $data = $request->request->all();

        try {
            if (array_key_exists('art', $data)){
                if ($data['art']['total0'] == 0){
                    $this->addFlashError('flash.presupuestos.new.error');
                    return $this->render('presupuestos/new.html.twig', array('form' => $handler->getForm()->createView()));
                }
                //$clienteRepo = $this->getDoctrine()->getRepository('App:ListaDeUsuarios')->findOneBy(['id' => $data['save_presupuestos']['cliente']]);

                $presupuesto->setCliente($data['save_presupuestos']['cliente']); //$clienteRepo->getNombre()
                $presupuesto->setUser($user);
                $presupuesto->setFecha(new \DateTime());

                $total = 0;
                foreach ($data['art'] as $key => $value) {
                    (substr($key, 0, 5) == 'total') ? $total += floatval($value) : '';
                }

                $presupuesto->setTotal($total);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($presupuesto);
                $manager->flush($presupuesto);

                foreach ($data['art'] as $key => $value) {
                    if (substr($key, 0, 4) == 'cant') {
                        $articulos = new PresupuestosArt();

                        $articulos->setCantidad($value);
                    }

                    if (substr($key, 0, 5) == 'idArt') {
                        $artRepo = $this->getDoctrine()->getRepository('App:Articulos');
                        $articulo  = $artRepo->findOneBy(["id" => intval($value)]);

                        $articulos->setIdArt($articulo);
                    }
                    (substr($key, 0, 6) == 'precio') ? $articulos->setPrecio(floatval($value)) : '';
                    if (substr($key, 0, 5) == 'total') {
                        $articulos->setPresupuesto($presupuesto);
                        $articulos->setTotal(floatval($value));

                        //dump($articulos); die;
                        $manager->persist($articulos);
                        $manager->flush($articulos);
                    }
                }

                $this->addFlashSuccess('flash.presupuestos.new.success');

                return $this->redirectToRoute('presupuestos_show', ['id'=> $presupuesto->getId()]);
            }
        } catch (\Exception $e) {
            $this->addFlashError('flash.presupuestos.new.error');
            $this->addFlashError($e->getMessage());
        }

        return $this->render('presupuestos/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

     /**
     * @Route(path="/presupuestos/art/{num_control}", name="ajax_presupuestos_art")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_NEW'])")
     */
    public function ajaxVentaArt(Request $request, int $num_control): Response
    {
        $form = $this->createForm(SavePresupuestosArtType::class, null, ['num_control' => $num_control]);

        return $this->render('ventas/articulos.html.twig', [
            'form' => $form->createView(),
            'num_control' => $num_control
        ]);
    }

    /**
     * @Route("/presupuestos/articulo/precio/{id}", name="ajax_get_articulo_precio_presupuesto", methods={"GET"})
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
     * @Route(path="/presupuestos/view/{id}", name="presupuestos_show")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_VIEW'])")
     * @param Presupuestos $venta
     * @return Response
     */
    public function viewAction(Presupuestos $presupuesto)
    {                   
        return $this->render('presupuestos/show.html.twig', ['venta' => $presupuesto]);
    }   

     /** 
     * @Route(path="/presupuesto/imprimir/{id}", name="presupuestos_imprimir")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_VIEW'])")  
     * @param Presupuestos $venta
     * @return Response
     */
    public function reciboAction(Presupuestos $venta){                                            
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Recupere el HTML generado en nuestro archivo twig
        $html = $this->renderView('presupuestos/imprimir.html.twig', ['venta' => $venta]);        
        
        $dompdf->loadHtml($html); 
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'           
        //$dompdf->setPaper(array(0,0,720,600), 'portrait');
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        
        $dompdf->stream("presupuesto.pdf", array("Attachment" => false));

        exit(0);               
}

  /**
     * Eliminar una entidad Presupuestos.
     *
     * @Route(path="/admin/presupuestos/{id}/delete", name="presupuestos_delete")
     * @Security("user.hasRole(['ROLE_PRESUPUESTOS_DELETE'])")
     * @param Presupuestos $entity
     * @param EntityManagerHelper $helper
     * @return RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(Presupuestos $entity, EntityManagerHelper $helper, UserInterface $user)
    {
        try {
            $ventasArtRepo = $this->getDoctrine()->getRepository(PresupuestosArt::class);
            $ventasArt     = $ventasArtRepo->findBy(['presupuesto' => $entity]);
            
            $em = $this->getDoctrine()->getManager();

            foreach ($ventasArt as $key) {              
                $em->remove($key);
                $em->flush();
            }
                      
             $helper->doDelete($entity);
             $this->addFlashSuccess('flash.presupuestos.delete.success');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlashError('flash.presupuestos.delete.error');
        }

        return $this->redirectToRoute('presupuestos_index');
    }     

}
