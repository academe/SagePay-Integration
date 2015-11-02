<?php namespace Academe\SagePayMsg\Model;

/**
 * Value object used to define the shipping recipient and address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

class ShippingDetails extends BillingDetails
{
    /**
     * @var string The prefix is added to the name fields when sending to Sage Pay
     */
    protected $nameFieldPrefix = 'recipient';

    /**
     * @var string The prefix added to address name fields
     */
    protected $addressFieldPrefix = 'shipping';

    /**
     * These are all on the same level, with field name prefixes.
     *
     * @return array Body fragment for the shipping details, requiring conversion to JSON
     */
    public function getBody()
    {
        // Only the person names are permitted for the shipping detail,
        // not the email and phone.
        return array_merge(
            $this->address->getBody(),
            $this->person->getNamesBody()
        );
    }
}
