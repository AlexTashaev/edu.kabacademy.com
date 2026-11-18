// local_kabsignup — phone field with a country flag and dial code (intl-tel-input 18).
// The person types the national number; on submit it becomes +<dial code><digits>,
// the format the account (phone1), the duplicate check and the ad events expect.
// Without JavaScript the field stays a plain text input and the server takes
// whatever was typed.
(function() {
    'use strict';

    function compose(raw, dialCode) {
        var value = raw.trim();
        if (value === '' || value.charAt(0) === '+' || value.indexOf('00') === 0 || !dialCode) {
            return value;
        }
        var digits = value.replace(/\D/g, '');
        if (digits === '') {
            return value;
        }
        // Typed with the country code but without "+": 79991234567, 972541234567.
        if (digits.indexOf(dialCode) === 0 && digits.length > 10) {
            return '+' + digits;
        }
        if (dialCode === '7' && digits.length === 11 && digits.charAt(0) === '8') {
            // Russian / Kazakh trunk prefix: 8 999 … → +7 999 …
            digits = digits.slice(1);
        } else {
            // Trunk zero elsewhere: 054 … → +972 54 …
            digits = digits.replace(/^0+/, '');
        }
        return '+' + dialCode + digits;
    }

    function init() {
        var input = document.querySelector('form.mform input[name="phone"]');
        if (!input || !window.intlTelInput || input.getAttribute('data-ks-iti')) {
            return;
        }
        input.setAttribute('data-ks-iti', '1');
        input.removeAttribute('placeholder');
        var iti = window.intlTelInput(input, {
            initialCountry: input.getAttribute('data-country') || '',
            preferredCountries: ['ru', 'il', 'ua', 'kz', 'de', 'us'],
            separateDialCode: true,
            autoPlaceholder: 'off',
            formatOnDisplay: false
        });
        if (input.form) {
            input.form.addEventListener('submit', function() {
                var data = iti.getSelectedCountryData() || {};
                input.value = compose(input.value, data.dialCode || '');
            }, true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
