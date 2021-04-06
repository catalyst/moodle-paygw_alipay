<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains class for alipay payment gateway.
 *
 * @package    paygw_alipay
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_alipay;

/**
 * The gateway class for alipay payment gateway.
 *
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {
    public static function get_supported_currencies(): array {
        // 3-character ISO-4217: https://en.wikipedia.org/wiki/ISO_4217#Active_codes.
        return [
            'CNY', 'GBP', 'HKD', 'USD', 'CHF', 'SGD', 'SEK', 'DKK', 'NOK', 'JPY', 'CAD',
            'AUD', 'EUR', 'NZD', 'MOP'
        ];
    }

    /**
     * Configuration form for the gateway instance
     *
     * Use $form->get_mform() to access the \MoodleQuickForm instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();
        $config = $form->get_gateway_persistent()->get_configuration();

        $mform->addElement('text', 'endpoint', get_string('endpoint', 'paygw_alipay'));
        $mform->setType('endpoint', PARAM_URL);
        $mform->addHelpButton('endpoint', 'endpoint', 'paygw_alipay');

        $mform->addElement('text', 'clientid', get_string('clientid', 'paygw_alipay'));
        $mform->setType('clientid', PARAM_TEXT);
        $mform->addHelpButton('clientid', 'clientid', 'paygw_alipay');

        $mform->addElement('hidden', 'moodleprivatekey');
        $mform->setType('moodleprivatekey', PARAM_TEXT);

        $mform->addElement('hidden', 'moodlepublickey');
        $mform->setType('moodlepublickey', PARAM_TEXT);

        if (empty($config['moodleprivatekey']) && empty($config['moodlepublickey'])) {
            $key = self::get_key();
            // Do we need to save this key to the db, so if the user refreshes we don't lose it?
            $mform->setDefault('moodleprivatekey', $key['private']);
            $mform->setDefault('moodlepublickey', $key['public']);
            $config['moodlepublickey'] = $key['public'];
        }
        $mform->addElement('static', 'publickeystring', get_string('moodlepublickey', 'paygw_alipay'), $config['moodlepublickey']);

        $mform->addElement('textarea', 'alipay', get_string('alipaypublickey', 'paygw_alipay'));
        $mform->setType('alipaypublickey', PARAM_TEXT);
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
                                                 \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled &&
                (empty($data->clientid) || empty($data->moodleprivatekey))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }

    /**
     * Create a new key.
     */
    private static function get_key() {
        $config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privatekey);
        $pubkey = openssl_pkey_get_details($res);

        return ['private' => $privatekey, 'public' => $pubkey['key']];
    }
}
