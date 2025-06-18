<?php
require_once __DIR__.'/editepdf/synthesisPdf.class.php';
$s = new synthesisPdf();
$pdfconfig['img_x'] = 115;
$pdfconfig['img_y'] = 105;
$pdfconfig['img_w'] = 45;
$pdfconfig['img_h'] = 45;
$reslt = $s->Synthesis(__DIR__.'/file/old_pdf/xxx2.pdf',__DIR__.'/file/img/a.png',__DIR__.'/file/new_pdf/xxx2_1.pdf',$pdfconfig);
var_dump($reslt);


exit();