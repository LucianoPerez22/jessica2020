<?php

namespace App\Controller;

use App\Entity\Articulos;
use App\Entity\Stock;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface; 


/**
 * @Route("/")
 */
class StockController extends BaseController
{
    /**
     * @Route(path="/stock/articulo/{id}", name="stock_index")
     * @Security("user.hasRole(['ROLE_USER'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper, Articulos $articulo)
    {          
        $stockRepo = $this->getDoctrine()->getRepository(Stock::class);
        $stock     = $stockRepo->findBy(['idArticulo' => $articulo->getId()], ['id' => 'desc'], 20);
        return $this->render('stock/index.html.twig', ['articulos' => $articulo, 'stock' => $stock]);
    }

     /**
     * @Route(path="/stock/articulo/{id}/cargar/{cargar}", name="stock_cargar")
     * @Security("user.hasRole(['ROLE_STOCK_CARGAR'])")     
     * @return Response
     */
    public function cargarAction(UserInterface $user, Articulos $articulo, int $cargar)
    {                
        $stock = new Stock();       

        $em = $this->getDoctrine()->getManager();

        try {
            $stock->setIdArticulo($articulo);
            $stock->setCantidad($cargar);
            $stock->setFecha(new \DateTime());
            $stock->setUsuario("Stock: " . $user);

            $em->persist($stock);
            $em->flush();
        
            $this->addFlashSuccess('flash.stock.new.success');

            return $this->redirectToRoute('stock_index', ['id' => $articulo->getId()]);
            
        } catch (\Exception $e) {
            $this->addFlashError('flash.stock.new.error');
            $this->addFlashError($e->getMessage());            
        }                
    }
}
