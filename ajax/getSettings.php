<?php

/**
 * Delete contact requests
 *
 * @param array $requestIds
 * @return bool - success
 */

QUI::getAjax()->registerFunction(
    'package_quiqqer_contact_ajax_getSettings',
    function () {
        return QUI::getPackage('quiqqer/contact')->getConfig()?->toArray();
    },
    [],
    'Permission::checkAdminUser'
);
