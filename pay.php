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

require_once(__DIR__ . '/../../../config.php');

require_login();
$component = required_param('component', PARAM_ALPHANUMEXT);
$paymentarea = required_param('paymentarea', PARAM_ALPHANUMEXT);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

$response = paygw_alipay\external\get_form::execute($component, $paymentarea, $itemid, $description);
if (!empty($response['warning'])) {
    $successurl = new moodle_url('/');
    redirect ($successurl, $response['warning']);
}
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');
$PAGE->set_url('/');
// Print page HTML.
echo $OUTPUT->header();
$icon = new pix_icon('i/loading', 'loading', 'moodle', array('class' => 'loadingicon'));
echo html_writer::tag('span', $OUTPUT->render($icon), array('class' => 'loadingicon'));
echo $response['alipayform'];
echo $OUTPUT->footer();