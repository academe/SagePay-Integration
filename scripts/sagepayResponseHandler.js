/**
 * Minimal usage (with jQuery):
 *
 * $(document).ready(function() {
 *     $('#payment').on('submit', function(event) {
 *         // Supress normal submit rehaviour.
 *         event.preventDefault();
 *
 *         Sagepay.tokeniseCardDetails(form, function(status, response) {
 *             new sagepayResponseHandler(status, response, {
 *                 // Initialise the response handler, after getting a response from Sage Pay.
 *                 init: function() {
 *                     // e.g. re-enable submit button, clear current form error messages.
 *                 },
 *                 // A card token has been successfuly obtained.
 *                 success: function() {
 *                     // Add this.cardIdentifier to the form then submit it.
 *                 },
 *                 // Display errors in the event of invalid fields.
 *                 invalid: function() {
 *                     // this.errors will contain arrays of messages, keyed to the field
 *                     // name, so messages can be placed on the form against the fields.
 *                 },
 *                 // Renew the session key.
 *                 renewsession: function() {
 *                     // The session key needs to be renewed: do an AJAX fetch, submit/refresh
 *                     // the form etc.
 *                 }
 *             });
 *         });
 *     });
 * });
 *
 * Some more detailed examples will be provided in the documenatation. We also need a handler
 * for catching more generic/global errors that are not bound to specific form fields.
 *
 */

'use strict';
// This handler handles all responses from the Sage Pay tokeniser.
function sagepayResponseHandler(status, response, options) {
	this.status = status;
	this.response = response;
	this.options = options;

	if (typeof options.init === "function") {
		options.init.apply(this);
	}

	// The 201 status means the card token resource has been created.
	if (status === 201) {
		this.cardIdentifier = response.cardIdentifier;
		this.expiry = response.expiry;
		this.cardType = response.cardType;

		if (typeof options.success === "function") {
			options.success.apply(this);
		}
	} else if (status === 401) {
		// The session token has expired - it is 400 seconds old or has been used three times.
		// It needs to be renewed - provide a callback so the site can decide how to do that:
		// maybe an AJAX fetch of a fresh session token, maybe an AJAX fetch of the section of the form
		// containing the card details fields, maybe submitting the whole form to refresh.
		// TODO: fallback action if no callback is provided.

		if (typeof options.renewsession === "function") {
			options.renewsession.apply(this);
		}
	} else {
		// Show the errors on the form.
		this.errors = response.responseJSON.errors;

		if (typeof options.invalid === "function") {
			options.invalid.apply(this);
		}
	}
}
