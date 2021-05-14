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

import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import * as Repository from './repository';

/**
 * This module is responsible for alipay content in the gateways modal.
 *
 * @module     paygw_alipay/gateway_modal
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
const showModalWithPlaceholder = async() => {
    const modal = await ModalFactory.create({
        body: await Templates.render('paygw_alipay/alipay_button_placeholder', {})
    });
    modal.show();
    return modal;
};

/**
 * Process the payment.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} description Description of the payment
 * @returns {Promise<string>}
 */
export const process = (component, paymentArea, itemId, description) => {
    return showModalWithPlaceholder()
        .then(modal => {
            return Repository.createPaymentUrl(component, paymentArea, itemId, description)
                .then(url => {
                    location.href = url;
                    // Return a promise that is never going to be resolved.
                    return new Promise(() => null);
                }).catch(e => {
                    modal.hide();
                    // We want to use promise reject here - as that's what core payment stuff expects disable eslint.
                    /* eslint-disable */
                    return Promise.reject(e.message);
                });
        });
};
