<?php
namespace LiteFrame\Utility;

use Doctrine\Common\Inflector\Inflector;

/**
 * {@inheritdoc}
 */
class Inflector extends Inflector
{
    public static function slugify($text)
    {
        return str_replace('_', '-', static::tableize($text));
    }
}
