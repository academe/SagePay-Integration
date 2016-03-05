<?php namespace Academe\SagePay\Psr7\Response;

/**
 * Value object to hold the card identifier, returned by SagePay.
 * Reasonable validation is done at creation.
 */

use DateTime;
use DateTimeZone;

use Exception;
use UnexpectedValueException;
use Academe\SagePay\Psr7\Helper;
use Psr\Http\Message\ResponseInterface;

class CardIdentifier extends AbstractResponse
{
    protected $cardIdentifier;
    protected $expiry;
    protected $cardType;

    /**
     * @param array|object $message The data returned from SagePay in the response body.
     */
    public function __construct(ResponseInterface $message = null)
    {
        if (isset($message)) {
            $this->setData($this->parseBody($message), $message->getStatusCode());
            $this->setHttpCode($message->getStatusCode());
        }
    }

    /**
     * @param array|object $data The parsed data sent or returned by Saeg Pay.
     */
    protected function setData($data, $httpCode)
    {
        $this->setHttpCode($this->deriveHttpCode($httpCode, $data));

        $this->cardIdentifier = Helper::structureGet($data, 'cardIdentifier', null);
        $this->expiry = Helper::parseDateTime(Helper::structureGet($data, 'expiry', null));
        $this->cardType = Helper::structureGet($data, 'cardType', null);

        return $this;
    }

    public static function fromData($data, $httpCode = null)
    {
        $message = new static();
        return $message->setData($data, $httpCode);
    }

    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

    public function getExpiry()
    {
        return $this->expiry;
    }

    public function getCardType()
    {
        return $this->cardType;
    }

    public function isExpired()
    {
        // Use the default system timezone; the DateTime comparison
        // operation will handle any timezone conversions.
        // Note that this does not do a remote check with the Sage Pay
        // API. We can only find out if it is really still valid by
        // attempting to use it.

        $time_now = new DateTime();

        return ! isset($this->expiry) || $time_now > $this->expiry;
    }

    /**
     * Reduce the object to an array so it can be serialised.
     */
    public function jsonSerialize()
    {
        return [
            'cardIdentifier' => $this->cardIdentifier,
            'expiry' => $this->expiry->format(Helper::SAGEPAY_DATE_FORMAT),
            'cardType' => $this->cardType,
            'httpCode' => $this->httpCode,
        ];
    }
}
