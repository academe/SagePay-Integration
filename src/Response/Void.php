<?php

namespace Academe\SagePay\Psr7\Response;

/**
 * Value object to hold the void instruction response.
 * Much of this will likely be moved to an abstract once further
 * instruction types are rolled out.
 */

use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Helper;

class Void extends AbstractInstruction
{
}
