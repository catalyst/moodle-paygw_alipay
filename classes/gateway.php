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
    /**
     * Currencies supported by this plugin.
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        // 3-character ISO-4217: https://en.wikipedia.org/wiki/ISO_4217#Active_codes.
        return [
            'CNY'
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

        $mform->addElement('text', 'endpoint', get_string('endpoint', 'paygw_alipay'));
        $mform->setType('endpoint', PARAM_URL);
        $mform->addHelpButton('endpoint', 'endpoint', 'paygw_alipay');

        $mform->addElement('text', 'clientid', get_string('clientid', 'paygw_alipay'));
        $mform->setType('clientid', PARAM_TEXT);
        $mform->addHelpButton('clientid', 'clientid', 'paygw_alipay');

        $mform->addElement('textarea', 'merchantprivatekey', get_string('merchantprivatekey', 'paygw_alipay'));
        $mform->setType('merchantprivatekey', PARAM_TEXT);
        $mform->addHelpButton('merchantprivatekey', 'merchantprivatekey', 'paygw_alipay');

        $mform->addElement('text', 'alipaycertpath', get_string('alipaycertpath', 'paygw_alipay'));
        $mform->setType('alipaycertpath', PARAM_TEXT);
        $mform->addHelpButton('alipaycertpath', 'alipaycertpath', 'paygw_alipay');

        $mform->addElement('text', 'alipayrootcertpath', get_string('alipayrootcertpath', 'paygw_alipay'));
        $mform->setType('alipayrootcertpath', PARAM_TEXT);
        $mform->addHelpButton('alipayrootcertpath', 'alipayrootcertpath', 'paygw_alipay');

        $mform->addElement('text', 'merchantcertpath', get_string('merchantcertpath', 'paygw_alipay'));
        $mform->setType('merchantcertpath', PARAM_TEXT);
        $mform->addHelpButton('merchantcertpath', 'merchantcertpath', 'paygw_alipay');
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
                (empty($data->clientid) || empty($data->merchantprivatekey))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }
}
