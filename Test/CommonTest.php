<?php
/**
 * Created by PhpStorm.
 * User: xulei
 * Date: 2018/8/2
 * Time: 16:48
 */

use Holy\Components\Excel\ExcelToSql;
use Holy\Components\Excel\HolyExcel;

require __DIR__ . "/../vendor/autoload.php";

ini_set("display_errors", "On");
error_reporting(E_ALL);
ini_set("error_reporting", E_ALL);


class CommonTest
{
    public function run()
    {
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
            'file' => '简历' . date('Y-m-d H:i:s'),
            'header' => ['姓名','职位','年龄','入职日期'],
            'data' => $list
        ];

        HolyExcel::getInstance($data)->generator();
    }

    public function runTwo()
    {
        $data = [
            'path'      => 'students.xlsx',
            'jump'      => 3,
            'table'     => 'student',
            'fields'    => ['name', 'chinese', 'maths', 'english'],
            'suffix'    => ExcelTosql::XLSX
        ];
        $sql = ExcelTosql::getInstance($data)->toInsertSql();
        file_put_contents('students.sql', $sql);
        var_dump($sql);
    }
}

$task = new CommonTest();
//$task->run();
//$task->runTwo();