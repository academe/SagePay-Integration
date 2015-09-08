# SagePayJS

This package provides the data models and business logic for the SagePay Integration payment gateway.
It does not provide the transport mechanism, so you can use what you like for that,
for example Guzzle, curl or a PSR-7 library.

It is very much work in progress at this very early stage, while this SagePay API is in beta.
However,we aim to move quickly and follow changes to the API as they are released.
The aim is for the package to be a complete model for the SagePay Integration API, providing all the data
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

$session_key_request = new \Academe\SagePayJs\Message\SessionKeyRequest($auth);

$client = new Client();
$request = $client->createRequest('POST', $session_key_request->getUrl(), [
    // The body of the request as JSON.
    'json' => $session_key_request->getBody(),
    
    // HTTP Basic auth credentials:
    'auth' => [$session_key_request->getIntegrationKey(), $session_key_request->getIntegrationPassword()],
    
    // OR via the raw headers:
    'headers' => $session_key_request->getHeaders(),
]);
$response = $client->send($request);

// The response, if all is well, is a JSON body.

// Creaye a SessionKeyResponse object from the SagePay Response.
$session_key_response = \Academe\SagePayJs\Message\SessionKeyResponse::fromData($response->json());
~~~

Now we can use the session key to get a card token (like SagePay Direct):

~~~php
$card_identifier_request = new \Academe\SagePayJs\Message\CardIdentifierRequest(
    $auth,
    $session_key_response,
    "MS. CARD HOLDER",
    "4929000000006",
    "0317",
    "123"
);

$client = new Client();
$request = $client->createRequest('POST', $card_identifier_request->getUrl(), [
    'json' => $card_identifier_request->getBody(),
    'headers' => $card_identifier_request->getHeaders(),
]);

$card_identifier_response = \Academe\SagePayJs\Message\CardIdentifierResponse::fromData($response2->json());
var_dump($card_identifier_response->toArray());

// array(3) {
//  ["cardIdentifier"]=>
//  string(36) "F8FCA69C-0C3D-449A-9CDA-48B09D1493ED"
//  ["expiry"]=>
//  string(32) "2015-09-08T11:34:14.651000+01:00"
//  ["cardType"]=>
//  string(4) "Visa"
// }


~~~

That's all a little cumbersome, but it's a start and something to learn from.

Firstly, we don't need to mess around with JSON. We don't want to locked into using
Guzzle 5.3, but it is a safe assumption that whatever HTTP client we use, it will
handle any JSON conversion in both directions. We'll base the rest of the library
on that assumption. We will try to handle arrays and objects provided by the
merchant application interchangeably.

It looks like the SessionKeyResponse and the Auth objects are always going to be
needed together with a Request object. Makeing Auth a property of SessionKeyResponse
may be a good move. So this:

    CardIdentifierResponse::fromData($response2->json()

would become:

    CardIdentifierResponse::fromData($auth, $response2->json()

Q: Should resource paths always start with a "/" and URLs never end with a "/"?
What do other projects standardise on?

