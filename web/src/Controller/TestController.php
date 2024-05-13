<?php

namespace App\Controller;


use App\Handler\Afip;
use App\Service\Afip\WsFE;
use App\Zennovia\Common\BaseController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class TestController extends BaseController
{
    /**
     * @Route("/test", name="test", methods={"GET","HEAD"})
     * @param $projectDir
     * @return Response
     */
    public function test($projectDir )
    {
        $rootDir = $projectDir;
        $certificado = dirname(__FILE__)."/certificados/JessyV2_2024_3bb88d0d3b04ab4e.crt";
        $clave = dirname(__FILE__)."/certificados/ClavePrivadaLucho.key";
        $cuit = str_replace('-', '', '20-30391056-6');
        $urlwsaa = "https://wsaa.afip.gov.ar/ws/services/LoginCms";

        //return new JsonResponse($certificado);

        $wsfe = new WsFE();
        $wsfe->CUIT = floatval($cuit);
        $wsfe->setURL("https://servicios1.afip.gov.ar/wsfev1/service.asmx");
        $wsfe->Login($certificado, $clave, $urlwsaa);

            return new JsonResponse($wsfe->$wsfe->RecuperaLastCMP(4, 1));


        //return new JsonResponse('Hola!');
    }
}
