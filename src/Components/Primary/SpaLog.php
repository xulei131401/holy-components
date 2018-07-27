<?php
/**
 * 日志类
 */

namespace component\log;

use component\Enumerate;
use think\Config;
use think\Request;

class SpaLog
{
    const L_FATAL = 0;                      // 致命的、必须修正的错误
    const L_ERROR = 1;                      // 特殊情况造成的代码执行失败
    const L_WARNING = 2;                    // 警告
    const L_INFO = 3;                       // 提示信息
    const L_DETAIL = 4;                     // 数据执行信息，如SQL语句耗时
    const L_DEBUG = 5;                      // 调试信息
    
    private $_logName = 'run.log';
    private $_state = array("FATAL", "ERROR", "WARNING", "INFO", "DETAIL", "DEBUG");
    private $_file = null;          // 日志文件,空则打印输出
    private $_appender = 'day';     // 文件增长方式：null-单个文件追加；'day|month|year'-按天|月|年增长
    private $_pattern = '[%d %t][%p][%s][%i]%m%n';     // 输出格式
    private $_level = 3;            // 日志级别
    private $_output = false;       // 是否直接输出
    private $_disabled = false;     // 是否记录

    private static $_instances = [];

    public static function getLogger($logName = Enumerate::DEFAULT_LOG_DEFAULT)
    {
        if (!isset(self::$_instances[$logName])) {
            self::$_instances[$logName] = new self($logName);
        }

        return self::$_instances[$logName];
    }
    
    private function __construct($logName)
    {
        $this->_logName = $logName;
        $cfgLog = Config::get('common.' . Enumerate::DEFAULT_LOG);
        if (isset($cfgLog[$logName])) {
            $cfg = $cfgLog[$logName];
        }else {
            $cfg = current($cfgLog);
            $cfg['file'] = $logName.'.'.$cfg['file'];
        }
        
        $this->_file = isset($cfg['file']) ? $cfg['file'] : $this->_file;
        $this->_appender = isset($cfg['appender']) ? $cfg['appender'] : $this->_appender;
        $this->_pattern = isset($cfg['pattern']) ? $cfg['pattern'] : $this->_pattern;
        $this->_level = isset($cfg['level']) ? $cfg['level'] : $this->_level;
        $this->_disabled = isset($cfg['disabled']) ? $cfg['disabled'] : $this->_disabled;
        $this->_output = isset($cfg['output']) ? $cfg['output'] : $this->_output;
    }

    public function fatal($msg)
    {
        return $this->_log($msg, self::L_FATAL);
    }

    public function error($msg)
    {
        return $this->_log($msg, self::L_ERROR);
    }

    public function warning($msg)
    {
        return $this->_log($msg, self::L_WARNING);
    }

    /**
     * @param $msg
     * @return bool|int
     */
    public function info($msg)
    {
        return $this->_log($msg, self::L_INFO);
    }

    public function detail($msg)
    {
        return $this->_log($msg, self::L_DETAIL);
    }

    public function debug($msg)
    {
        return $this->_log($msg, self::L_DEBUG);
    }

    /**
     * 返回“写日志”及“上级”函数的调用栈信息。
     */
    private function _getCallStack()
    {
        $traces = debug_backtrace();
        foreach ($traces as $i => $trace) {
            $file = $trace['file'];
            $method = strtolower($trace['function']);
            if ($file != __FILE__ && in_array($method, array('fatal', 'error', 'warning', 'info', 'detail', 'debug'))) {
                $stack = array();
                // 当前栈信息
                $stack['this'] = $trace;
                $stack['this']['file'] = substr($stack['this']['file'], strlen(ROOT_PATH));
                if ($i + 1 < count($traces)) {
                    $file = substr($traces[$i + 1]['file'], strlen(ROOT_PATH));
                    $stack['parent'] = $file . ':' . $traces[$i + 1]['line'];
                }

                return $stack;
            }
        }

        return null;
    }

    /**
     *  # pattern参数的格式含义
        # 格式名 含义
        # %d 输出日志时间点的日期或时间，默认格式为Y-m-d H:i:s
        # %f 输出日志信息所属的类的文件名
        # %l 输出日志事件的发生位置，即输出日志信息的语句处于它所在的类的第几行
        # %m 输出代码中指定的信息，如log(message)中的message
        # %n 输出一个回车换行符，“\n”
        # %p 输出优先级，即DEBUG，INFO，WARN，ERROR，FATAL。如果是调用debug()输出的，则为DEBUG，依此类推
        # %i 输出用户的ip
        # %u 输出用户的id
        # %s 输出用户的session id
        # %t 输出当前的毫秒值
     * @param $pattern
     * @param $msg
     * @param $level
     * @return mixed|string
     */
    private function _parsePattern($pattern, $msg, $level)
    {
        if (!$pattern) {
            return $msg . "\n";
        }

        $output = $pattern;
        if (false !== strpos($pattern, '%d')) {
            $date = date("Y-m-d H:i:s");
            $output = str_replace('%d', $date, $output);
        }

        if (false !== strpos($pattern, '%f') || false !== strpos($pattern, '%l')) {
            $traces = $this->_getCallStack();
            if (null !== $traces) {
                $file = $traces['this']['file'];
                $line = $traces['this']['line'];

                $output = str_replace('%f', $file, $output);
                $output = str_replace('%l', $line, $output);
            }
        }

        if (false !== strpos($pattern, '%m')) {
            $output = str_replace('%m', $msg, $output);
        }

        if (false !== strpos($pattern, '%n')) {
            $output = str_replace('%n', "\n", $output);
        }

        if (false !== strpos($pattern, '%p')) {
            $output = str_replace('%p', $this->_state[$level], $output);
        }

        if (false !== strpos($pattern, '%i')) {
            $request = Request::instance();
            $ip = $request->ip();
            $output = str_replace('%i', $ip, $output);
        }

        if (false !== strpos($pattern, '%s')) {
            $output = str_replace('%s', session_id(), $output);
        }

        if (false !== strpos($pattern, '%t')) {
            list($usec, $sec) = explode(" ", microtime());
            $output = str_replace('%t', round($usec * 1000), $output);
        }

        return $output;
    }

    private function _log($msg, $level)
    {
        if ($this->_disabled || $this->_level < $level) {
            return FALSE;
        }

        if (is_array($msg)) {
            $msg = json_encode($msg);
        }

        $output = $this->_parsePattern($this->_pattern, $msg, $level);
   
        if ($this->_file) {
            $dir = Config::get('common.' . Enumerate::DEFAULT_LOG)[Enumerate::DEFAULT_LOG_DEFAULT][Enumerate::DEFAULT_LOG_SAVE_PATH];
            if (!is_dir($dir)) {
                mkdir($dir);
                if (!is_writeable($dir)) chmod($dir, 0755);
            }

            $filePath = $dir . $this->_file;

            if ('day' == $this->_appender) {
                $filePath .= date('Y-m-d');
            } else if ('month' == $this->_appender) {
                $filePath .= date('Y-m');
            } else if ('year' == $this->_appender) {
                $filePath .= date('Y');
            }

            return file_put_contents($filePath, $output, FILE_APPEND);
        }

        return FALSE;
    }

}