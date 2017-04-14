<?php
namespace PropertyAgent\Models;

/**
* @todo
*/
abstract class Utilities
{
    /**
    * @todo
    */
    const TYPE = array(
        'int' => '\d+?',
        'slug' => '[\w-_]+?',
        'uniq' => '[a-f0-9]{32}'
    );

    /**
    * @todo
    */
    public static function createUniqId()
    {
        return md5(uniqid(rand(), true));
    }

    /**
    * @todo
    */
    public static function print($arg)
    {
        if (is_object($arg) || is_callable($arg) || is_array($arg)) {
            $arg = print_r($arg, true);
        }

        echo "<pre>$arg</pre>";
    }
}