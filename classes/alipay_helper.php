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
 * Contains helper class to work with Alipay.
 *
 * @package    paygw_alipay
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_alipay;
use core_payment\helper;
use moodle_url;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use paygw_paypal\paypal_helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/payment/gateway/alipay/_autoload.php');

/**
 * Class alipay_helper
 * @package paygw_alipay
 * @copyright  2021 Catalyst IT
 */
class alipay_helper {
    /**
     * @var integer Payment is pending
     */
    public const ORDER_STATUS_PENDING = 0;
    /**
     * @var integer Payment was received.
     */
    public const ORDER_STATUS_PAID = 1;

    /**
     * Get an unprocessed order record - if one already exists - return it.
     *
     * @param string $component
     * @param string $paymentarea
     * @param integer $itemid
     * @returns false|\stdClass
     */
    public static function get_unprocessed_order($component, $paymentarea, $itemid) {
        global $USER, $DB;

        $existingorder = $DB->get_record('paygw_alipay', ['component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'userid' => $USER->id,
            'status' => self::ORDER_STATUS_PENDING]);
        if ($existingorder) {
            return $existingorder;
        }
        return false;
    }

    /**
     * Create a new order.
     *
     * @param string $component
     * @param string $paymentarea
     * @param integer $itemid
     * @returns \stdClass
     */
    public static function create_order($component, $paymentarea, $itemid, $accountid)  {
        global $USER, $DB;

        // Create a new order record.
        $neworder = new \stdClass();
        $neworder->component = $component;
        $neworder->paymentarea = $paymentarea;
        $neworder->itemid = $itemid;
        $neworder->userid = $USER->id;
        $neworder->accountid = $accountid;
        $neworder->status = self::ORDER_STATUS_PENDING;
        $neworder->timecreated = time();
        $neworder->modified = $neworder->timecreated;

        $id = $DB->insert_record('paygw_alipay', $neworder);
        $neworder->id = $id;

        return $neworder;
    }

    /**
     * Get payment script to trigger QR Code display.
     *
     * @param \stdClass $config
     * @param \stdClass $order
     * @param string $description
     * @param int $cost
     * @return string
     */
    public static function get_payment_script($config, $order, $description, $cost) {
        // Moodle sets this to &nbsp; by default easysdk expects '&' see: MDL-71368.
        ini_set('arg_separator.output', '&');

        $processurl = new moodle_url('/payment/gateway/alipay/process.php');

        Factory::setOptions(alipay_helper::options($config));

        try {
            $result = Factory::payment()->page()->pay($description, $order->id, $cost, $processurl->out());
            $responsechecker = new ResponseChecker();

            if ($responsechecker->success($result)) {
                $resulttext = $result->body;
            } else {
                $resulttext = "Call failed, reason:". $result->msg."，".$result->subMsg.PHP_EOL;
            }
        } catch (Exception $e) {
            $resulttext = "Call failed, ". $e->getMessage(). PHP_EOL;;
        }

        return $resulttext;
    }

    /**
     * Check Alipay to see if this order has been paid.
     *
     * @param \Alipay\EasySDK\Kernel\Config $config
     * @param \stdClass $order
     * @throws \Exception
     * @returns boolean
     */
    public static function check_payment($config, $order) {
        // Moodle sets this to &nbsp; by default easysdk expects '&' see: MDL-71368.
        ini_set('arg_separator.output', '&');

        Factory::setOptions(alipay_helper::options($config));

        try {
            $result = Factory::payment()->common()->query($order->id);
            $responsechecker = new ResponseChecker();
            if ($responsechecker->success($result)) {
                if (!empty($result->tradeStatus) &&
                    ($result->tradeStatus === 'TRADE_SUCCESS' || $result->tradeStatus === 'TRADE_FINISHED')) {
                    return true;
                } else {
                    debugging("Call success, but invalid tradeStatus");
                }
            }
        } catch (Exception $e) {
            debugging("Call failed, " . $e->getMessage());
        }
        return false;
    }

    /**
     * Helper function to set normal alipay options.
     * @param $config
     * @return \Alipay\EasySDK\Kernel\Config
     */
    public static function options($config) {
        $options = new \Alipay\EasySDK\Kernel\Config();
        $options->protocol = 'https';
        $options->signType = 'RSA2';
        $options->appId = $config->clientid;
        $options->gatewayHost = $config->endpoint;
        $options->merchantPrivateKey = $config->merchantprivatekey;
        $options->alipayCertPath = $config->alipaycertpath;
        $options->alipayRootCertPath = $config->alipayrootcertpath;
        $options->merchantCertPath = $config->merchantcertpath;

        return $options;
    }

    public static function process_payment ($order) {
        global $DB;
        $payable = helper::get_payable($order->component, $order->paymentarea, $order->itemid);
        $cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), helper::get_gateway_surcharge('alipay'));
        $message = '';
        try {
            $paymentid = helper::save_payment($payable->get_account_id(), $order->component, $order->paymentarea,
                $order->itemid, (int) $order->userid, $cost, $payable->get_currency(), 'paypal');

            // Store Alipay extra information.
            $order->paymentid = $paymentid;
            $order->timemodified = time();
            $order->status = alipay_helper::ORDER_STATUS_PAID;

            $DB->update_record('paygw_alipay', $order);

            helper::deliver_order($order->component, $order->paymentarea, $order->itemid, $paymentid, (int) $order->userid);
            $success = true;
        } catch (\Exception $e) {
            debugging('Exception while trying to process payment: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $message = get_string('internalerror', 'paygw_alipay');
            $success = false;
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }
}