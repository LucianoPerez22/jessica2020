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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * @Route("/")
 */
class VentasRecurrentesController extends AbstractController
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
* @Route(path="/venta-recurrentes/upload", name="venta_recurrentes_upload", methods="POST")
* @Security("user.hasRole(['ROLE_VENTAS_RECURRENTES'])")
*/
public function temporaryUploadAction(Request $request, Afip $afip)
    {
        $data = ($request->request->all());

        $date = ($data['date']);
        $amount = (floatval($data['amount']));

        /** @var UploadedFile $uploadedFile */
        $file = $request->files->get('myCsv');

        if (($open = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                $myDb[] = $data;
            }

            fclose($open);
        }


        $invoices = [];
        foreach ($myDb as $data){

            if (!$data || !array_key_exists(1, $data)) continue;

            $invoices[] = $afip->facturaElectronicaGenerar($data[0], floatval($data[1]), $date);

        }

        return $this->render('ventas_recurrentes/result.html.twig', ['invoices' => $invoices]);
    }

    /**
     * @Route(path="/venta-recurrentes/new", name="venta_recurrentes_new")
     * @Security("user.hasRole(['ROLE_VENTAS_RECURRENTES'])")
     */
    public function newAction(){
        return $this->render('ventas_recurrentes/new.html.twig', []);
    }


}
