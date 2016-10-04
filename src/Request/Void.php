<?php

namespace Academe\SagePay\Psr7\Request;

/**
 * This is the first of the "instructions" requests.
 * When further instructions are introduced, much functionality here is likely
 * to be moved out to an AbstractInstruction class.
 */

use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Model\Endpoint;

class Void extends AbstractRequest
{
}
