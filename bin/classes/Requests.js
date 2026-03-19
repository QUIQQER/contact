/**
 * Contact Requests Handler
 */
define('package/quiqqer/contact/bin/classes/Requests', [

    'Ajax'

], function (QUIAjax) {
    "use strict";

    const pkg = 'quiqqer/contact';

    return new Class({

        Type: 'package/quiqqer/contact/bin/classes/Requests',

        /**
         * Get a list of all Requests
         *
         * @param {Object} SearchParams
         * @return {Promise}
         */
        getList: function (SearchParams) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_contact_ajax_requests_getList', resolve, {
                    'package': pkg,
                    searchParams: JSON.encode(SearchParams),
                    onError: reject
                });
            });
        },

        /**
         * Get a list of all request forms
         *
         * @return {Promise}
         */
        getForms: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_contact_ajax_requests_getForms', resolve, {
                    'package': pkg,
                    onError: reject
                });
            });
        },

        /**
         * Delete contact requests
         *
         * @param {Array} requestIds - The request IDs that are to be deleted
         * @return {Promise}
         */
        deleteRequests: function (requestIds) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_contact_ajax_requests_deleteRequests', resolve, {
                    'package': pkg,
                    requestIds: JSON.encode(requestIds),
                    onError: reject
                });
            });
        }
    });
});
