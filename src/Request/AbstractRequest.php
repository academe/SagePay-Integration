<?php namespace Academe\SagePay\Psr7\Request;

/**
 * Shared message abstract.
 * Contains base methods that request messages will use.
 */

use Exception;
use UnexpectedValueException;

use DateTime;
use DateTimeZone;

use Academe\SagePay\Psr7\AbstractMessage;

use Academe\SagePay\Psr7\Factory\GuzzleFactory;

abstract class AbstractRequest extends AbstractMessage implements  \JsonSerializable
{
    protected $endpoint;
    protected $auth;
    protected $factory;
    protected $resource_path = [];

    /**
     * @var string Most messages are sent as POST requests, so this is the default
     */
    protected $method = 'POST';

    /**
     * 
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * 
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Support substitution strings; any {fooBar} mapped to $this->getFooBar()
     *
     * @returns array The path of this resource, as an array of path segments
     */
    public function getResourcePath()
    {
        $path = $this->resource_path;

        // Look for segments that need a substitution.
        $subtitution_parameters = preg_grep('/^\{.*\}$/', $path);

        if ( ! empty($subtitution_parameters)) {
            foreach($subtitution_parameters as $key => $sub) {
                // The name of the getter method.
                $method_name = 'get' . ucfirst(substr($sub, 1, -1));

                // Replace the value from the getter method.
                $path[$key] = $this->$method_name();
            }
        }

        return $path;
    }

    /**
     * @returns string The full URL of this resource
     */
    public function getUrl()
    {
        return $this->getEndpoint()->getUrl($this->getResourcePath());
    }

    /**
     * @returns string The HTTP method that the 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * The HTTP Basic Auth header, as an array.
     * Use this if your transport tool does not do "Basic Auth" out of the box.
     */
    protected function getBasicAuthHeaders()
    {
        return [
            'Authorization' => 'Basic '
                . base64_encode(
                    $this->auth->getIntegrationKey()
                    . ':' . $this->auth->getIntegrationPassword()
                ),
        ];
    }

    /**
     * Get the PSR-7 factory.
     * Create a factory if none supplied and relevant libraries are installed.
     */
    public function getFactory($exception = false)
    {
        if (!isset($this->factory) && GuzzleFactory::isSupported()) {
            // If the GuzzleFactory is supported (relevant Guzzle package is
            // installed) then instantiate this fatcory.

            $this->factory = new GuzzleFactory();
        }

        // If the exception flag is set, then throw an exception if we do not
        // have a factory.
        if ($exception && empty($this->factory)) {
            throw new Exception('No PSR-7 factory has been provided.');
        }

        return $this->factory;
    }

    /**
     * Return as a PSR-7 request message.
     */
    public function message()
    {
        return $this->getFactory(true)->JsonRequest(
            $this->getMethod(),
            $this->getUrl(),
            $this->getHeaders(),
            method_exists($this, 'jsonSerializePeek') ? json_encode($this->jsonSerializePeek()) : json_encode($this)
        );
    }

    /**
     * Set various flags - anything with a setFoo() method.
     */
    protected function setOptions(array $options = [])
    {
        foreach($options as $name => $value) {
            $method = 'set' . ucfirst($name);
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } else {
                // Unknown option.
                throw new UnexpectedValueException(sprintf('Unknown option %s', $name));
            }
        }
    }

    /**
     * Set various flags - anything with a setFoo() method.
     */
    public function withOptions(array $options = [])
    {
        $copy = clone $this;
        return $copy->setOptions($options);
    }

}
