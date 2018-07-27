<?php

namespace component\excel;

use component\http\Json;
use component\log\SpaLog;
use PHPExcel;

class ExcelView
{
    /**
     * 使用示例：
        $list = [];
        foreach($result as $data) {
            $list[] = [
                $data['join_agent_user']['nickname'],
                date('Y-m-d H:i', strtotime($data['publish_at'])),
                $data['sn'],
                $data['plaintext'],
                $data['scope_type_text']
            ];
        }

        return [
            'file' => '《普通体验卡》卡号密码清单' . date('Y-m-d H:i:s'),
            'header' => ['申领人','申领时间','卡号','卡密','体验卡类型'],
            'data' => $list
        ];
     *
     * @param $data
     */
    public static function create($data = [])
    {
        if (!$data) {
            return;
        }

        $header = $data['header'];
        $originData = $data['data'];
        if (!is_array($header)){
            SpaLog::getLogger()->error("文件数据错误");
            return;
        }

        //创建对象
        $excel = new PHPExcel();
        try {
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
        } catch (\PHPExcel_Exception $e) {

        }

        //填充表头信息
        foreach ($header as $i => $title) {
            $sheet->setCellValueByColumnAndRow($i, 1, $title);
        }

        //填充表格信息
        foreach ($originData as $i => $line){
            $col = 0;
            foreach ($line as $key => $value){
                $sheet->setCellValueExplicitByColumnAndRow($col++, $i + 2, $value);
            }
        }

        //创建Excel输入对象
        //$write = new \PHPExcel_Writer_Excel5($excel);     // 用于其他版本格式
        $write = new \PHPExcel_Writer_Excel2007($excel);    // 用于 2007 格式
        //$write->setOffice2003Compatibility(true);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $fileName = $data['file'] .'.xlsx';
        header("Content-Disposition:attachment;filename=$fileName");
        header("Content-Transfer-Encoding:binary");

        try {
            $write->save('php://output');
        } catch (\PHPExcel_Writer_Exception $e) {
            SpaLog::getLogger("excel_write_error")->error(Json::array2json($data));
        }
    }
}
