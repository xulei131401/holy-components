<?php
/**
 * Created by PhpStorm.
 * User: xulei
 * Date: 2018/8/2
 * Time: 16:43
 */

namespace Holy\Components\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 基于phpoffice/phpspreadsheet封装，推荐使用
 * Class HolyExcel
 * @package Holy\Components\Excel
 */
class HolyExcel
{
    const POINT = '.';

    protected $_data = [];
    protected $_write;
    protected $_spreadsheet;
    protected $_sheet;
    protected $_suffix = 'xlsx';
    protected $_filename;

    protected $_reader;

    private function __construct($data)
    {
        $this->_data = $data;

        $this->_initData();
        $this->_initExcel();
    }

    /**********************************init系列**************************************/
    protected function _initData()
    {
        $this->_filename = $this->_data['filename'] . self::POINT . $this->_suffix;
    }

    protected function _initExcel()
    {
        $this->_spreadsheet = new Spreadsheet();
        $this->_sheet = $this->_spreadsheet->getActiveSheet();
        $this->_write = new Xlsx($this->_spreadsheet);
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
            $this->_sheet->setCellValueByColumnAndRow($i + 1, 1, $title);
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
                $this->_sheet->setCellValueByColumnAndRow($col++, $i+2, $value);
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