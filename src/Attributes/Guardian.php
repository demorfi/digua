<?php declare(strict_types=1);

namespace Digua\Attributes;

use Digua\Interfaces\Guardian as GuardianInterface;
use Attribute;

#[Attribute]
abstract class Guardian implements GuardianInterface
{

}