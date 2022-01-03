<?php

namespace App\Controller;

use App\Entity\Articulos;
use App\Form\ArticulosType;
use App\Form\Filter\ArticulosFilterType;
use App\Form\Filter\MarcasFilterType;
use App\Form\Handler\SaveCommonFormHandler;
use App\Zennovia\Common\BaseController;
use App\Zennovia\Common\FindEntitiesHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/articulos")
 */
class ArticulosController extends BaseController
{
    /**
     * @Route(path="/admin/articulos/list", name="articulos_index")
     * @Security("user.hasRole(['ROLE_USER'])")
     * @param FindEntitiesHelper $helper
     * @return Response
     */
    public function indexAction(FindEntitiesHelper $helper)
    {
        $form = $this->createForm(ArticulosFilterType::class, null, ['csrf_protection' => false]);
        $repo = $this->getDoctrine()->getRepository('App:Articulos');

        $dataResult = $helper->getDataResultFiltered($repo, $form);
        $dataResult['form'] = $form->createView();
       
        return $this->render('articulos/index.html.twig', $dataResult);       
    }

    /**
     * @Route(path="/admin/articulos/new", name="articulos_new")
     * @Security("user.hasRole(['ROLE_MARCAS_NEW'])")
     * @param Request $request
     * @param SaveCommonFormHandler $handler
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, SaveCommonFormHandler $handler){
        $marca = new Articulos();       

        $handler->setClassFormType(SaveMarcasType::class);
        $handler->createForm($marca);
        
        if($handler->isSubmittedAndIsValidForm($request)){                
            try {                                                           
                if ($handler->processForm()) {
                    $this->addFlashSuccess('flash.articulos.new.success');
    
                    return $this->redirectToRoute('articulos_index');
                }               
                
            }catch (\Exception $e) {
                $this->addFlashError('flash.marcas.new.error');
                $this->addFlashError($e->getMessage());
            }                           
        }

        return $this->render('marcas/new.html.twig', array('form' => $handler->getForm()->createView()));
    }

    /**
     * @Route("/{id}", name="articulos_show", methods={"GET"})
     */
    public function show(Articulos $articulo): Response
    {
        return $this->render('articulos/show.html.twig', [
            'articulo' => $articulo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="articulos_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Articulos $articulo): Response
    {
        $form = $this->createForm(ArticulosType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('articulos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articulos/edit.html.twig', [
            'articulo' => $articulo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="articulos_delete", methods={"POST"})
     */
    public function delete(Request $request, Articulos $articulo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articulo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($articulo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('articulos_index', [], Response::HTTP_SEE_OTHER);
    }
}
