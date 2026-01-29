<?php

use QUI\Contact\CtaAction\Control;

QUI::$Ajax->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_send',
    function ($attributes) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $attributes = json_decode($attributes, true);
            $control->setAttributes($attributes);
        }

        $control->send();
    },
    ['attributes']
);
