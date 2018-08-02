<?php
/**
 * Created by PhpStorm.
 * User: xulei
 * Date: 2018/8/2
 * Time: 16:43
 */

namespace Holy\Components\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * 基于phpoffice/phpspreadsheet封装，推荐使用
 * Class HolyExcel
 * @package Holy\Components\Excel
 */
class ExcelToSql
{
    const CSV = 'csv';
    const HTML = 'html';
    const XML = 'xml';
    const XLS = 'xls';
    const XLSX = 'xlsx';

    protected $_reader;
    protected $_spreadsheet;
    protected $_sheet;
    protected $_data;

    private function __construct($data = [])
    {
        $this->_data = $data;

        $this->_initExcel();
    }

    protected function _initExcel()
    {
        try {
            if (!isset($this->_data['suffix'])) {
                die('后缀不能为空');
            }

            $this->_reader = IOFactory::createReader(ucfirst(strtolower($this->_data['suffix'])));
            $this->_reader->setReadDataOnly(true);
            $this->_spreadsheet = $this->_reader->load($this->_data['path']);

            if (!isset($this->_data['path'])) {
                die('文件路径不能为空');
            }

        } catch (Exception $e) {
            die('文件路径不能为空');
        }

    }

    public static function getInstance($data = [])
    {
        return new self($data);
    }

    public function buildInsertSqlTemplate()
    {
        if (!isset($this->_data['table'])) {
             return '';
        }

        $template = "INSERT INTO `{$this->_data['table']}` (";
        foreach ($this->_data['fields'] as $field) {
            $template .= "`{$field}`,";
        }

        $template = trim($template, ',');
        $template .= ') VALUES ';

        return $template;
    }

    public function toInsertSql()
    {
        $this->_sheet = $this->_spreadsheet->getActiveSheet();
        $highestRow = $this->_sheet->getHighestRow();
        if ($highestRow - 2 <= 0) {
            return '';
        }

        $sql = $this->buildInsertSqlTemplate();

        if (!isset($this->_data['jump'])) {
            die('跳跃行数不能为空');
        }

        $count = count($this->_data['fields']);
        for ($row = $this->_data['jump']; $row <= $highestRow; $row++) {
            $sql .= "(";

            for ($j = 1; $j <= $count; $j++) {
                $data = $this->_sheet->getCellByColumnAndRow(1, $row)->getValue();
                $sql .= "'{$data}',";
            }

            $sql = trim($sql, ',');
            $sql .= "),";
        }

        $sql = trim($sql, ',');
        $sql .= ';';

        return $sql;

    }
}