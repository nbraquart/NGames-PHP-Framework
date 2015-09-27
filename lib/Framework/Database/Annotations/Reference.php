<?php
namespace Framework\Database\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Reference
{

    public $targetClass;
}