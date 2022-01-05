<?php

namespace App\Controller;

use App\Entity\Articulos;
use App\Entity\Stock;
use App\Form\Handler\SaveCommonFormHandler;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;


/**
 * @Route("/")
 */
class StockController extends BaseController
{
    /**
     * @Route(path="/stock/articulo/{id}/view", name="stock_index")
     * @Security("user.hasRole(['ROLE_USER'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper, Articulos $articulo)
    {        
        return $this->render('stock/index.html.twig', ['articulos' => $articulo]);
    }

   
}
