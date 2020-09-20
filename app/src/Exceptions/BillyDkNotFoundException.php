<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk\Exceptions;

use Kenashkov\ErpApi\Interfaces\ErpNotFoundExceptionInterface;

class BillyDkNotFoundException extends BillyDkException implements ErpNotFoundExceptionInterface
{

}