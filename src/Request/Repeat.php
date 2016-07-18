<?php namespace Academe\SagePay\Psr7\Request;

/**
 * The repeat payment value object to send a transaction to Sage Pay.
 * See https://test.sagepay.com/documentation/#transactions
 */

use UnexpectedValueException;
use Academe\SagePay\Psr7\Model\Endpoint;
use Academe\SagePay\Psr7\Model\Auth;
use Academe\SagePay\Psr7\Money\AmountInterface;
use Academe\SagePay\Psr7\Model\AddressInterface;
use Academe\SagePay\Psr7\Model\PersonInterface;

class Repeat extends AbstractRequest
{
    // Supports the URL "api/v1/transactions/<transactionId>"
    protected $resource_path = ['transactions'];

    // Minimum mandatory data (constructor).
    protected $transactionId;
    protected $vendorTxCode;
    protected $amount;
    protected $description;

    // Optional or overridable data.
    protected $shippingAddress;
    protected $shippingRecipient;

    /**
     * @var string The prefix is added to the name fields when sending to Sage Pay
     */
    protected $shippingNameFieldPrefix = 'recipient';

    /**
     * @var string The prefix added to address name fields
     */
    protected $shippingAddressFieldPrefix = 'shipping';

    /**
     * Repeat payment constructor.
     *
     * @param Endpoint $endpoint
     * @param Auth $auth
     * @param string $transactionId
     * @param string $vendorTxCode
     * @param AmountInterface $amount
     * @param string $description
     * @param AddressInterface|null $shippingAddress
     * @param PersonInterface|null $shippingRecipient
     */
    public function __construct(
        Endpoint $endpoint,
        Auth $auth,
        $transactionId,
        $vendorTxCode,
        AmountInterface $amount,
        $description,
        AddressInterface $shippingAddress = null,
        PersonInterface $shippingRecipient = null
    ) {
        $this->setEndpoint($endpoint);
        $this->setAuth($auth);
        $this->setDescription($description);

        $this->setTransactionId($transactionId);
        $this->vendorTxCode = $vendorTxCode;
        $this->amount = $amount;

        $this->shippingAddress = $shippingAddress->withFieldPrefix($this->shippingAddressFieldPrefix);
        $this->shippingRecipient = $shippingRecipient->withFieldPrefix($this->shippingNameFieldPrefix);
    }

    /**
     * @param ShippingAddress $shippingAddress
     * @return Transaction
     */
    public function withShippingAddress(ShippingAddress $shippingAddress)
    {
        $copy = clone $this;
        $copy->shippingAddress = $shippingAddress;
        return $copy;
    }

    /**
     * @param ShippingRecipient $shippingRecipient
     * @return Repeat
     */
    public function withShippingRecipient(ShippingRecipient $shippingRecipient)
    {
        $copy = clone $this;
        $copy->shippingRecipient = $shippingRecipient;
        return $copy;
    }

    /**
     * @param $description
     * @return $this
     */
    protected function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param $description
     * @return Repeat
     */
    public function withDescription($description)
    {
        $copy = clone $this;
        return $copy->setDescription($description);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $transactionId
     * @return $this
     */
    protected function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @param $transactionId
     * @return Repeat
     */
    public function withTransactionId($transactionId)
    {
        $copy = clone $this;
        return $copy->setTransactionId($transactionId);
    }

    /**
     * @return string $transactionId
     */
    protected function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Get the message body data for serializing.
     * @return array
     */
    public function jsonSerialize()
    {
        // The mandatory fields.
        $result = [
            'transactionType' => static::TRANSACTION_TYPE_REPEAT,
            'referenceTransactionId' => $this->getTransactionId(),
            'vendorTxCode' => $this->vendorTxCode,
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrencyCode(),
            'description' => $this->getDescription(),
        ];

        $shippingDetails = [];

        if ( ! empty($this->shippingAddress)) {
            $shippingDetails = array_merge($shippingDetails, $this->shippingAddress->jsonSerialize());
        }

        if ( ! empty($this->shippingRecipient)) {
            // We only want the names from the recipient details.
            $shippingDetails = array_merge($shippingDetails, $this->shippingRecipient->getNamesBody());
        }

        // If there are shipping details, then merge it in:
        if ( ! empty($shippingAddress)) {
            $result['shippingDetails'] = $shippingDetails;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->getBasicAuthHeaders();
    } 
}
