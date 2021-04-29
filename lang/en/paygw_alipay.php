<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     paygw_alipay
 * @category    string
 * @copyright   2021 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Alipay ';
$string['pluginname_desc'] = 'The Alipay plugin allows you to receive payments via Alipay.';
$string['privacy:metadata'] = 'The Alipay plugin does not store any personal data.';
$string['clientid'] = 'Client ID';
$string['clientid_help'] = 'The client ID that Alipay generated for your application.';
$string['endpoint'] = 'Gateway endpoint';
$string['endpoint_help'] = 'The url of the Alipay Gateway';
$string['merchantprivatekey'] = 'Merchant private key';
$string['merchantprivatekey_help'] = 'Your private key used for accessing Alipay';
$string['alipaypublickey'] = 'Alipay public key';
$string['gatewaydescription'] = 'Alipay is an authorised payment gateway provider.';
$string['gatewayname'] = 'Alipay';
$string['alipaycertpath'] = 'Path to the Alipay cert';
$string['alipaycertpath_help'] = '';
$string['alipayrootcertpath'] = 'Path to the Alipay root cert';
$string['alipayrootcertpath_help'] = '';
$string['merchantcertpath'] = 'Path to your local cert';
$string['merchantcertpath_help'] = '';
$string['invaliduser'] = 'An order id was passed that does not exist or does not relate to the logged in user';
$string['paymentverificationfailed'] = 'We could not verify this order was paid.';
$string['internalerror'] = 'An internal error has occurred.';
$string['paymentsuccessful'] = 'Your payment has been successfully processed';
$string['orderalreadycomplete'] = 'This order has already been processed';
$string['paymentalreadyprocessed'] = 'A payment has already been processed - please refresh your page';
$string['privacy:metadata:paygw_alipay'] = 'Stores information about alipay payments';
$string['privacy:metadata:paygw_alipay:timecreated'] = 'The time when the order was initiated.';
$string['privacy:metadata:paygw_alipay:timemodified'] = 'The time when the order record was last updated.';
$string['privacy:metadata:paygw_alipay:userid'] = 'The user who made the order.';
$string['privacy:metadata:paygw_alipay:status'] = 'The status of the order.';