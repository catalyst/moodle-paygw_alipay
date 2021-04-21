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
 * This class contains a list of webservice functions related to the Alipay payment gateway.
 *
 * @package    paygw_alipay
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_alipay\external;

use core_payment\helper;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/payment/gateway/alipay/_autoload.php');
class get_form extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
            'description' => new external_value(PARAM_TEXT, 'The description of the payment'),
        ]);
    }

    /**
     * Returns the Alipay form.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return string[]
     */
    public static function execute(string $component, string $paymentarea, int $itemid, string $description): array {
        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $description
        ]);

        $config = (object)helper::get_gateway_configuration($component, $paymentarea, $itemid, 'alipay');
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $surcharge = helper::get_gateway_surcharge('alipay');

        // Moodle sets this to &nbsp; by default easysdk expects '&' see: MDL-71368.
        ini_set('arg_separator.output', '&');

        $options = new \Alipay\EasySDK\Kernel\Config();
        $options->protocol = 'https';
        $options->signType = 'RSA2';
        $options->appId = $config->clientid;
        $options->gatewayHost = $config->endpoint;
        $options->merchantPrivateKey = $config->merchantprivatekey;
        $options->alipayCertPath = $config->alipaycertpath;
        $options->alipayRootCertPath = $config->alipayrootcertpath;
        $options->merchantCertPath = $config->merchantcertpath;

        Factory::setOptions($options);
        $cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);
        // TODO: Generate Tradenumber.

        try {
            $result = Factory::payment()->page()->pay($description, "2234567890", $cost, "/");
            $responseChecker = new ResponseChecker();

            if ($responseChecker->success($result)) {
                $resulttext = $result->body;
            } else {
                $resulttext = "Call failed, reason:". $result->msg."，".$result->subMsg.PHP_EOL;
            }
        } catch (Exception $e) {
            $resulttext = "Call failed, ". $e->getMessage(). PHP_EOL;;
        }

        return [
            'alipayform' => $resulttext
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'alipayform' => new external_value(PARAM_RAW, 'Alipay form for payment or error.'),
        ]);
    }
}
