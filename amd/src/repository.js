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
 * PayPal repository module to encapsulate all of the AJAX requests that can be sent for Alipay.
 *
 * @module     paygw_alipay/repository
 * @package    paygw_alipay
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return the payment url.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} description The description of the payment.
 * @returns {string} payment url.
 */
export const createPaymentUrl = async(component, paymentArea, itemId, description) => {
    const url = M.cfg.wwwroot + '/payment/gateway/alipay/pay.php?' +
        'component=' + component +
        '&paymentarea=' + paymentArea +
        '&itemid=' + itemId +
        '&description=' + description;
    return url;
};