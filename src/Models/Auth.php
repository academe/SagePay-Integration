<?php namespace Academe\SagePayMsg\Models;

/**
 * Value object given the account authentication details.
 * Provides the as needed, and the correct base URL.
 */

use Exception;
use UnexpectedValueException;

class Auth
{
    protected $vendorName;
    protected $integrationKey;
    protected $integrationPassword;
    protected $mode;

    // This release is locked onto just one API version.
    const API_VERSION = 'v1';

    const MODE_LIVE = 1;
    const MODE_TEST = 2;

    protected $urls = [
        1 => 'https://www.sagepay.com/api/{version}{resource}',
        2 => 'https://test.sagepay.com/api/{version}{resource}',
    ];

    public function __construct(
        $vendorName,
        $integrationKey,
        $integrationPassword,
        $mode = self::MODE_LIVE
    ) {
        $this->vendorName = $vendorName;
        $this->integrationKey = $integrationKey;
        $this->integrationPassword = $integrationPassword;

        // The mode - testing or production. Possible others later.
        if ( ! isset($this->urls[$mode])) {
            throw new UnexpectedValueException(sprintf('Unexpected mode value "%s"', $mode));
        }

        $this->mode = $mode;
    }

    public function getVendorName()
    {
        return $this->vendorName;
    }

    public function getIntegrationKey()
    {
        return $this->integrationKey;
    }

    public function getIntegrationPassword()
    {
        return $this->integrationPassword;
    }

    public function getApiVersion()
    {
        return static::API_VERSION;
    }

    /**
     * Get the endpoint URL.
     * A resource can be supplied as a string or array (if it has multiple path parts).
     * String resources must include a "/" prefix and be ready-encoded for the URL.
     * A resource as an array should not have directory separators included, and will
     * be url encoded here, so should not be done in advance.
     */
    public function getUrl($resource = '')
    {
        // If the resource is an array, then combine it into the path.
        if (is_array($resource)) {
            // Encode all parts of the path.
            $resource = '/' . implode('/', array_map('rawurlencode', $resource));
        }

        return str_replace(
            ['{version}', '{resource}'],
            [$this->getApiVersion(), $resource],
            $this->urls[$this->mode]
        );
    }

    /**
     * Get the URL of sagepay.js - the card token generator for the front end.
     */
    public function getJavascriptUrl()
    {
        return $this->getUrl(['js', 'sagepay.js']);
    }

    /**
     * Return a testing instance (since it was an optioal setting on first instantiation).
     */
    public function withTestingMode()
    {
        $copy = clone $this;
        $copy->mode = static::MODE_TEST;
        return $copy;
    }

    /**
     * Indicates whether we are using a test account.
     */
    public function isTesting()
    {
        return $this->mode == static::MODE_TEST;
    }

    /**
     * Override any of the URLs.
     * Supports replacement fields {version} and {resource}
     */
    public function withUrl($mode, $url)
    {
        if ( ! isset($this->urls[$mode])) {
            throw new UnexpectedValueException(sprintf('Unexpected mode value "%s"', $mode));
        }

        $copy = clone $this;
        $copy->urls[$mode] = $url;
        return $copy;
    }
}
