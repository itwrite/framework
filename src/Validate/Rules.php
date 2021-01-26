<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/10/27
 * Time: 10:37
 */

namespace Jasmine\Framework\Validate;


trait Rules
{
    /**
     * 存放默认信息
     * @var array
     */
    private $defaultMessages = [];

    /**
     * @param $method
     * @return string
     * itwri 2020/10/27 14:22
     */
    protected function getDefaultMessage($method){
        return $this->defaultMessages[$method] ?? '';
    }

    /**
     * @param $method
     * @param $message
     * itwri 2020/10/27 14:22
     */
    protected function setDefaultMessage($method,$message){
        $this->defaultMessages[$method] = $message;
    }

    /**
     * =================================================================================
     * 规则 以 rule 为前缀的方法
     * =================================================================================
     */
    /**
     * 必要的
     * @param $value
     * @return bool
     * itwri 2019/8/1 18:37
     */
    function ruleRequire($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'The {field} is required.');
        $value = is_string($value) ? trim($value) : $value;
        if ($value === 0 || $value == '0') {
            return true;
        }
        if (!is_null($value)) {
            return !empty($value);
        }
        return false;
    }

    /**
     * 在一定的长度之间
     * @param string $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/1 22:49
     */
    function ruleLength($value, $min, $max = null)
    {
        $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if (func_num_args() < 3) {
            $this->setDefaultMessage(__FUNCTION__,'The minimum length of {field} should be %s.');
            return $len >= $min;
        }

        $bok = false;
        if($min != ''){
            $bok = $bok && ($len >= $min);
        }
        if($max != ''){
            $bok = $bok && $len < $max;
        }
        $this->setDefaultMessage(__FUNCTION__,'The length of {field} should between %s and %s.');
        return $bok;
    }

    /**
     * 在一个特定和枚举列表中
     * @param $value
     * @return bool
     * itwri 2019/8/1 23:34
     */
    function ruleIn($value)
    {
        $values = array_slice(func_get_args(), 1);
        $this->setDefaultMessage(__FUNCTION__,'The value should in ('.implode(',',$values).').');
        return in_array($value, $values);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:13
     */
    function ruleNotIn($value)
    {
        $args = func_get_args();

        $values = array_slice($args, 1);
        $this->setDefaultMessage(__FUNCTION__,'The value should not in ('.implode(',',$values).').');
        return !call_user_func_array([$this, 'ruleIn'], $args);
    }

    /**
     * 是个数字
     * @param $value
     * @return bool
     * itwri 2019/8/3 16:33
     */
    function ruleNumber($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'The value should be a number.');
        return is_numeric($value);
    }

    /**
     * 是个整数
     * @param $value
     * @return bool
     * itwri 2019/8/3 21:32
     */
    function ruleInt($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'The value should be a integer.');
        return $this->ruleNumber($value) && strpos($value, '.') == false;
    }

    /**
     * 是个正整数
     * @param $value
     * @return bool
     * itwri 2019/8/4 22:09
     */
    function ruleInteger($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'The value should be a positive integer.');
        return $this->ruleInt($value) && $value >= 0;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:52
     */
    function ruleFloat($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'The value should be a float.');
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * 在一定范围内
     * @param $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/4 22:12
     */
    function ruleBetween($value, $min, $max)
    {
        $this->setDefaultMessage(__FUNCTION__,'The length of {field} should between %s and %s.');
        if ($this->ruleNumber($value) && func_num_args() > 2) {
            return $value >= $min && $value <= $max;
        }
        return false;
    }

    /**
     * @param $value
     * @param $min
     * @param $max
     * @return bool
     * itwri 2019/8/27 0:51
     */
    function ruleRange($value, $min, $max)
    {
        $this->setDefaultMessage(__FUNCTION__,'The length of {field} should between %s and %s.');
        return $this->ruleBetween($value, $min, $max);
    }

    /**
     * @param $value1
     * @param $value2
     * @return bool
     * itwri 2020/10/27 12:15
     */
    function ruleEq($value1, $value2)
    {
        $this->setDefaultMessage(__FUNCTION__,'{field} not equal to `%s`.');
        return $value1 == $value2;
    }

    /**
     * @param $value1
     * @param $value2
     * @return mixed
     * itwri 2020/10/27 12:15
     */
    function ruleEqualTo($value1, $value2){
        $this->setDefaultMessage(__FUNCTION__,'{field} not equal to `%s`.');
        return call_user_func_array([$this, 'ruleEq'], func_get_args());
    }

    /**
     * @param $value1
     * @param $value2
     * @return bool
     * itwri 2020/10/27 12:15
     */
    function ruleDiff($value1, $value2)
    {
        $this->setDefaultMessage(__FUNCTION__,'{field} the same to `%s`.');
        return !call_user_func_array([$this, 'ruleEq'], func_get_args());
    }

    /**
     * @param $value1
     * @param $value2
     * @return bool
     * itwri 2020/10/27 12:15
     */
    function ruleDifferent($value1, $value2)
    {
        $this->setDefaultMessage(__FUNCTION__,'{field} the same to `%s`.');
        return !call_user_func_array([$this, 'ruleDiff'], func_get_args());
    }

    /**
     *
     * @param $value
     * @param $min
     * @return bool
     * itwri 2019/8/16 13:12
     */
    function ruleMin($value, $min)
    {
        $this->setDefaultMessage(__FUNCTION__,'The minimum value of {field} should be %s.');
        return $value >= $min;
    }

    /**
     * @param $value
     * @param $max
     * @return bool
     * itwri 2019/8/16 14:09
     */
    function ruleMax($value, $max)
    {
        $this->setDefaultMessage(__FUNCTION__,'The maximum value of {field} should be %s.');
        return $value <= $max;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/16 14:11
     */
    function ruleEmail($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'{field} is not an email address.');
        if (!$value) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function ruleBool($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'Expect a bool value.');
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }


    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:27
     */
    function ruleBoolean($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'Expect a bool value.');
        return $this->ruleBool($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:37
     */
    function ruleDate($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'Expect a date value.');
        return strtotime($value) !== false;
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/23 0:42
     */
    function ruleArray($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'Expect an array.');
        return is_array($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2019/8/27 0:50
     */
    function ruleUrl($value)
    {
        $this->setDefaultMessage(__FUNCTION__,'It not a valid Url.');
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $value
     * @param int $type
     * @return bool
     * itwri 2019/9/6 1:11
     */
    function ruleIp($value, $type = 4)
    {
        $this->setDefaultMessage(__FUNCTION__,'It not a valid IP address.');
        $flag = $type == 6 ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4;
        return filter_var($value, FILTER_VALIDATE_IP, $flag) !== false;
    }

    /**
     * 正则表达式1
     * @param $value
     * @param $regex
     * @return false|int
     * itwri 2020/10/27 10:44
     */
    public function ruleRegex($value, $regex){
        $this->setDefaultMessage(__FUNCTION__,'It\'s a wrong value giving.');
        $args = func_get_args();
        $value = array_shift($args);
        $pattern = implode('', $args);
        return preg_match($pattern, $value);
    }

    /**
     *
     * 正则表达式2
     * @param $value
     * @param $regex
     * @return false|int
     * itwri 2019/9/15 21:19
     */
    function ruleRegExp($value, $regex)
    {
        $this->setDefaultMessage(__FUNCTION__,'It\'s a wrong value giving.');
        return call_user_func_array([$this, 'ruleRegex'], func_get_args());
    }

    /**
     *
     * 正则表达式3
     * @param $value
     * @param $regex
     * @return false|int
     * itwri 2019/9/15 21:19
     */
    public function ruleRegular($value, $regex)
    {
        $this->setDefaultMessage(__FUNCTION__,'It\'s a wrong value giving.');
        return call_user_func_array([$this, 'ruleRegex'], func_get_args());
    }

    /**
     * @param $value
     * @return bool
     * itwri 2020/10/29 15:06
     */
    public function ruleIsObject($value){
        $this->setDefaultMessage(__FUNCTION__,'It\'s not an object.');
        return is_object($value);
    }

    /**
     * @param $value
     * @return bool
     * itwri 2020/10/29 15:06
     */
    public function ruleIsArray($value){
        $this->setDefaultMessage(__FUNCTION__,'It\'s not an array.');
        return is_array($value);
    }
}