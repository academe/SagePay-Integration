<?php namespace Academe\SagePay\Psr7\Response;

/**
 * Value object to hold the card identifier, returned by SagePay.
 * Reasonable validation is done at creation.
 */

use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Academe\SagePay\Psr7\Helper;

class CardIdentifier extends AbstractResponse
{
    protected $cardIdentifier;
    protected $expiry;
    protected $cardType;

    /**
     * @param array|object|ResponseInterface $message The data returned from SagePay in the response body.
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
     * @return $this
     */
    protected function setData($data)
    {
        $this->cardIdentifier = Helper::dataGet($data, 'cardIdentifier', null);
        $this->expiry = Helper::parseDateTime(Helper::dataGet($data, 'expiry', null));
        $this->cardType = Helper::dataGet($data, 'cardType', null);

        return $this;
    }

    /**
     * @param array|object|string $data
     * @param null $httpCode
     * @return mixed
     */
    public static function fromData($data, $httpCode = null)
    {
        $message = new static();
        return $message->setData($data, $httpCode);
    }

    /**
     * @return mixed
     */
    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

    /**
     * The expiry timestamp of the card identifier resource, not the expiry date of the card.
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @return mixed
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @return bool
     */
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
     * @inheritdoc
     */
    public static function isResponse(array $data)
    {
        return !empty(Helper::dataGet($data, 'cardIdentifier'));
    }

    /**
     * Reduce the object to an array so it can be serialised.
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'httpCode' => $this->getHttpCode(),
            'cardIdentifier' => $this->cardIdentifier,
            'expiry' => $this->expiry->format(Helper::SAGEPAY_DATE_FORMAT),
            'cardType' => $this->cardType,
        ];
    }
}
