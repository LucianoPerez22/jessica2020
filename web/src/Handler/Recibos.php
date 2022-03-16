<?php
namespace App\Handler;

use Dompdf\Dompdf;
use Dompdf\Options;

class Recibos
{
    public function imprimirRecibo($html){                                            
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', TRUE);
        
        $dompdf = new Dompdf($pdfOptions);
                            
        $dompdf->loadHtml($html); 
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'           
        //$dompdf->setPaper(array(0,0,720,600), 'portrait');
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        
        $dompdf->stream("recibo.pdf", array("Attachment" => false));

        exit(0);               
}

}