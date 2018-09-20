<?php
namespace LiteFrame\Utility;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

/**
 * {@inheritdoc}
 */
class Inflector extends DoctrineInflector
{
    public static function slugify($text)
    {
        return str_replace('_', '-', static::tableize($text));
    }

    public static function underscore($text)
    {
        return static::tableize($text);
    }

    public static function pluraltable($name)
    {
        $table = static::tableize($name);
        return static::pluralize($table);
    }
}
