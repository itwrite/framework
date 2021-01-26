<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2019/8/1
 * Time: 13:45
 */

namespace Jasmine\Framework\Validate;


class Validator implements ValidatorInterface
{
    /**
     * 引入规则
     */
    use Rules;

    const SCENE_CREATE = 'create';
    const SCENE_UPDATE = 'update';
    const SCENE_DELETE = 'delete';
    const SCENE_LIST = 'list';

    /**
     * 规则
     * @var array
     */
    protected $rules = [
        self::SCENE_CREATE => [],
        self::SCENE_UPDATE => [],
        self::SCENE_DELETE => [],
        self::SCENE_LIST   => [],
    ];

    /**
     * 存放提示信息
     * @var array
     */
    protected $messages = [];

    /**
     * 暂存需要验证的数据
     * @var array
     */
    private $_data = [];

    /**
     * 错误信息暂存区
     * @var array
     */
    private $_errorMsgArr = [];

    /**
     * 扩展规则
     * @var array
     */
    private $_extensions = [];

    /**
     * Validator constructor.
     * @param array $data
     */
    function __construct($data = [])
    {
        $this->with($data);
    }

    /**
     * @param $data
     * @return $this
     * itwri 2020/5/11 10:26
     */
    public function with($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @param string $scene
     * @param array $rules
     * @return $this
     * itwri 2020/5/11 10:30
     */
    public function setRules(string $scene, array $rules)
    {
        $this->rules[$scene] = $rules;
        return $this;
    }

    /**
     * @param string $scene
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function getRules(string $scene)
    {
        return isset($this->rules[$scene]) ? $this->rules[$scene] : null;
    }

    /**
     * @param $scene
     * @param $field
     * @param $rule
     * @return $this|mixed
     * itwri 2020/5/11 12:40
     */
    function newSceneRule($scene, $field, $rule)
    {
        if (!isset($this->rules[$scene])) {
            $this->rules[$scene] = [];
        }

        /**
         * 检查是否已有规则
         */
        if (!isset($this->rules[$scene][$field])) {
            $this->rules[$scene][$field] = '';
        }

        /**
         * 判断新规则是否合法
         */
        if (!is_string($rule) || empty($rule) || func_num_args() < 2) {
            throw new \InvalidArgumentException('The given argument was invalid.',5001);
        }

        /**
         * 分析已有规则
         */
        $arr = explode('|', $this->rules[$scene][$field]);

        /**
         * 追加到已有的规则中
         */
        $arr[] = $rule;

        $this->rules[$scene][$field] = implode('|', $arr);

        return $this;
    }

    /**
     * @param string $field
     * @param string $rule
     * @param callable|\Closure $resolve
     * @return mixed|void
     * @throws \InvalidArgumentException
     * itwri 2020/10/28 10:29
     */
    public function extend(string $field,string $rule,$resolve)
    {
        if(empty($rule)){
            throw new \InvalidArgumentException('Invalid arguments.');
        }
        $method = implode('.', [$field, explode(':', $rule)[0]]);
        $this->_extensions[$method] = $resolve;
    }

    /**
     * @param array $data
     * @param array $rules
     * @return mixed
     * itwri 2020/10/29 15:14
     */
    public static function make($data = [],$rules = []){
        return (new static())->with($data)->setRules('{default}',$rules);
    }

    /**
     * 消息规换器
     * @param $rule
     * @param $newMessage
     * @return $this
     * itwri 2020/10/27 11:15
     */
    public function message($rule,$newMessage){
        $this->messages[$rule] = $newMessage;
        return $this;
    }

    /**
     * @param $field
     * @param $rule
     * @param $args
     * @return $this
     * itwri 2019/8/1 23:08
     */
    private function resolveError($field, $rule, $args = [])
    {
        //组装key
        $key = implode('.', [$field, $rule]);

        //消息判空
        $message = isset($this->messages[$key]) ? $this->messages[$key] : ($this->getDefaultMessage($this->getRuleMethodName($rule)) ?? '');

        if (is_callable($message) || $message instanceof \Closure) {
            $message = call_user_func_array($message, $args);
        }

        $message = str_replace('{field}',$field,$message);

        //消息中替换个数
        $count = substr_count($message, '%s');

        /**
         * 根据%s个数切割参数
         */
        $args = $args > $count ? array_slice($args, 0, $count) : $args;

        array_unshift($args, $message);

        $this->_errorMsgArr[] = [
            'field'=>$field,
            'message'=>call_user_func_array('sprintf', $args)
        ];
        return $this;
    }

    /**
     * @return array
     * itwri 2019/8/1 18:39
     */
    public function getErrors()
    {
        return $this->_errorMsgArr;
    }

    /**
     * @return array|mixed
     * itwri 2020/10/28 10:38
     */
    public function getMessages(){
        return array_column($this->getErrors(),'message');
    }

    /**
     * @param null $sceneOrRules
     * @return bool|mixed
     * @throws \Exception
     * itwri 2020/10/29 15:16
     */
    public function check($sceneOrRules = null)
    {
        /**
         * 规则场景为空（即没有规则）时，可认为不需要校验，直接返回
         */
        if (!isset($sceneOrRules) || empty($sceneOrRules)) {
            return true;
        }

        /**
         * 当传入的是一个数组
         * 则可认为传入的是规则的数据,即临时规则
         */
        if (is_array($sceneOrRules)) {
            $rules = $sceneOrRules;
        } else {
            $rules = $this->getRules($sceneOrRules);
        }

        /**
         * 取出规则中所有需要校验的字段
         */
        $fields = array_keys($rules);

        return $this->checkFields($rules, $fields, $this->_data);
    }

    /**
     * @param null $sceneOrRules
     * @return bool|mixed
     * @throws \Exception
     * itwri 2020/10/29 15:16
     */
    public function fails($sceneOrRules = null){
        return !$this->check($sceneOrRules);
    }

    /**
     * 获取数组值
     * @param $target
     * @param $key
     * @param null $default
     * @return mixed
     * itwri 2019/8/23 0:51
     */
    protected function arrayGet($target, $key, $default = null)
    {
        /**
         * 参数小于2或者key为null
         * 返回原数据
         */
        if (func_num_args() < 2 || is_null($key)) return $target;

        /**
         * 字符键、数据键
         */
        if (is_string($key) || is_numeric($key)) {

            /**
             * 如果存在，则直接返回
             */
            if (is_array($target) && isset($target[$key])) return $target[$key];

            /**
             * 按字符‘.’切割分析
             */
            foreach (explode('.', strval($key)) as $segment) {
                if (is_array($target)) {
                    if (!array_key_exists($segment, $target)) {
                        return self::value($default);
                    }
                    $target = $target[$segment];
                } elseif (is_object($target)) {
                    if (!isset($target->{$segment})) {
                        return self::value($default);
                    }

                    $target = $target->{$segment};
                } else {
                    return self::value($default);
                }
            }
        }
        return self::value($target);
    }


    /**
     * @param $allRules
     * @param array $needCheckFields
     * @param array|null $data
     * @return bool
     * @throws \Exception
     * itwri 2020/10/29 15:05
     */
    protected function checkFields($allRules, array $needCheckFields, Array $data = null)
    {
        /**
         * 没有需要检查的字段
         */
        if (empty($needCheckFields)) {
            return true;
        }

        foreach ($needCheckFields as $field) {
            //如果字段为空，跳过
            if (empty($field)) continue;

            $field = trim($field);

            /**
             * 分析字段，如果有规则存在，则根据规则进行校验
             */
            $rules = trim(isset($allRules[$field]) ? $allRules[$field] : '');

            if (!empty($rules)) {
                /**
                 * 可以多规则，以英文字符 '|' 间隔
                 */
                $rules = is_array($rules) ? $rules : explode('|', $rules);

                /**
                 * 对每一个规则进行检查
                 */
                foreach ($rules as $rule) {
                    /**
                     * 英文‘:’之后的为参数，参数以英文‘,’间隔
                     */
                    $arr = explode(':', $rule);
                    /**
                     * 第一个为规则方法名
                     */
                    $rule = array_shift($arr);

                    /**
                     * 处理传参数据
                     */
                    $args = explode(',', implode(':', $arr));

                    /**
                     * 数据值
                     */
                    $value = $this->arrayGet($data, $field);

                    //处理需要的传参
                    $params = array_merge([$value], $args);
                    //
                    $method = implode('.', [$field, $rule]);
                    /**
                     * 自定义的扩展优先
                     */
                    if (isset($this->_extensions[$method]) && is_callable($this->_extensions[$method])) {
                        /**
                         * 校验如果不正确则返回false，退出校验
                         */
                        $res = call_user_func_array($this->_extensions[$method], $params);
                        if ($res != true) {
                            //生成错和提示
                            $this->resolveError($field, $rule, $args);
                            return false;
                        }
                    } else {
                        /**
                         * 内部方法，方法名加前缀,转驼峰
                         */
                        $method = $this->getRuleMethodName($rule);

                        if(empty($rule) || !method_exists($this, $method)){
                            //生成错和提示
                            throw new \Exception('There is no rule for this check.');
                        }
                        /**
                         * 校验如果不正确则返回false，退出校验
                         */
                        $res = call_user_func_array([$this, $method], $params);
                        if ($res != true) {
                            //生成错和提示
                            $this->resolveError($field, $rule, $args);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $rule
     * @return string
     * itwri 2020/10/27 14:20
     */
    protected function getRuleMethodName($rule){
        return  'rule' . $this->studly($rule);
    }

    /**
     * @param $value
     * @return mixed
     * itwri 2019/8/9 22:36
     */
    protected function studly($value)
    {
        $this->value($value);

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * @param $value
     * @return mixed
     * itwri 2019/8/9 22:36
     */
    protected function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}