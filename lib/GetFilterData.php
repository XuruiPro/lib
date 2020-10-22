<?php
/**
 * User: luckyxu
 * Date: 2020/10/22
 * Time: 15:37
 * File: GetFilterData.php
 */


class GetFilterData
{
    /**
     * 过滤数据
     * @param array $data 验证的数组
     * @param array $rules
     * 注释：rules列表规则说明 {"key": "rule"}方式的对象
     *      key 表示验证的传值字段
     *          key {"key":"验证值"}
     *          key.id {"key":{"id":"验证值"}}
     *          key.* {"key": ["验证值"]}
     *          key.*.id {"key": [{"id": "验证值"}]}
     *          可按照规则无限延伸
     *      rule 表示验证值的规则
     *          int 整数类型
     *          string 字符串类型
     *          date 日期类型  默认方式为date:Y-m-d 后面的Y-m-d可自行设置
     *          array 数组类型
     *          page 传入的当前页数
     *          limit 传入的每页条数
     * @return mixed
     */
    public function filterData($data, $rules)
    {
        $ruleList = $this->formatRules($rules);
        $result = $this->handleData($data, $ruleList);
        return $result;
    }

    /**
     * 处理数据
     * @param $data
     * @param $ruleList
     * @return mixed
     */
    private function handleData($data, $ruleList)
    {
        if (!is_array($data)) $data = [];
        $result = [];
        foreach ($ruleList as $key => $val) {
            if ($key == '*') {
                foreach ($data as $v) {
                    if ($val['data']) {
                        $result[] = $this->handleData($v, $val['data']);
                    } else {
                        $result[] = $this->executeRule($val['rule'], $v);
                    }
                }
            } else {
                $data[$key] = $data[$key] ?? '';
                if ($val['data']) {
                    $result[$key] = $this->handleData($data[$key], $val['data']);
                } else {
                    $result[$key] = $this->executeRule($val['rule'], $data[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * 格式化多个规格
     * @param $rules
     * @return array|mixed
     */
    private function formatRules($rules)
    {
        $result = [];
        foreach ($rules as $rule => $val) {
            $rs = $this->formatRule($rule, $val);
            $result = $this->arrayMerge($result, $rs);
        }
        return $result;
    }

    /**
     * 格式化单个规则
     * @param $rule
     * @param $data
     * @param int $level
     * @param string $arrKey
     * @return array
     */
    private function formatRule($rule, $data, $level = 0, $arrKey = '')
    {
        $result = [];
        $tmpList = explode('.', $rule);
        $ruleList = array_slice($tmpList, $level);
        if ($ruleList) {
            $key = $ruleList[0];
            $result[$key] = [
                'rule' => $data,
                'data' => []
            ];
            $level++;
            $result[$key]['data'] = $this->formatRule($rule, $data, $level, $arrKey);
        }
        return $result;
    }

    /**
     * 合并多维数组
     * @param $arrayOne
     * @param $arrayTwo
     * @return mixed
     */
    private function arrayMerge($arrayOne, $arrayTwo)
    {
        foreach ($arrayTwo as $key => $val) {
            if (is_array($val)) {
                $arrayOne[$key] = $arrayOne[$key] ?? [];
                $arrayOne[$key] = $this->arrayMerge($arrayOne[$key], $val);
            } else {
                if (!isset($arrayOne[$key])) {
                    $arrayOne[$key] = $val;
                }
            }
        }
        return $arrayOne;
    }

    /**
     * 规则执行
     * @param $rule
     * @param $data
     * @return null
     */
    private function executeRule($rule, $data)
    {
        $data = is_string($data) ? trim($data) : $data;
        $ruleArr = explode(':', $rule);
        $ruleLabel = $ruleArr[0] ?? '';
        $ruleInfoList = array_slice($ruleArr, 1);
        $ruleInfo = implode(':', $ruleInfoList);
        switch ($ruleLabel) {
            case 'int':  // 转成int型
                $data = intval($data);
                break;
            case 'string':  // 转成string型
                $data = strval($data);
                break;
            case 'date':  // 转成时间格式
                if (!$ruleInfo) $ruleInfo = 'Y-m-d';
                $data = is_string($data) && $data ? date($ruleInfo, strtotime($data)) : '';
                break;
            case 'array': // 转成数组格式
                $data = is_array($data) ? $data : [];
                break;
            case 'page': // 当前页数
                $data = intval($data);
                $data = $data < 1 ? 1 : $data;
                break;
            case 'limit': // 每页多少条
                $data = intval($data);
                $data = $data < 1 ? 10 : $data;
                $data = $data > 100 ? 100 : $data;
                break;
        }
        return $data;
    }
}