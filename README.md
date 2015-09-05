# SagePayJS

Very much work in progress.

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

Current version of API spec is "11-08-2015":
https://test.sagepay.com/documentation/#shipping-details-object
