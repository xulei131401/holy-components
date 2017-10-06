<?php

namespace Holy\Components\Support\Doc;


use Holy\Components\Database\Connectors\MySqlConnector;

class DatabaseDictionary
{
    const SQL_FILE_NAME = 'dict.sql';
    const SQL_JSON_FILE_NAME = 'dict.json';
    protected static $config =
        [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'magic_seven',
            'username' => 'root',
            'password' =>  '123456',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null
        ];

    public static function buildData()
    {
        $pdo = (new MySqlConnector())->connect(self::$config);
        $stmt = $pdo->prepare('SHOW TABLE STATUS');
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $sql = array();
        foreach ($data as $key => &$table){
            $tableName = $table['Name'];

            $stmt = $pdo->prepare("SHOW FULL FIELDS FROM ${tableName}");
            $stmt->execute();
            $table['Columns'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SHOW CREATE TABLE ${tableName}");
            $stmt->execute();
            $createSql = $stmt->fetchAll();

            $create_sql = preg_replace('/AUTO_INCREMENT=\d+ /i', '', $createSql[0]['Create Table']).";";
            $table['Create_sql'] = $create_sql;
            $sql[] = $create_sql;
        }
        $result = json_encode($data);
        self::createFile($result, $sql);
        return $result;
    }

    protected static function createFile($data, $sql)
    {
        $jsonFile = dirname(__FILE__).'/'.self::SQL_JSON_FILE_NAME;
        $sqlFile = dirname(__FILE__).'/'.self::SQL_FILE_NAME;
        if (!empty($data) && !empty($sql)){
            file_put_contents($jsonFile, $data);
            file_put_contents($sqlFile, implode("\n\n", $sql));
        }
    }
}