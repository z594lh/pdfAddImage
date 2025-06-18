<?php
/**
 * Created by PhpStorm.
 * User: Zhongyw
 * Date: 2019/1/2
 * Time: 11:52
 */
/*require_once('./fpdf/fpdf.php');*/
/*require_once('./fpdi/fpdi.php');*/
require_once __DIR__.'/fpdf/fpdf.php';
require_once __DIR__.'/fpdi/fpdi.php';
class synthesisPdf
{
    public function Synthesis($pdfpath,$imgpath,$newpath,$pdfconfig){
        $pdf = new FPDI();
        $pageCount = $pdf->setSourceFile($pdfpath);
        $printPageNum = isset($pdfconfig['page_num'])?$pdfconfig['page_num']:1;
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++)
        {
            if($pageNo == $printPageNum){
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                if ($size['w'] > $size['h']) $pdf->AddPage('L', array($size['w'], $size['h']));
                else $pdf->AddPage('P', array($size['w'], $size['h']));
                $pdf->useTemplate($templateId);
                $img_x = isset($pdfconfig['img_x'])?$pdfconfig['img_x']:0;
                $img_y = isset($pdfconfig['img_y'])?$pdfconfig['img_y']:0;
                $img_w = isset($pdfconfig['img_w'])?$pdfconfig['img_w']:0;
                $img_h = isset($pdfconfig['img_h'])?$pdfconfig['img_h']:0;
                $pdf->image($imgpath,$img_x,$img_y,$img_w,$img_h,'png');
            }else{
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                if ($size['w'] > $size['h']) $pdf->AddPage('L', array($size['w'], $size['h']));
                else $pdf->AddPage('P', array($size['w'], $size['h']));
                $pdf->useTemplate($templateId);
            }
        }
        return $pdf->Output($newpath,'F');
    }

    /**
     * 睿邑达裁剪PDF
     * @param $pdfpath
     * @param $newpath
     * @param int $width
     * @param int $height
     * @return string
     */
    public function cropping($pdfpath,$newpath,$width=0,$height=0){
        $pdf = new FPDI();
        $pdf->setSourceFile($pdfpath);
        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        /*宽度不够不裁剪，直接把原面单复制到_1面单*/
        if($size['w']<200){
            $command = 'cp '.$pdfpath.' '.$newpath;
            shell_exec($command);
            return '';
        }
        $newWidth = $width>0?$width:420;
        $newHeight = $height>0?$height:null;
        $pdf->AddPage();
        $pdf->useTemplate($templateId, 0, 0, $newWidth, $newHeight,true);
        return $pdf->Output($newpath, 'F');
    }
}
