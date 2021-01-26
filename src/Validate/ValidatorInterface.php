<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/5/11
 * Time: 10:25
 */

namespace Jasmine\Framework\Validate;


interface ValidatorInterface
{
    /**
     * 数据
     * @param array $data
     * @return mixed
     * itwri 2020/5/11 10:26
     */
    public function with(array $data);

    /**
     * 检验场景
     * @param mixed $sceneOrRules
     * @return mixed
     * itwri 2020/5/11 10:26
     */
    public function check($sceneOrRules = null);

    /**
     * @param null $sceneOrRules
     * @return mixed
     * itwri 2020/10/28 10:33
     */
    public function fails($sceneOrRules = null);

    /**
     * 获取所有错误信息
     * @return array
     * itwri 2020/5/11 10:28
     */
    public function getErrors();

    /**
     * @return mixed
     * itwri 2020/10/28 10:37
     */
    public function getMessages();

    /**
     * 获取某个场景下的规则
     * @param string $scene
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function getRules(string $scene);

    /**
     * 设置某个场景下的规则
     * @param string $scene
     * @param array $rules
     * @return mixed
     * itwri 2020/5/11 10:30
     */
    public function setRules(string $scene, array $rules);

    /**
     * 追加某个场景下的规则
     * @param $scene
     * @param $field
     * @param $rule
     * @return mixed
     * itwri 2020/10/27 11:39
     */
    public function newSceneRule($scene, $field, $rule);

    /**
     * 消息替换器
     * @param $rule
     * @param $newMessage
     * @return mixed
     * itwri 2020/10/27 11:15
     */
    public function message(string $rule,$newMessage);

    /**
     * @param string $field
     * @param string $rule
     * @param \Closure|callable $resolve
     * @return mixed
     * itwri 2020/10/28 10:25
     */
    public function extend(string $field,string $rule,$resolve);

    /**
     * @param array $data
     * @param array $rules
     * @return mixed
     * itwri 2020/10/29 15:14
     */
    public static function make($data = [],$rules = []);
}