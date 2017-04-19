<?php
namespace PropertyAgent\Models;

abstract class Utilities
{
    const TYPE = array(
        'int' => '\d+?',
        'slug' => '[\w-_]+?',
        'uniq' => '[a-f0-9]{32}'
    );

    public static function createUniqId()
    {
        return md5(uniqid(rand(), true));
    }

    public static function print($arg)
    {
        if (is_object($arg) || is_callable($arg) || is_array($arg)) {
            $arg = print_r($arg, true);
        }

        echo "<pre>$arg</pre>";
    }
}