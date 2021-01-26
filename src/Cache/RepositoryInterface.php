<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/7/31
 * Time: 20:46
 */

namespace Jasmine\Framework\Cache;


interface RepositoryInterface
{

    public function get($key);

    public function set($key,$value,$expire = 0);
    
    public function forget($key);
}