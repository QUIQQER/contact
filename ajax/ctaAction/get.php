<?php

use QUI\Contact\CtaAction\Control;

QUI::getAjax()->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_get',
    function ($attributes) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $allowed = Control::getAllowedAttributes();
            $attributes = json_decode($attributes, true);

            if (!empty($attributes) && is_array($attributes)) {
                $attributes = array_intersect_key($attributes, array_flip($allowed));
            } else {
                $attributes = [];
            }
            $control->setAttributes($attributes);
        }

        $html = QUI\Control\Manager::getCSS();
        $html .= $control->create();

        return $html;
    },
    ['attributes']
);
