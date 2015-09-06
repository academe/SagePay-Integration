# SagePayJS

This package provides the data models and business logic for the SagePay.JS payment gateway.
It does not provide the transport mechanism, so you can use what you like for that,
for example curl, PSR-7

It is very much work in progress at this very early stage, while this SagePay API is in beta.
However,we aim to move quickly and follow changes to the API as they are released.
The aim is for the package to be a complete model for the SagePay API, providing all the data
objects, messages (in both directions) and as much validation as is practical.

There is no test suite in here yet. That will come once the structure is a little more stable.

* Started by dumping everything into the "Models" folder.
* Now putting the messages into `Messages`.
* Messages are suffixed weith `Request` or `Response` depending on whether that go to SagePay or come from SagePay.
* The Response messages should be instantiable with a JSON or array object.
  That should also create any child objects that define the whole message.
  a locator service may be useful here if many objects are being created, so they can be overridden
  by the merchant application as needed.
* Trying to use value objects throughout, which is a new thing for me.
* Sticking to PHP 5.4 for now, and including an autoloader so is can be used outside of composer.
  May branch the package later to take on modern composer and older non-composer routes.
* This package will just handle the messages and business logic (e..g validation and structures).
  The HTTP communinications are to be handled in a separate package to wrap this.
  I'm trying to keep these two concerns separate for a number of reasons, least of all testing.

Collections or arrays? I feel collections would be better, to help maintain the right data structures.
For example, the error codes returned are an array of `Error` objects. Making this a collection will
ensure they really are `Error` objects, and can be extended with functionality that can help support
those errors, such as collecting them into errors for each submitted field, for example (the API docs
provides an example with multiple errors for field `cardDetails.cardholderName` (both wrong length and
containing invalid characters).

Current version of API spec is "11-08-2015 (beta)":
https://test.sagepay.com/documentation/#shipping-details-object

## Example Code

Using Guzzle 5.3 a Session key can be requested from the test environment like this:

~~~php
use GuzzleHttp\Client;

$auth = new \Academe\SagePayJs\Models\Auth(
    'your_vendor_name',
    'YOUR_INTEGRATION_KEY',
    'YOUR_INTEGRATION_PASSWORD',
    \Academe\SagePayJs\Models\Auth::MODE_TEST
);

$key_request = new \Academe\SagePayJs\Message\SessionKeyRequest($auth);

$client = new Client();
$request = $client->createRequest('POST', $key_request->getUrl(), [
    // The body of the request as JSON.
    'json' => ['vendorName' => $auth->getVendorName()],
    // HTTP Basic auth credentials.
    'auth' => [$auth->getIntegrationKey(), $auth->getIntegrationPassword()],
]);
$response = $client->send($request);

// The response, if all is well, is a JSON body.
$body = $response->json();
echo "BODY=" . $body;

// Example:
// BODY=BODY = Array
//(
//    [expiry] => 2015-09-06T23:50:39.123+01:00
//    [merchantSessionKey] => 77A4E43E-AECB-4250-BCA1-3BCDBBC57CE5
//)

~~~

That's all a little cumbersome, but it's a start and something to learn from.

Firstly, we don't need to mess around with JSON. We don't want to locked into using
Guzzle 5.3, but it is a safe assumption that whatever HTTP client we use, it will
handle any JSON conversion in both directions. We'll base the rest of the library
on that assumption.

