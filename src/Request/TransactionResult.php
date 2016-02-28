<?php namespace Academe\SagePay\Psr7\Request;

/**
 * Request the result of a transaction, stored on Sage Pay.
 * See "Retrieve and Transaction" https://test.sagepay.com/documentation/#transactions
 */

use Exception;
use UnexpectedValueException;

//use ReflectionClass;

use Academe\SagePay\Psr7\Model\Auth;

class TransactionResult extends AbstractRequest
{
    protected $resource_path = ['transactions', '{transactionId}'];

    protected $method = 'GET';

    protected $auth;

    /**
     * @param string $transactionId The ID that Sage Pay gave to the transaction
     */
    public function __construct(Auth $auth, $transactionId)
    {
        $this->transactionId = $transactionId;
        $this->auth = $auth;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function getBody()
    {
        return null;
    }

    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    } 
}
