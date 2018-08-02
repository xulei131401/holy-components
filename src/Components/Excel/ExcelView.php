<?php

namespace component\excel;

use PHPExcel;


/**
 * 基于phpoffice/phpexcel封装，不建议使用
 * Class ExcelView
 * @package component\excel
 */
class ExcelView
{
    const POINT = '.';

    protected $_data = [];
    protected $_write;
    protected $_excel;
    protected $_sheet;
    protected $_suffix = 'xlsx';
    protected $_filename;

    protected $_reader;

    private function __construct($data)
    {
        $this->_data = $data;
    }

    /**********************************init系列**************************************/
    protected function _initData()
    {
        $this->_filename = $this->_data['filename'] . self::POINT . $this->_suffix;
    }

    protected function _initExcel()
    {
        $this->_excel = new PHPExcel();
        $this->_excel->setActiveSheetIndex(0);
        $this->_sheet = $this->_excel->getActiveSheet();
        $this->_write = new \PHPExcel_Writer_Excel2007($this->_excel);    // 用于 2007 格式
        //$write = new \PHPExcel_Writer_Excel5($excel);     // 用于其他版本格式
        //$write->setOffice2003Compatibility(true);
    }

    /*********************************获取实例**************************************/
    public static function getInstance($data = [])
    {
        return new self($data);
    }

    /**
     * 使用示例：
        $result = [
            [
                'name' => '张浩',
                'job'  => 'php工程师',
                'age'  => 23,
                'date' => date('Y-m-d H:i:s')
            ]
        ];

        $list = [];
        foreach($result as $data) {
            $list[] = [
                $data['name'],
                $data['job'],
                $data['age'],
                $data['date']
            ];
        }

        $data = [
            'filename' => '简历' . date('Y-m-d H:i:s'),
            'header' => ['姓名','职位','年龄','入职日期'],
            'data' => $list
        ];
     *
     */

    /*********************************由数据动态生成表格**************************************/
    public function generator()
    {
        $this->_initData();
        $this->_initExcel();

        if (!$this->_data) {
            return;
        }

        $this->fillHeader();
        $this->fillCellData();
        $this->writeHeader();
        $this->outStream();
    }

    /*********************************设置表格头部**************************************/
    public function fillHeader()
    {
        if (!$this->_data['header']) {
            return;
        }

        foreach ($this->_data['header'] as $i => $title) {
            $this->_sheet->setCellValueByColumnAndRow($i, 1, $title);
        }

    }

    /*********************************设置表格数据**************************************/
    public function fillCellData()
    {
        if (!$this->_data['data']) {
            return;
        }

        //填充表格信息
        foreach ($this->_data['data'] as $i => $line){
            $col = 0;
            foreach ($line as $key => $value){
                $this->_sheet->setCellValueExplicitByColumnAndRow($col++, $i + 2, $value);
            }
        }
    }

    /*********************************设置响应header**************************************/
    public function writeHeader()
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename={$this->_filename}");
        header("Content-Transfer-Encoding:binary");
    }

    /*********************************输出文件**************************************/
    public function outStream()
    {
        try {
            $this->_write->save('php://output');
        } catch (\Exception $e) {

        }
    }

    /*********************************get系列**************************************/

    public function getFilename()
    {
        return $this->_filename;
    }

    /*********************************set系列**************************************/

    /**
     * @param $suffix
     */
    public function setSuffix($suffix)
    {
        $this->_suffix = $suffix;
    }
}
