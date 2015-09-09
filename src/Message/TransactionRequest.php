<?php namespace Academe\SagePayMsg\Message;

/**
 * The transaction value object to send a transaction to SagePay.
 * TODO: look at all the other transaction types - they are very different messages.
 *   Maybe change this one to a Payment only, with an abstract class to hold
 *   the constants and shared request message functionality?
 */

use Exception;
use UnexpectedValueException;

use Academe\SagePayMsg\Models\Auth;
use Academe\SagePayMsg\PaymentMethod\PaymentMethodInterface;
use Academe\SagePayMsg\Money\AmountInterface;
use Academe\SagePayMsg\Models\AddressInterface;
use Academe\SagePayMsg\Models\ShippingDetails;
use Academe\SagePayMsg\Models\BillingDetails;

class TransactionRequest extends AbstractRequest
{
    protected $resource_path = ['transactions'];

    protected $auth;

    // Minimum mandatory data (constructor).
    protected $transactionType;
    protected $paymentMethod;
    protected $vendorTxCode;
    protected $amount;
    protected $description;
    protected $billingDetails;

    // Optional or overridable data (setters).
    protected $entryMethod = 'Ecommerce';
    protected $recurringIndicator;
    protected $giftAid = false;
    protected $applyAvsCvcCheck;
    protected $apply3DSecure;
    // Customer email and phone moved to the billingDetails.
    //protected $customerEmail;
    //protected $customerPhone;
    protected $shippingDetails;

    /**
     * Valid values for enumerated input types.
     */

    protected $transaction_types = array(
        'payment' => 'Payment',
    );

    protected $entry_methods = array(
        'ecommerce' => 'Ecommerce',
        'mailorder' => 'MailOrder',
        'telephoneorder' => 'TelephoneOrder',
    );

    protected $recurring_indicators = array(
        'recurring' => 'Recurring',
        'instalment' => 'Instalment',
    );

    protected $apply_avs_cvc_checks = array(
        'force' => 'Force',
        'disable' => 'Disable',
        'forceignoringrules' => 'ForceIgnoringRules',
    );

    protected $apply_3d_secures = array(
        'force' => 'Force',
        'disable' => 'Disable',
        'forceignoringrules' => 'ForceIgnoringRules',
    );

    public function __construct(
        Auth $auth,
        $transactionType,
        PaymentMethodInterface $paymentMethod,
        $vendorTxCode,
        AmountInterface $amount,
        $description,
        BillingDetails $billingDetails,
        ShippingDetails $shippingDetails = null
    ) {
        $this->auth = $auth;
        $this->description = $description;

        // Some simple normalisation.
        $transactionType = ucfirst(strtolower($transactionType));

        // Is the transaction type one we are expecting?
        if ( ! in_array($transactionType, $this->transaction_types)) {
            throw new UnexpectedValueException(sprintf('Unknown transaction type "%s".', (string)$transactionType));
        }

        $this->transactionType = $transactionType;
        $this->paymentMethod = $paymentMethod;
        $this->vendorTxCode = $vendorTxCode;
        $this->amount = $amount;
        $this->billingDetails = $billingDetails;
        $this->shippingDetails = $shippingDetails;
    }

    public function withEntryMethod($entryMethod)
    {
        if ( ! isset($this->entry_methods[strtolower($entryMethod)])) {
            throw new UnexpectedValueException(sprintf(
                'Unknown entryMethod "%s"; require one of %s',
                (string)$entryMethod,
                implode(', ', $this->getEntryMethods())
            ));
        }
        $entryMethod = $this->entry_methods[strtolower($entryMethod)];

        $copy = clone $this;
        $copy->entryMethod = $entryMethod;
        return $copy;
    }

    public function getEntryMethods()
    {
        return $this->entry_methods;
    }

    public function withRecurringIndicator($recurringIndicator)
    {
        if ( ! in_array($recurringIndicator, $this->getRecurringIndicators())) {
            throw new UnexpectedValueException(sprintf(
                'Unknown recurringIndicator "%s"; require one of %s',
                (string)$recurringIndicator,
                implode(', ', $this->getRecurringIndicators())
            ));
        }

        $copy = clone $this;
        $copy->recurringIndicator = $recurringIndicator;
        return $copy;
    }

    public function getRecurringIndicators()
    {
        return $this->recurring_indicators;
    }

    public function withGiftAid($giftAid)
    {
        $copy = clone $this;
        $copy->giftAid = ! empty($giftAid);
        return $copy;
    }

    public function withApplyAvsCvcCheck($applyAvsCvcCheck)
    {
        if ( ! in_array($applyAvsCvcCheck, $this->getApplyAvsCvcChecks())) {
            throw new UnexpectedValueException(sprintf(
                'Unknown applyAvsCvcCheck "%s"; require one of %s',
                (string)$applyAvsCvcCheck,
                implode(', ', $this->getApplyAvsCvcChecks())
            ));
        }

        $copy = clone $this;
        $copy->applyAvsCvcCheck = $applyAvsCvcCheck;
        return $copy;
    }

    public function getApplyAvsCvcChecks()
    {
        return $this->apply_avs_cvc_checks;
    }

    public function withApply3DSecure($apply3DSecure)
    {
        if ( ! in_array($apply3DSecure, $this->getApply3DSecures())) {
            throw new UnexpectedValueException(sprintf(
                'Unknown apply3DSecure "%s"; require one of %s',
                (string)$apply3DSecure,
                implode(', ', $this->getApply3DSecures())
            ));
        }

        $copy = clone $this;
        $copy->apply3DSecure = $apply3DSecure;
        return $copy;
    }

    public function getApply3DSecures()
    {
        return $this->apply_3d_secures;
    }

    public function withShippingDetails(ShippingDetails $shippingDetails)
    {
        $copy = clone $this;
        $copy->shippingDetails = $shippingDetails;
        return $copy;
    }

    public function withDescription($description)
    {
        $copy = clone $this;
        $copy->description = $description;
        return $copy;
    }

    public function getBody()
    {
        $result = array(
            'transactionType' => $this->transactionType,
            'paymentMethod' => $this->paymentMethod->getBody(),
            'vendorTxCode' => $this->vendorTxCode,
            'amount' => $this->amount->getAmount(),
            'currency' => $this->amount->getCurrencyCode(),
            'description' => $this->description,
            'entryMethod' => $this->entryMethod,
        );

        // Add the billing details.
        $result = array_merge($result, $this->billingDetails->getBody());

        // If there are shipping details, then merge this in:
        if ( ! empty($this->shippingDetails)) {
            $result['shippingDetails'] = $this->shippingDetails->getBody();
        }

        return $result;
    }
}
