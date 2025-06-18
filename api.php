<?php

require_once __DIR__ . '/vendor/autoload.php';
use Xthiago\PDFVersionConverter\Guesser\RegexGuesser;

error_reporting(0);
// api.php
$targetDir = __DIR__. '/file/old_pdf/'; // 设置上传文件的目标文件夹
$targetDirNew = __DIR__. '/file/new_pdf/'; // 设置上传文件的目标文件夹
$targetFile = $targetDir . basename($_FILES["pdfUpload"]["name"]); // 目标文件名



if (isset ( $_GET ["act"] ) && $_GET ["act"] != "") {

    $act = $_GET ["act"];
    switch ($act) {
        case 'getPdfList':
            $files = array();
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $filePath = $type == 'old'?$targetDir:$targetDirNew;
            $xdPath = '/file/'.$type.'_pdf/';
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filePath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'pdf') {
                    $files[] = array(
                        'name' => $file->getFilename(),
                        'mtime' => date('Y-m-d H:i:s', $file->getMTime()),
                        'path' => $xdPath
                    );
                }
            }
            // 返回 JSON 格式的文件列表
            echo json_encode(array('files' => $files));
            break;
        case 'delete':
            $fileName = basename($_GET['filename']);
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $filePath = $type == 'old'?$targetDir.$fileName:$targetDirNew . $fileName;

            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete the file.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'File does not exist.']);
            }
            break;
        case 'uploadfile':
            // 检查是否有文件被上传
            if (isset($_FILES["pdfUpload"])) {
                // 获取上传文件的临时名称
                $tmp_name = $_FILES["pdfUpload"]["tmp_name"];

                // 获取上传文件的 MIME 类型
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                // 检查 MIME 类型是否为 PDF
                if ($mimeType === 'application/pdf') {
                    // 检查文件是否已经存在
                    if (file_exists($targetFile)) {
                        echo "文件已存在。";
                    } else {
                        // 尝试移动上传的文件到目标目录
                        if (move_uploaded_file($tmp_name, $targetFile)) {
                            echo "文件上传成功。";
                        } else {
                            echo "抱歉，上传文件时出现了错误。";
                        }
                    }
                } else {
                    echo "不允许上传此文件类型。请上传 PDF 文件。";
                }
            } else {
                echo "没有文件被上传。";
            }
            break;
        case 'clear':
            $result = ['success' => true, 'message' => ''];
            $directories = [
                $targetDir,
                $targetDirNew
            ];
            foreach ($directories as $dir) {
                if (is_dir($dir)) {
                    if ($handle = opendir($dir)) {
                        while (false !== ($file = readdir($handle))) {
                            if ($file != "." && $file != "..") {
                                $filePath = $dir . $file;
                                // 只处理 .pdf 文件
                                if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                                    if (!unlink($filePath)) {
                                        $result['success'] = false;
                                        $result['message'] = '无法删除文件：' . $filePath;
                                    }
                                }
                            }
                        }
                        closedir($handle);
                    } else {
                        $result['success'] = false;
                        $result['message'] = '无法打开目录：' . $dir;
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = '目录不存在：' . $dir;
                }
            }
            echo json_encode($result);
            break;
        case 'downloadfile':
            $fileName = isset($_GET['filename']) ? $_GET['filename'] : '';
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $filePath = __DIR__ . '/file/'.$type.'_pdf/' . $fileName;
            if (file_exists($filePath)) {
                // 告诉浏览器这是一个文件下载响应
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream'); // 通用的二进制文件
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));

                // 读取文件内容并直接输出到浏览器
                readfile($filePath);
                exit;
            } else {
                // 如果文件不存在，返回错误信息
                header('HTTP/1.0 404 Not Found');
                echo "File not found.";
            }
            break;
        case 'optPdf':
            require_once __DIR__.'/editepdf/synthesisPdf.class.php';
            $pdfconfig['img_x'] = isset($_POST['img_x']) ? $_POST['img_x'] : '';
            $pdfconfig['img_y'] = isset($_POST['img_y']) ? $_POST['img_y'] : '';
            $pdfconfig['img_w'] = isset($_POST['img_w']) ? $_POST['img_w'] : '';
            $pdfconfig['img_h'] = isset($_POST['img_h']) ? $_POST['img_h'] : '';
            $pdfconfig['checkpdf'] = isset($_POST['checkpdf']) ? $_POST['checkpdf'] : '';

            $guesser = new RegexGuesser();
            $version = $guesser->guess('/www/wwwroot/axing/file/old_pdf/'.$pdfconfig['checkpdf']);
            if($version>1.4){
                $command = 'cd /www/wwwroot/axing/file/old_pdf &&  gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile="bate_'.$pdfconfig['checkpdf'].'" "'.$pdfconfig['checkpdf'].'" && rm -rf '.$pdfconfig['checkpdf'].' && mv bate_'.$pdfconfig['checkpdf'].' '.$pdfconfig['checkpdf'];
                shell_exec($command);
            }
            $s = new synthesisPdf();
            $new_filename = pathinfo($pdfconfig['checkpdf'] , PATHINFO_FILENAME) . '_1.' . pathinfo($pdfconfig['checkpdf'] , PATHINFO_EXTENSION);
            $reslt = $s->Synthesis(__DIR__.'/file/old_pdf/'.$pdfconfig['checkpdf'],__DIR__.'/file/img/a.png',__DIR__.'/file/new_pdf/'.$new_filename,$pdfconfig);
            if($reslt == ''){
                echo '盖章成功';
            }else{
                echo '失败。请重试';
            }
            break;
    }


}




exit();
