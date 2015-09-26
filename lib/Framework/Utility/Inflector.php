<?php
namespace Framework\Utility;

class Inflector
{

    public static function camelize($string)
    {
        return lcfirst(str_replace(' ', '', self::humanize($string)));
    }

    public static function underscore($string)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    public static function humanize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    public static function pluralize($variable, $x = false)
    {
        return $variable > 1 ? ($x ? 'x' : 's') : '';
    }

    public static function ellipsis($string, $maxLength)
    {
        if (strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength - 4) . ' ...';
        }
        
        return $string;
    }
}