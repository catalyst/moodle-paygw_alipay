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
 * Listens for Payment Notification from Alipay
 *
 * This script waits for Payment notification from Alipay,
 * then double checks that data by sending it back to Alipay.
 * If Alipay verifies this then it flags the payment as paid.
 *
 * @package    paygw_alipay
 * @copyright 2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;
use paygw_alipay\alipay_helper;

require_once(__DIR__ . '/../../../config.php');

// Get Moodle order id from Alipay response.
$orderid = required_param('out_trade_no', PARAM_INT);

require_login(null, false);

$order = $DB->get_record('paygw_alipay', ['id' => $orderid], '*', MUST_EXIST);

$homepageurl = new moodle_url('/');

// Sanity check the userid matches and that we have an open order.
if ($order->userid <> $USER->id) {
    redirect($homepageurl, get_string("invaliduser", "paygw_alipay"), "0", 'warning');
}
$successurl = helper::get_success_url($order->component, $order->paymentarea, $order->itemid);
if ((int) $order->status === alipay_helper::ORDER_STATUS_PAID) {
    redirect($successurl, get_string("orderalreadycomplete", "paygw_alipay"));
}
$config = (object)helper::get_gateway_configuration($order->component, $order->paymentarea, $order->itemid, 'alipay');

$paymentsuccess = alipay_helper::check_payment($config, $order);
if ($paymentsuccess) {
    alipay_helper::process_payment($order);
    redirect($successurl, get_string('paymentsuccessful', 'paygw_alipay'), "0", 'success');
} else {
    redirect($homepageurl, get_string("paymentverificationfailed", "paygw_alipay"));
}
