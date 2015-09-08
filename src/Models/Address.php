<?php namespace Academe\SagePay\Models;

/**
 * Value object used to define the customer's billing address.
 * Reasonable validation is done at creation.
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePay\Iso3166\Countries;
use Academe\SagePay\Iso3166\States;

class Address implements AddressInterface
{
    protected $address1;
    protected $address2;
    protected $city;
    protected $postalCode;
    protected $country;
    protected $state;

    protected $fieldPrefix = '';

    public function __construct($address1, $address2, $city, $postalCode, $country, $state)
    {
        // These fields are always mandatory.
        foreach(array('address1', 'city', 'country') as $field_name) {
            if (empty($$field_name)) {
                throw new UnexpectedValueException(sprintf('Field "%s" is mandatory but not set.', $field_name));
            }
        }

        // Validate Country is ISO 3166-1 code.
        if ( ! Countries::isValid($country)) {
            throw new UnexpectedValueException(sprintf('Country code "%s" is not recognised.', (string)$country));
        }

        // State must be set if country is US.
        if ($country == 'US') {
            if (empty($state)) {
                throw new UnexpectedValueException('State must be provided for US country.');
            }

            // Validate State is ISO 3166-2 code.
            if ( ! States::isValid($country, $state)) {
                throw new UnexpectedValueException(sprintf(
                    'State code "%s" for country "%s" is not recognised.', (string)$state, (string)$country
                ));
            }
        }

        // State must not be set if country is not US.
        if ($country != 'US' && ! empty($state)) {
            throw new UnexpectedValueException('State must be left blank for non-US countries.');
        }

        // postCode is optional only if country is IE.
        if ($country != 'IE' && empty($postalCode)) {
            throw new UnexpectedValueException('Postalcode is mandatory for non-IE countries.');
        }

        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->state = $state;
    }

    /**
     * Create a new instance from an array of values.
     * TODO: use the from_data helper, currently in the abstract message.
     */
    public static function fromArray($params)
    {
        return new static(
            isset($params['address1']) ? $params['address1'] : null,
            isset($params['address2']) ? $params['address2'] : null,
            isset($params['city']) ? $params['city'] : null,
            isset($params['postalCode']) ? $params['postalCode'] : null,
            isset($params['country']) ? $params['country'] : null,
            isset($params['state']) ? $params['state'] : null
        );
    }

    protected function addAddressPrefix($field)
    {
        if ( ! $this->fieldPrefix) {
            return $field;
        }

        return $this->fieldPrefix . ucfirst($field);
    }

    /**
     * Return the body partial for message construction.
     * Includes all mandatory fields, and optional fields only if not empty.
     * Takes into account the field name prefix, if set.
     */
    public function getBody()
    {
        $return = array(
            $this->addAddressPrefix('address1') => $this->address1,
        );

        if ( ! empty($this->address2)) {
            $return[$this->addAddressPrefix('address2')] = $this->address2;
        }

        $return[$this->addAddressPrefix('city')] = $this->city;

        if ( ! empty($this->postalCode)) {
            $return[$this->addAddressPrefix('postalCode')] = $this->postalCode;
        }

        $return[$this->addAddressPrefix('country')] = $this->country;

        if ( ! empty($this->state)) {
            $return[$this->addAddressPrefix('state')] = $this->state;
        }

        return $return;
    }

    /**
     * Set the field prefix used when returning the object as an array.
     */
    public function withFieldPrefix($fieldPrefix)
    {
        $copy = clone $this;
        $copy->fieldPrefix = $fieldPrefix;
        return $copy;
    }
}
