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

* The `Academe\SagePayMsg\Models` namespace comntains internal models for various data structures and for constructing messages.
* The `Academe\SagePayMsg\Messages` namespace is for message structures that go to and from Sage Pay.
* Messages are suffixed with `Request` or `Response` depending on whether that go to Sage Pay or come from Sage Pay.
* The Response messages should be instantiable with a JSON or array object.
  They should also create any child objects that define the whole message.
  A locator service may be useful here if many objects are being created, so they can be overridden
  by the merchant application as needed.
* Unmutable value objects are used throughout, where possible.
* Sticking to PHP 5.4 for now, and including an autoloader so is can be used outside of composer.
  There will be non-composer applications, such as WordPress plugins, that will benefit from this.
* This package will just handle the messages and business logic (e.g. validation and data structures).
  The HTTP communinications are to be handled outside this package.
  I'm trying to keep these two concerns separate for a number of reasons, least of all testing.
* 3DSecure is not supported by v1 of the API. Although v1 *can* take live payments, I would not recommend
  doing so until 3DSecure can be used. Without it, your liability as a merchant site for passing
  through fraudulent payments is much higher. v2 of the API is reported to include an implementation of 3DSecure.

Current version of API spec is "11-08-2015 (beta)" - 2015-08-11 would be a better date:
https://test.sagepay.com/documentation/#shipping-details-object

## Example Code

Using Guzzle 5.3 a Session key can be requested from the test environment as follows.
This happens on tyhe server before presenting the user with the payment form:

~~~php
use GuzzleHttp\Client;

use Academe\SagePayMsg\Models\Auth;
use Academe\SagePayMsg\Message\SessionKeyRequest;
use Academe\SagePayMsg\Message\SessionKeyResponse;

$auth = new Auth(
    'your_vendor_name',
    'YOUR_INTEGRATION_KEY',
    'YOUR_INTEGRATION_PASSWORD',
    Auth::MODE_TEST
);

$session_key_request = new SessionKeyRequest($auth);

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
$session_key_response = SessionKeyResponse::fromData($response->json());
~~~

Now we can use the session key to get a card token (like Sage Pay Direct, so server-to-server).
This will normally be done on the browser using `sagepay.js` to do the AJAX call.
However, you do not have to use `sagepay.js` - it is a straight-forward POST and you can
certainly improve on the script provided by default, or adapt it to specific use-cases:


~~~php
use Academe\SagePayMsg\Message\CardIdentifierRequest;
use Academe\SagePayMsg\Message\CardIdentifierResponse;

// Construct the card request.
// This would normally be done on the browser using the sagepay.js script.
$card_identifier_request = new CardIdentifierRequest(
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
$card_identifier_response = CardIdentifierResponse::fromData($response->json());

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

Now we can make a payment with details from the customer. That payment will include
customer and product or service details, and the card token we just obtained.

~~~php
use Academe\SagePayMsg\Models\Address;
use Academe\SagePayMsg\Models\Person;
use Academe\SagePayMsg\Models\BillingDetails;
use Academe\SagePayMsg\Money\Amount;
use Academe\SagePayMsg\PaymentMethod\Card;
use Academe\SagePayMsg\Message\TransactionRequest;
use Academe\SagePayMsg\Message\TransactionResponse;

// We have a billing address:
$billing_address = Address::fromData([
    'address1' => 'address one',
    'postalCode' => 'NE26',
    'city' => 'Whitley',
    'state' => 'AL',
    'country' => 'US',
]);

// A customer to bill:
$billing_person = new Person(
    'Bill Firstname',
    'Bill Lastname',
    'billing@example.com',
    '+44 191 12345678'
);

// And we put the two together.
$billing_details = new BillingDetails($billing_person, $billing_address);

// We can do the same for shipping, but that is optional.

// There is an amount, in GBP in this case, to pay:
$amount = Amount::GBP()->withMajorUnit(9.99);

// And we are going to be paying that by card:
$card = new Card($session_key, $card_identifier_response);

// Put it all together into a payment transaction:
$transaction = new TransactionRequest(
    $auth,
    TransactionRequest::TRANSACTION_TYPE_PAYMENT,
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
// errors, etc. The API is still a little in flux in this area, so I'll leave detailed
// examples until later.
// Assuming there are no problems and we get a HTTP 200, the result object is captured:
$transaction_response = TransactionResponse::fromData($response->json());

// The results of the payment should be in that object.
// More work is needed to make sense of the result, but that's the basic flow. Here it
// is expanded into a more workable example,.but do bear in mind this is likely to change:

use Academe\SagePayMsg\Models\ErrorCollection;
use Academe\SagePayMsg\Models\Error;

try {
    $response = $client->send($request);

    // Now create the transaction response from the return data.
    $transaction_response = TransactionResponse::fromData($response->json());
    var_dump($transaction_response);
} catch(\GuzzleHttp\Exception\ClientException $e) {
    // Get the response that Guzzle has saved and added to its exception.
    $response = $e->getResponse();

    // Here we have one or more errors.
    // We get multiple errors when the error code is 422, otherwise we get just the one error.
    if ($response->getStatusCode() == 422) {
        // Put the errors or errors into a collection.
        $errors = ErrorCollection::fromData($response->json());

        // The error collection will be able to return errors organised by the field name.
        foreach($errors as $error) {
            // These errors would be fed to the re-presented form for display in context.
            echo "<p>Error code " . $error->getCode()
                . " (" . $error->getDescription() . ")"
                . " on field " . $error->getProperty() . "</p>";
        }
    } else {
        // A more serious error occurred; not just field validation issues.
        // One error may be an expiry of the session key or card identifier.
        // These will not be fatal errors, but will require asking the user for
        // their card details again. More details to come.
        $error = Error::fromData($response3->json());
        echo "Error " . $error->getCode() . " " . $error->getDescription();
    }
} catch(\GuzzleHttp\Exception\ServerException $e) {
    // Could not even talk to Sage Pay.
    echo "Problem at Sage Pay";
}
~~~

Some lessons:

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

### A Typical Workflow

1. The server will get a session key. This will last for 200 seconds or three uses.
2. The payment form is presented, with credit card fields and personal details fields.
3. The user will complete the form and press submit.
4. sagepay.js will catch the submit and attempt to fetch a card token givem the card details
   entered by the user.
5. If the session key has expired, then fetch a new one - probably AJAX.
6. Once a card token is obtained, the form will be submitted. The card token will also last
   for 200 seconds or three uses.
7. On the server, attempt to submit the form details and card token as a transaction.
8. If the transaction is returned as invalid, then re-present the form with supplied error messages.
9. If the card token has not expired, then reuse it and do *not* present the credit card fields to the user.
10. If the card token has expired, then the credit card fields need to be presented to the
   user again, and that will require another session key to include in the form.
11. If the transaction was accepted, then the successful result can be recorded and the user informed.

The key things to remember are:

* The session key can be found to be expired at any time.
* The card token will expire after a successful submission of a transaction, or three invalid transactions.
* Do not present the credit card fields to the user if we habe a valid card token.
* Do not call sagepay.js on form submit if either we have a card token, or there are no credit card fields in the form.
