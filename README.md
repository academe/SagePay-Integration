# Sage Pay Integration Messages

This package provides the data models and business logic for the *Sage Pay Integration* payment gateway.
It does not provide the transport mechanism, so you can use what you like for that,
for example Guzzle, curl or a PSR-7 library.

The Sage Pay Integration payment gateway is a RESTful API run by by [Sage Pay](https://applications.sagepay.com/apply/3F7A4119-8671-464F-A091-9E59EB47B80C).

It is very much work in progress at this very early stage, while this Sage Pay API is in beta.
However,we aim to move quickly and follow changes to the API as they are released.
The aim is for the package to be a complete model for the Sage Pay Integration API, providing all the data
objects, messages (in both directions) and as much validation as is practical.
During this early stages you will find many of my thoughts on how this package will work, and
many U-turns too. Any feedback or suggestions is most welcome, but do bear in mind things will
be changing a lot, but hopefully heading in the right direction.

There is no test suite in here yet. That will come once the structure is a little more stable.

* Started by dumping everything into the "Models" folder.
* Now putting the messages into `Messages`.
* Messages are suffixed weith `Request` or `Response` depending on whether that go to Sage Pay or come from Sage Pay.
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
* 3DSecure is not supported by v1 of the API. Although v1 *can* take live payments, I would not recommend
  doing so until 3DSecure can be used. Without it, your liability as a merchant site for passing
  through fraudulent payments is much higher.

Current version of API spec is "11-08-2015 (beta)":
https://test.sagepay.com/documentation/#shipping-details-object

## Example Code

Using Guzzle 5.3 a Session key can be requested from the test environment like this:

~~~php
use GuzzleHttp\Client;

$auth = new \Academe\SagePayMsg\Models\Auth(
    'your_vendor_name',
    'YOUR_INTEGRATION_KEY',
    'YOUR_INTEGRATION_PASSWORD',
    \Academe\SagePayMsg\Models\Auth::MODE_TEST
);

$session_key_request = new \Academe\SagePayMsg\Message\SessionKeyRequest($auth);

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

// Creaye a SessionKeyResponse object from the Sage Pay Response.
$session_key_response = \Academe\SagePayMsg\Message\SessionKeyResponse::fromData($response->json());
~~~

Now we can use the session key to get a card token (like Sage Pay Direct, so server-to-server):

~~~php
// Construct the card request.
// This would normally be done on the browser using the sagepay.js script.
$card_identifier_request = new \Academe\SagePayMsg\Message\CardIdentifierRequest(
    $auth,
    $session_key_response,
    "MS. CARD HOLDER",
    "4929000000006",
    "0317",
    "123"
);

// New REST client to get the card identifier.
// The headers for this one call use the session key and not the integration key
// as they do for all other server-to-server POSTs.
$client = new Client();
$request = $client->createRequest('POST', $card_identifier_request->getUrl(), [
    'json' => $card_identifier_request->getBody(),
    'headers' => $card_identifier_request->getHeaders(),
]);

// Send the request.
// This will normally need to be wrapped in an exception handler.
$response = $client->send($request);

// The $session_key_response is not needed from this point. It was just needed to
// fetch the card identifier token.

// Collecting the response body.
// This can be intialised using any array that contains element `cardIdentifier` at a minimum.
$card_identifier_response = \Academe\SagePayMsg\Message\CardIdentifierResponse::fromData($response->json());

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

Now we can make a payment with details from the customer.

~~~php
// We have a billing address:
$billing_address = \Academe\SagePayMsg\Models\Address::fromArray(array(
    'address1' => 'address one',
    'postalCode' => 'NE26',
    'city' => 'Whitley',
    'state' => 'AL',
    'country' => 'US',
));

// A customer to bill:
$billing_person = new \Academe\SagePayMsg\Models\Person('Bill Firstname', 'Bill Lastname', 'billing@example.com', '+44 191 12345678');

// And we put the two together.
$billing_details = new \Academe\SagePayMsg\Models\BillingDetails($billing_person, $billing_address);

// We can do the same for shipping, but that is optional.

// There is an amount, in GBP in this case, to pay:
$amount = \Academe\SagePayMsg\Money\Amount::GBP()->withMajorUnit(9.99);

// And we are going to be paying that by card:
$card = new \Academe\SagePayMsg\PaymentMethod\Card($session_key, $card_identifier_response);

// Put it all together into a payment transaction:
$transaction = new \Academe\SagePayMsg\Message\TransactionRequest(
    $auth,
    \Academe\SagePayMsg\Message\TransactionRequest::TRANSACTION_TYPE_PAYMENT,
    $card,
    'MyVendorTxCode-' . rand(10000000, 99999999),
    $amount,
    'My Purchase Description',
    $billing_details
);

// Create a REST client to send the transaction:
$client = new Client();
$request = $client->createRequest('POST', $transaction->getUrl(), [
    'json' => $transaction->getBody(),
    'headers' => $transaction->getHeaders()],
]);

// And send it:
$response = $client->send($request);

// There are a number of results of sending that request, which need to be handled in
// a consistent way - there could be one API error, a server error, multiple validation
// errors, etc.
// Assuming there are no problems and we get a HTTP200, the result object is captured:
$transaction_response = \Academe\SagePayMsg\Message\TransactionResponse::fromData($response->json());

// The results of the payment should be in that object.
// More work is needed to make sense of the result, but that's the basic flow.
~~~

It's a start and something to learn from.

Firstly, we don't need to mess around with JSON. We don't want to locked into using
Guzzle 5.3, but it is a safe assumption that whatever HTTP client we use, it will
handle any JSON conversion in both directions. We'll base the rest of the library
on that assumption. We will try to handle arrays and objects provided by the
merchant application interchangeably.

It looks like the SessionKeyResponse and the Auth objects are always going to be
needed together with a Request object. Makeing Auth a property of SessionKeyResponse
may be a good move. So this:

    CardIdentifierResponse::fromData($response->json()

would become:

    CardIdentifierResponse::fromData($auth, $response->json())

Q: Should resource paths always start with a "/" and URLs never end with a "/"?
What do other projects standardise on?

