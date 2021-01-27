<?php

/**
 * Created by PhpStorm.
 * User: itwri
 * Date: 2020/2/29
 * Time: 14:09
 */

namespace Jasmine\Helper;


/**
 * Implements a lightweight PSR-0 compliant autoloader.
 *
 * @author itwrite <itwrite@163.com>
 */
class Autoloader
{
    protected static $mappings = [];
    protected static $registered = false;

    /**
     * @param array $prefixArr
     * itwri 2020/7/30 14:18
     */
    public static function mergre(array $prefixArr)
    {
        $count = 0;
        foreach ($prefixArr as $prefix => $baseDirectory) {
            self::$mappings[$prefix] = $baseDirectory;
            $count++;
        }
        return $count > 0;
    }

    /**
     * Registers the autoloader class with the PHP SPL autoloader.
     * @param array $prefixArr
     * @param bool $prepend Prepend the autoloader on the stack instead of appending it.
     */
    public static function register($prefix, $baseDir = null)
    {
        if(self::$registered == false){
            self::$registered = spl_autoload_register(implode('::', [__CLASS__, 'autoload']), true, true);
        }
        if(is_array($prefix)){
            return self::mergre($prefix);
        }
        return self::mergre([$prefix=>$baseDir]);
    }

    /**
     * Loads a class from a file using its fully qualified name.
     *
     * @param string $className Fully qualified name of a class.
     */
    public static function autoload($className)
    {
        $className = $className[0] == '\\' ? substr($className, 1) : $className;

        foreach (self::$prefixArr as $prefix => $baseDirectory) {

            if (0 === strpos($className, $prefix)) {
                $parts = explode('\\', substr($className, strlen($prefix)));
                $filePath = $baseDirectory . '/' . implode('/', $parts) . '.php';

                if (is_file($filePath)) {
                    /** @noinspection PhpIncludeInspection */
                    require $filePath;
                    break;
                }
            }
        }
    }
}