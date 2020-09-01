<?php
/**
 * curl获取https请求类
 */

class Https
{
    private static $_https = null;

    public static $curlopt_timeout = 100; // 设置curl超时时间

    /**
     * 设置私有构造函数
     */
    private function __construct()
    {
        //TODO
    }

    /**
     * 对外的请求方法
     * @param $url string 请求的地址
     * @param $headers array 请求的header头部 以key => value对应
     * @param $sendData array 请求的内容 以key => value对应
     * @param $method string 请求的类型 [GET|POST|PUT|DELETE]
     */
    public static function openUrl($url, $headers, $sendData, $method)
    {
        if (!self::$_https instanceof self) {
            self::$_https = new self();
        }
        $https = self::$_https;
        $method = strtoupper($method);
        $result = '';
        switch ($method) {
            case 'GET':
                $result = $https->get($url, $headers, $sendData);
                break;
            case 'POST':
                $result = $https->post($url, $headers, $sendData);
                break;
            case 'PUT':
                $result = $https->put($url, $headers, $sendData);
                break;
            case 'DELETE':
                $result = $https->delete($url, $headers, $sendData);
                break;
        }
        return $result;
    }

    /**
     * 解析url，以?和#和&分隔成数组
     */
    private function parse_url_arr($parse_str)
    {
        $parse_url_arr = explode('?', $parse_str);
        if (count($parse_url_arr) == 1) {
            $parse_url_temp = explode('#', $parse_str);
            if (count($parse_url_temp) == 2) {
                $parse_url_arr[0] = $parse_url_temp[0];
                $parse_url_arr[1] = [];
                $parse_url_arr[2] = $parse_url_temp[1];
            }
        }
        if (count($parse_url_arr) == 2) {
            $parse_url_temp = explode('#', $parse_url_arr[1]);
            if (count($parse_url_temp) == 2) {
                $parse_url_arr[1] = $parse_url_temp[0];
                $parse_url_arr[2] = $parse_url_temp[1];
            }
            $parse_url_arr[1] = explode('&', $parse_url_arr[1]);
        }
        return $parse_url_arr;
    }

    /**
     * 合并url数组，以?和#和&合并
     */
    private function parse_url_str($parse_arr)
    {
        $parse_url_str = '';
        if (count($parse_arr) == 1) {
            $parse_url_str .= $parse_arr[0];
        }
        if (count($parse_arr) == 2) {
            $parse_arr[1] = implode($parse_arr[1], '&');
            $parse_url_str .= $parse_arr[0] . '?' . $parse_arr[1];
        }
        if (count($parse_arr) == 3) {
            $parse_arr[1] = implode($parse_arr[1], '&');
            $parse_url_str .= $parse_arr[0] . '?' . $parse_arr[1] . '#' . $parse_arr[2];
        }
        return $parse_url_str;
    }

    /**
     * url追加数据,只有GET方式支持
     */
    private function urlAdddata($url, $sendData)
    {
        if (is_array($sendData)) {
            $urlArr = $this->parse_url_arr($url);
            foreach ($sendData as $k => $v) {
                $urlArr[1][] = $k . '=' . $v;
            }
            $url = $this->parse_url_str($urlArr);
        }
        return $url;
    }

    /**
     * 把需要传输的数据数组转成能字符串
     */
    private function dataToString($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        return $data;
    }

    /**
     * 把header数据头变为可用的
     */
    private function headerToTrueHeader($headers)
    {
        $headersTrue = [];
        foreach ($headers as $k => $v) {
            if (!is_numeric($k)) {
                $headersTrue[] = $k . ': ' . $v;
            } else {
                $headersTrue[] = $v;
            }
        }
        return $headersTrue;
    }

    /**
     * 设置curl相同的部分
     */
    private function curl($url, $headers)
    {
        $curl = curl_init(); // 启动一个CURL会话  
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        $headers = $this->headerToTrueHeader($headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  // 设置header头
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器  
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$curlopt_timeout); // 设置超时限制防止死循环  
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容  
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        return $curl;
    }

    /**
     * GET请求
     */
    private function get($url, $headers, $sendData)
    {
        $url = $this->urlAdddata($url, $sendData);
        $curl = $this->curl($url, $headers);
        $tmpInfo = curl_exec($curl); // 执行操作  
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据  
    }

    /**
     * POST请求
     */
    private function post($url, $headers, $sendData)
    {
        $curl = $this->curl($url, $headers);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求  
        $sendData = $this->dataToString($sendData);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sendData); // Post提交的数据包
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据 
    }

    /**
     * PUT请求
     */
    private function put($url, $headers, $sendData)
    {
        $curl = $this->curl($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        $sendData = $this->dataToString($sendData);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sendData); // 设置请求体，提交数据包
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据  
    }

    /**
     * DELETE请求
     */
    private function delete($url, $headers, $sendData)
    {
        $curl = $this->curl($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $sendData = $this->dataToString($sendData);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sendData); // 设置请求体，提交数据包
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据  
    }
}
