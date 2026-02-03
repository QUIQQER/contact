<?php

use QUI\Contact\CtaAction\Control;

QUI::$Ajax->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_send',
    function ($attributes, $formData) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $attributes = json_decode($attributes, true);
            $control->setAttributes($attributes);
        }

        if (is_string($formData)) {
            $formData = json_decode($formData, true);
        }

        if (!is_array($formData)) {
            throw new \Exception('Invalid form data');
        }

        $control->send($formData);
    },
    ['attributes', 'formData']
);
