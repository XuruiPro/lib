<?php
/**
 * 日志文件
 */
class Logger
{
    public static $_logLevel = 1; // [0-stop 关闭日志|1-debug 调试|2-notice 警告|3-error 错误] 0为关闭 其他级别顺序为3>2>1
    public static $_logTimeFormat = '[Y-m-d H:i:s]'; //写入日志时间格式
    public static $_logPath = 'log'; // 日志写入的文件位置
    public static $_logPathNameFormat = 'Ymd'; //日志存储文件名称格式[为时间格式]
    
    private static $_logger = null;
    
    /**
     * 写入日志文件
     * @param $msg string 日志信息
     * @param $position string 日志位置,便于查找日志产生的位置，一般用class名称命名
     * @param $level int 日志级别 [1-debug|2-notice|3-error]
     */
    public static function log($msg, $position, $level = 1)
    {
        if (!self::$_logger instanceof Logger) {
            self::$_logger = new Logger();
        }
        $logger = self::$_logger;
        if (self::$_logLevel !=0 && self::$_logLevel <= $level && in_array($level, [1, 2, 3])) {
            $msg = $logger->getMsg($msg, $position, $level);
            $logger->setLogFile($msg);
        }
    }
    
    /**
     * 组装日志信息
     */
    private function getMsg($msg, $position, $level)
    {
        $msg = strval($msg);
        $position = strval($position);
        $level = strval($level);
        $msgStr = date(self::$_logTimeFormat, time());
        $msgStr .= "\n";
        $msgStr .= 'position:【 ' .$position. ' 】';
        switch ($level) {
            case 1:
                $level = 'DEBUG';
                break;
            case 2:
                $level = 'NOTICE';
                break;
            case 3:
                $level = 'ERROR';
                break;
        } 
        $msgStr .= '　level:【 ' . $level . ' 】';
        $msgStr .= "\n";
        $msgStr .= $msg;
        $msgStr .= "\n\n";
        return $msgStr;
    }
    
    /**
     * 写入日志文件内容
     */
    private function setLogFile($msg)
    {
        $logPath = rtrim(self::$_logPath, '/');
        if (!is_dir($logPath)) {
            @mkdir($logPath);
        }
        $logPath .= '/' . date(self::$_logPathNameFormat, time()) . '.log';
        file_put_contents($logPath, $msg, FILE_APPEND);
    }
}