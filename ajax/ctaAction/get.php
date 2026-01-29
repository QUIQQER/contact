<?php

use QUI\Contact\CtaAction\Control;

QUI::$Ajax->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_get',
    function ($attributes) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $attributes = json_decode($attributes, true);
            $control->setAttributes($attributes);
        }

        $html = QUI\Control\Manager::getCSS();
        $html .= $control->create();

        return $html;
    },
    ['attributes']
);
