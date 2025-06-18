<?php

require_once('./fpdf/fpdf.php');
require_once('./fpdi/fpdi.php');

//pic_watermark
$pdf = new FPDI();

// get the page count
$pageCount = $pdf->setSourceFile('86613955.pdf');
    
// iterate through all pages
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++)
{
    // import a page
    $templateId = $pdf->importPage($pageNo);

    // get the size of the imported page
    $size = $pdf->getTemplateSize($templateId);

    // create a page (landscape or portrait depending on the imported page size)
    if ($size['w'] > $size['h']) $pdf->AddPage('L', array($size['w'], $size['h']));
    else $pdf->AddPage('P', array($size['w'], $size['h']));

    // use the imported page
    $pdf->useTemplate($templateId);

    // Place the graphics
    $pdf->image("fenjian.png", 78, 143, 15);

}
$path = __DIR__.'/../img_pdf/pic.pdf';
$pdf->Output($path,'F');


