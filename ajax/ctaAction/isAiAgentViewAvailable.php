<?php

use QUI\Contact\CtaAction\Control;

/**
 * Whether the AI agent view is available for the CTA action brick.
 *
 * Used by the brick editor to decide whether the "Auswahl" and "KI Agent"
 * options of the "Ansicht beim Öffnen" setting may be offered.
 *
 * @todo TEMPORARY SOLUTION. This only reports whether the hardcoded
 *       pcsg/sales-agent integration is available (see Control). Replace with a
 *       general AI-agent-view provider lookup later.
 *
 * @return bool
 */

QUI::getAjax()->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_isAiAgentViewAvailable',
    function () {
        return Control::isAiAgentViewAvailable();
    },
    false,
    'Permission::checkAdminUser'
);
