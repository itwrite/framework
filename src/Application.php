<?php
/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/7/6
 * Time: 22:08
 */

namespace Jasmine\Framework;


use Jasmine\Container\Container;

class Application extends Container
{

    protected $baseDir = '';

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }
}