(function () {
    window.addEventListener(
        'load',
        function () {
            var inputElementEmail = document.querySelector('input[name="billing_email"], #billing_email');
            var inputElementPhoneNumber = document.querySelector('input[name="billing_phone"], #billing_phone');

            if (inputElementEmail) {
                inputElementEmail.addEventListener('change', inputListenerEmail);
            }
            if (inputElementPhoneNumber) {
                inputElementPhoneNumber.addEventListener('change', inputListenerPhoneNumber);
            }

            const address = getAddress()
            if ((inputElementEmail && extractEmailValue(inputElementEmail)) || (inputElementPhoneNumber && extractPhoneNumberValue(inputElementPhoneNumber))) {
                trackEvent(extractEmailValue(inputElementEmail), extractPhoneNumberValue(inputElementPhoneNumber), address.first_name, address.last_name, address.company, address.country, address.address_line_1, address.address_line_2, address.city, address.state, address.pin_code);
            } else if (contlo_checkout_vars.external_user_id) {
                trackEvent('', '', address.first_name, address.last_name, address.company, address.country, address.address_line_1, address.address_line_2, address.city, address.state, address.pin_code);
            }
        }
    );

    function getUrl(email, phone_number, first_name, last_name, company, country, address_line_1, address_line_2, city, state, pin_code) {
        return `${contlo_checkout_vars.ajax_url}?action=track_started_checkout_event
		&email=${encodeURIComponent(email)}
		&phone_number=${encodeURIComponent(phone_number)}
		&first_name=${encodeURIComponent(first_name)}
		&last_name=${encodeURIComponent(last_name)}
		&company=${encodeURIComponent(company)}
		&country=${encodeURIComponent(country)}
		&address_line_1=${encodeURIComponent(address_line_1)}
		&address_line_2=${encodeURIComponent(address_line_2)}
		&city=${encodeURIComponent(city)}
		&state=${encodeURIComponent(state)}
		&pin_code=${encodeURIComponent(pin_code)}`
    }

    function trackEvent(email, phone_number, first_name, last_name, company, country, address_line_1, address_line_2, city, state, pin_code) {
        return fetch(getUrl(email, phone_number, first_name, last_name, company, country, address_line_1, address_line_2, city, state, pin_code));
    }

    function extractEmailValue(inputElementEmail) {
        if (inputElementEmail.checkValidity && inputElementEmail.checkValidity()) {
            return inputElementEmail.value.trim();
        } else {
            return '';
        }
    }

    function extractPhoneNumberValue(inputElementPhoneNumber) {
        if (inputElementPhoneNumber.checkValidity && inputElementPhoneNumber.checkValidity()) {
            return inputElementPhoneNumber.value.trim();
        } else {
            return '';
        }

    }

    function getAddress() {
        var inputElementCountry = document.querySelector('input[name="billing_country"], #billing_country');
        var inputElementAddress1 = document.querySelector('input[name="billing_address_1"], #billing_address_1');
        var inputElementAddress2 = document.querySelector('input[name="billing_address_2"], #billing_address_2');
        var inputElementCity = document.querySelector('input[name="billing_city"], #billing_city');
        var inputElementState = document.querySelector('input[name="billing_state"], #billing_state');
        var inputElementPinCode = document.querySelector('input[name="billing_postcode"], #billing_postcode');
        var inputElementFirstName = document.querySelector('input[name="billing_first_name"], #billing_first_name');
        var inputElementLastName = document.querySelector('input[name="billing_last_name"], #billing_last_name');
        var inputCompany = document.querySelector('input[name="billing_company"], #billing_company');
        address = {}
        address['first_name'] = inputElementFirstName ? inputElementFirstName.value.trim() : ''
        address['last_name'] = inputElementLastName ? inputElementLastName.value.trim() : ''
        address['company'] = inputCompany ? inputCompany.value.trim() : ''
        address['country'] = inputElementCountry ? inputElementCountry.value.trim() : ''
        address['address_line_1'] = inputElementAddress1 ? inputElementAddress1.value.trim() : ''
        address['address_line_2'] = inputElementAddress2 ? inputElementAddress2.value.trim() : ''
        address['city'] = inputElementCity ? inputElementCity.value.trim() : ''
        address['state'] = inputElementState ? inputElementState.value.trim() : ''
        address['pin_code'] = inputElementPinCode ? inputElementPinCode.value.trim() : ''
        return address
    }

    function inputListenerEmail(event) {
        var email = extractEmailValue(event.target);
        var phone_number = extractPhoneNumberValue(document.querySelector('input[name="billing_phone"], #billing_phone'));
        const address = getAddress()
        if (email || phone_number) {
            trackEvent(email, phone_number, address.first_name, address.last_name, address.company, address.country, address.address_line_1, address.address_line_2, address.city, address.state, address.pin_code)
        }
    }

    function inputListenerPhoneNumber(event) {
        var phone_number = extractPhoneNumberValue(event.target);
        var email = extractEmailValue(document.querySelector('input[name="billing_email"], #billing_email'))
        const address = getAddress()
        if (email || phone_number) {
            trackEvent(email, phone_number, address.first_name, address.last_name, address.company, address.country, address.address_line_1, address.address_line_2, address.city, address.state, address.pin_code)
        }
    }
})();
