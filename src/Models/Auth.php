<?php namespace Academe\SagePayJs\Models;

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

    public function __construct(
        $vendorName,
        $integrationKey,
        $integrationPassword,
        $mode = static::MODE_LIVE
    ) {
        const MODE_TESTING = 1;
        const MODE_LIVE = 2;

        protected $urls = [
            1 => 'https://test.sagepay.com/api/{version}/{resource}',
            2 => 'https://www.sagepay.com/api/{version}/{resource}',
        ];

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

    public function getUrl($resource = '')
    {
        // If the resource is an array, then combine it into the path.
        if (is_array($resource)) {
            // Encode all parts of the path.
            $resource = implode('/', array_map('rawurlencode', $resource));
        }

        return str_replace(
            ['{version}', '{resource}'],
            [$this->getApiVersion(), $resource],
            $this->urls[$this->mode]
        );
    }

    public function isTesting()
    {
        return $this->mode == static::MODE_TESTING;
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
