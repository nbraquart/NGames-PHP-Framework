<?php
namespace Ngames\Framework\Database\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Reference
{

    public $targetClass;
}