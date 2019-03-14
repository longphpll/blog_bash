<?php

// 选择导出字段--Execl导出
function excelExport($name = '', $headArr, $data)
{

    $fileName = $name . "_" . date("YmdHis ") . ".xlsx";

    $objPHPExcel = new \PHPExcel();

    $cellKey = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
        'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
    );

    // 合并单元格
    $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellKey[count($headArr) - 1] . '1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
    // 插入标题数据
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $name);
    // 加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    // 设置字体大小
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
    // 设置对齐方式
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中

    $key = ord("A");

    // 表头数据处理
    foreach ($headArr as $v) {

        $cellName = chr($key);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName . '2', $v);// 设置表头数据
        $objPHPExcel->getActiveSheet()->getStyle($cellName . '2')->getFont()->setBold(true);//设置是否加粗
        $objPHPExcel->getActiveSheet()->getStyle($cellName . '2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
        $key += 1;
    }

    $column = 3;

    foreach ($data as $key => $rows) { // 行数

        $span = ord("A");

        foreach ($rows as $keyName => $value) { // 数据写入
            // 计算记录数
            if (isset($rows['record'])) {
                $tal = count($rows['record']);
            } else {
                $tal = 0;
            }
            if ($keyName == 'record') {
                foreach ($rows['record'] as $ke => $val) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($span + $ke) . $column, $val);
                    $objPHPExcel->getActiveSheet()->getStyle(chr($span + $ke) . $column)->getAlignment()->setWrapText(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension(chr($span + $ke))->setWidth(120);//设置列宽度
                }
            } elseif ($keyName == 'img') {
                foreach ($rows['img'] as $ky => $vl) {
                    $objDrawing[$ky] = new \PHPExcel_Worksheet_Drawing();
                    /*设置图片路径 */
                    $objDrawing[$ky]->setPath($vl['path']);
                    /*设置图片高度*/
                    $objDrawing[$ky]->setHeight(40);//照片高度
                    $objDrawing[$ky]->setWidth(80); //照片宽度
                    /*设置图片要插入的单元格*/
                    $objDrawing[$ky]->setCoordinates(chr($span + $ky + $tal) . $column);
                    /*设置图片所在单元格的格式*/
                    $objDrawing[$ky]->setOffsetX(5);
                    $objDrawing[$ky]->setRotation(5);
                    $objDrawing[$ky]->getShadow()->setVisible(true);
                    $objDrawing[$ky]->getShadow()->setDirection(80);
                    $objDrawing[$ky]->setWorksheet($objPHPExcel->getActiveSheet());
                }
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($span) . $column, $value);
                $objPHPExcel->getActiveSheet()->getStyle(chr($span) . $column)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
                $objPHPExcel->getActiveSheet()->getStyle(chr($span) . $column)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
                $objPHPExcel->getActiveSheet()->getColumnDimension(chr($span))->setWidth(50);//设置列宽度
                $span++;

            }
        }

        $column++;

    }

    $file_name = iconv("utf-8", "gb2312", $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表


    // 2003 excel xls

    // header('Content-Type: application/vnd.ms-excel');

    // header('Content-Disposition: attachment;filename="links_out'.$timestamp.'.xls"');

    // header('Cache-Control: max-age=0');

    // $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    // $objWriter->save('php://output');

    // 2007 execl xlsx
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    header("Content-Disposition: attachment;filename=" . $file_name);

    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();
}

// 自定义布局--Execl导出
function customExport($name = '', $rowTotal, $columnTotal, $data)
{

    error_reporting(0);

    $fileName = $name . "_" . date("YmdHis ");

    $objPHPExcel = new \PHPExcel();

    $beginSheet = ord("A");

    $endSheet = ord("S");

    $cellKey = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
        'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
    );

    for ($i = 0; $i < $rowTotal; $i++) {
        switch ($i) {
            case 1:
                // 设置自动换行
                $objPHPExcel->getActiveSheet()->getStyle(chr($beginSheet) . $i . ':' . chr($endSheet) . $rowTotal)->getAlignment()->setWrapText(TRUE);
                // 设置对齐方式--居中对齐
                $objPHPExcel->getActiveSheet()->getStyle(chr($beginSheet) . $i . ':' . chr($endSheet) . $rowTotal)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
                // 合并单元格,处理标题
                $objPHPExcel->getActiveSheet()->mergeCells(chr($beginSheet) . $i . ':' . chr($endSheet) . $i);//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
                // 插入标题数据
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($beginSheet) . $i, $name);
                break;
            case 2:
                $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', '产地检疫');

                $objPHPExcel->getActiveSheet()->mergeCells('M2:S2');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2', '调运检疫');
                break;
            case 3:
                $cont = [
                    "检疫内容",
                    "林木种子（吨）",
                    "种苗繁育基地（万亩）",
                    "花卉基地（万亩）",
                    "经济林基地（万亩）",
                    "中药材基地（万亩）",
                    "用材林（万亩）"
                ];
                foreach ($cont as $key => $value) {
                    $objPHPExcel->getActiveSheet()->mergeCells(chr($beginSheet) . $i . ':' . chr($beginSheet) . ($i + 1));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($beginSheet) . $i, $value);
                    $beginSheet++;
                }

                $twoCont  = [
                    "木材（万㎡）",
                    "竹材（万根）",
                    "果品（吨）",
                    "中药材（吨）",
                    "花卉（万株）"
                ];
                $twoSheet = ord('H');
                $objPHPExcel->getActiveSheet()->mergeCells('H3:L3');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H3', '储存、加工场所及市场');

                foreach ($twoCont as $key => $value) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($twoSheet) . ($i + 1), $value);
                    $twoSheet++;
                }

                $jy_title = [
                    "检疫内容",
                    "林木种子（吨）",
                    "苗木及其他繁殖材料（万株）",
                    "竹材（万株）",
                    "果品（吨）",
                    "花卉（万株）",
                    "药材（吨）"
                ];
                $jy_Sheet = ord('M');

                foreach ($jy_title as $key => $value) {
                    $objPHPExcel->getActiveSheet()->mergeCells(chr($jy_Sheet) . $i . ':' . chr($jy_Sheet) . ($i + 1));
                    // $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);//设置列宽度
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($jy_Sheet) . $i, $value);
                    $jy_Sheet++;
                }

                break;
            default:
                # code...
                break;
        }
        # code...
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', '应检数');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', $data['place_tree_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C5', $data['seed_breed_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D5', $data['flowers_base_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E5', $data['economic_forest_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F5', $data['chinese_medicine_base_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G5', $data['timber_forest_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H5', $data['wood_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I5', $data['bamboo_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J5', $data['fruit_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K5', $data['chinese_medicine_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K6', $data['place_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L5', $data['flowers_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L6', $data['place_tree_real']);


    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M5', '检疫数');


    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N5', $data['dispatch_tree_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N6', $data['dispatch_tree_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O5', $data['dispatch_breed_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O6', $data['dispatch_breed_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P5', $data['dispatch_bamboo_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P6', $data['dispatch_bamboo_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q5', $data['dispatch_fruit_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q6', $data['dispatch_fruit_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R5', $data['dispatch_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R6', $data['dispatch_real']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S5', $data['dispatch_medicine_should']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S6', $data['dispatch_medicine_real']);


    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A6', '实检数');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M6', '复检数');

    $objPHPExcel->getActiveSheet()->mergeCells('B7:L7');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B7', $data['epidemic_rate']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N7', $data['epidemic_number']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N8', $data['quarantine_treatment']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B8', $data['quarantine_rate']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B9', $data['quarantine_treatment_rate']);


    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A7', '带疫率');

    $objPHPExcel->getActiveSheet()->mergeCells('N7:S7');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M7', '带疫数');


    $objPHPExcel->getActiveSheet()->mergeCells('B8:L8');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A8', '检疫率');

    $objPHPExcel->getActiveSheet()->mergeCells('B9:L9');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A9', '检疫处理率');

    $objPHPExcel->getActiveSheet()->mergeCells('M8:M9');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M8', '带疫处理');
    $objPHPExcel->getActiveSheet()->mergeCells('N8:S9');


    $objPHPExcel->getActiveSheet()->mergeCells('A10:A13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A10', '无检疫对象苗圃（含其他种苗繁育基地');

    $objPHPExcel->getActiveSheet()->mergeCells('B10:B11');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B10', '国家级');

    $objPHPExcel->getActiveSheet()->mergeCells('C10:D10');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C10', '数量（个）');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E10', $data['country_number']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E11', $data['country_area']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E12', $data['province_number']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E13', $data['province_area']);


    $objPHPExcel->getActiveSheet()->mergeCells('C11:D11');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C11', '面积（h㎡）');

    $objPHPExcel->getActiveSheet()->mergeCells('B12:B13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B12', '省级');

    $objPHPExcel->getActiveSheet()->mergeCells('C12:D12');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C12', '数量（个）');

    $objPHPExcel->getActiveSheet()->mergeCells('C13:D13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C13', '面积（h㎡）');

    $objPHPExcel->getActiveSheet()->mergeCells('F10:H11');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F10', '收取检疫费（万元）');
    $objPHPExcel->getActiveSheet()->mergeCells('I10:L11');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I10', $data['fee']);


    $objPHPExcel->getActiveSheet()->mergeCells('F12:H13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F12', '处理违章案件');

    $objPHPExcel->getActiveSheet()->mergeCells('I12:J12');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I12', '次数（次）');
    $objPHPExcel->getActiveSheet()->mergeCells('K12:L12');


    $objPHPExcel->getActiveSheet()->mergeCells('I13:J13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I13', '面积（h㎡）');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('k12', $data['frequency']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('k13', $data['fine']);

    $objPHPExcel->getActiveSheet()->mergeCells('K13:L13');


    $objPHPExcel->getActiveSheet()->mergeCells('M10:M13');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M10', '备注');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N10', $data['remark']);

    $objPHPExcel->getActiveSheet()->mergeCells('N10:S13');

    $file_name = iconv("utf-8", "gb2312", $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

    header('Content-Type:application/vnd.ms-excel;charset=utf-8;');

    header('Content-Disposition:attachment;filename="' . $fileName . '.xlsx"');

    header('Cache-Control:max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();
}

// 统计--Execl导出
function statisticsExport($name = '', $headArr, $data)
{

    $fileName = $name . "_" . date("YmdHis ") . ".xlsx";

    //实例化 extend 目录下的 PHPExcel 类
    $objPHPExcel = new \PHPExcel();

    $cellKey = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
        'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
    );

    // 处理标题

    // 合并单元格
    $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellKey[count($headArr) - 1] . '1');//合并单元格（如果要拆分单元格是需要先合并再拆分的，否则程序会报错）
    // 插入标题数据
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $name);
    // 加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    // 设置字体大小
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
    // 设置对齐方式
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中

    $key = ord("A");

    // 表头数据处理
    foreach ($headArr as $v) {

        $cellName = chr($key);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName . '2', $v);// 设置表头数据
        $objPHPExcel->getActiveSheet()->getStyle($cellName . '2')->getFont()->setBold(true);//设置是否加粗
        $objPHPExcel->getActiveSheet()->getStyle($cellName . '2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
        $key += 1;
    }

    $column = 3;
    foreach ($data as $key => $rows) { // 行数

        $span = ord("A");

        foreach ($rows as $keyName => $value) { // 数据写入

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($span) . $column, $value);
            $objPHPExcel->getActiveSheet()->getStyle(chr($span) . $column)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置水平居中
            $objPHPExcel->getActiveSheet()->getStyle(chr($span) . $column)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);//设置垂直居中
            $objPHPExcel->getActiveSheet()->getColumnDimension(chr($span))->setWidth(50);//设置列宽度
            $span++;

        }

        $column++;

    }

    $file_name = iconv("utf-8", "gb2312", $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

    header('Content-Type: application/vnd.ms-excel;charset=utf-8;');

    header("Content-Disposition: attachment;filename=" . $file_name);

    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();
}

/**
 * 生成xml文件
 * @param xml_file xml文件名
 *        xml_path 文件路径
 *        img_size 图片大小
 *        data 相关数据
 * @return json code
 */
function makeXml($xml_file, $xml_path, $img_size, $data = [])
{
    //创建一个新的 DOM文档
    $dom = new DomDocument();
    //在根节点创建 annotation 标签
    $annotation = $dom->createElement('annotation');
    $dom->appendChild($annotation);

    //在 annotation 节点下创建 folder 标签
    $folder = $dom->createElement('folder');

    $folder->appendChild($dom->createTextNode('Saved Pictures'));
    $annotation->appendChild($folder);

    //在 annotation 节点下创建 filename 标签
    $filename = $dom->createElement('filename');

    $filename->appendChild($dom->createTextNode($xml_file));
    $annotation->appendChild($filename);

    //在 annotation 节点下创建 path 标签
    $path = $dom->createElement('path');

    $path->appendChild($dom->createTextNode($xml_path));
    $annotation->appendChild($path);

    //在 annotation 节点下创建 source 标签 相关信息
    $source   = $dom->createElement('source');
    $database = $dom->createElement('database');

    $database->appendChild($dom->createTextNode('Unknown'));
    $source->appendChild($database);
    $annotation->appendChild($source);

    //在 annotation 节点下创建 size 标签 相关信息
    $size   = $dom->createElement('size');
    $width  = $dom->createElement('width');
    $height = $dom->createElement('height');
    $depth  = $dom->createElement('depth');

    $width->appendChild($dom->createTextNode($data['width']));
    $height->appendChild($dom->createTextNode($data['height']));
    $depth->appendChild($dom->createTextNode('3'));

    $size->appendChild($width);
    $size->appendChild($height);
    $size->appendChild($depth);
    $annotation->appendChild($size);

    //在 annotation 节点下创建 segmented 标签
    $segmented = $dom->createElement('segmented');

    $segmented->appendChild($dom->createTextNode('0'));
    $annotation->appendChild($segmented);

    //在 annotation 节点下创建 object 标签 相关信息
    $object    = $dom->createElement('object');
    $name      = $dom->createElement('name');
    $pose      = $dom->createElement('pose');
    $truncated = $dom->createElement('truncated');
    $difficult = $dom->createElement('difficult');
    $bndbox    = $dom->createElement('bndbox');
    $xmin      = $dom->createElement('xmin');
    $ymin      = $dom->createElement('ymin');
    $xmax      = $dom->createElement('xmax');
    $ymax      = $dom->createElement('ymax');

    $name->appendChild($dom->createTextNode($data['name']));
    $pose->appendChild($dom->createTextNode('Unspecified'));
    $truncated->appendChild($dom->createTextNode('0'));
    $difficult->appendChild($dom->createTextNode('0'));

    $xmin->appendChild($dom->createTextNode($data['xmin']));
    $ymin->appendChild($dom->createTextNode($data['ymin']));
    $xmax->appendChild($dom->createTextNode($data['xmax']));
    $ymax->appendChild($dom->createTextNode($data['ymax']));

    $object->appendChild($name);
    $object->appendChild($pose);
    $object->appendChild($truncated);
    $object->appendChild($difficult);
    $object->appendChild($bndbox);
    $bndbox->appendChild($xmin);
    $bndbox->appendChild($ymin);
    $bndbox->appendChild($xmax);
    $bndbox->appendChild($ymax);

    $annotation->appendChild($object);

    $path = "/file/xml/";
    if (!file_exists($path)) {
        mkdir($path, 0700, true);
    }
    $file_name = $path . time() . '.xml';

    $file = fopen($file_name, "w");
    if (!fwrite($file, $dom->saveXML())) {
        return json_decode(['code' => 'error', 'var' => ['采集文件保存失败']]);
    }
}

//生成无限极分类树
function make_tree($arr)
{
    $refer = array();
    $tree  = array();
    foreach ($arr as $k => $v) {
        $refer[$v['id']] = &$arr[$k]; //创建主键的数组引用
    }
    foreach ($arr as $k => $v) {
        $pid = $v['parentId'];  //获取当前分类的父级id
        if ($pid == 0) {
            $tree[] = &$arr[$k];  //顶级栏目
        } else {
            if (isset($refer[$pid])) {
                $refer[$pid]['son'][] = &$arr[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
            }
        }
    }
    return $tree;
}

function array_to_object($arr)
{
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }

    return (object)$arr;
}

/**
 * 获取推流地址
 * 如果不传key和过期时间，将返回不含防盗链的url
 * @param bizId 您在腾讯云分配到的bizid
 *        streamId 您用来区别不同推流地址的唯一id
 *        key 安全密钥
 *        time 过期时间 sample 2016-11-12 12:00:00
 * @return String url
 */
function getPushUrl($bizId, $streamId, $key = null, $time = null)
{
    if ($key && $time) {
        $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
        //txSecret = MD5( KEY + livecode + txTime )
        //livecode = bizid+"_"+stream_id  如 8888_test123456
        $livecode = $bizId . "_" . $streamId; //直播码
        $txSecret = md5($key . $livecode . $txTime);
        $ext_str  = "?" . http_build_query(array(
                "bizid"    => $bizId,
                "txSecret" => $txSecret,
                "txTime"   => $txTime
            ));
    }
    return "rtmp://liveup.hnlinkeda.com/live/" . $livecode . (isset($ext_str) ? $ext_str : "");
}

/**
 * 获取播放地址
 * @param bizId 您在腾讯云分配到的bizid
 *        streamId 您用来区别不同推流地址的唯一id
 * @return String url
 */
function getPlayUrl($bizId, $streamId)
{
    $livecode = $bizId . "_" . $streamId; //直播码
    return array(
        "rtmp://livedown.hnlinkeda.com/live/" . $livecode,
        "http://livedown.hnlinkeda.com/live/" . $livecode . ".flv",
        "http://livedown.hnlinkeda.com/live/" . $livecode . ".m3u8"
    );
}




