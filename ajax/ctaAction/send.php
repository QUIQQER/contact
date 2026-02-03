<?php

use QUI\Contact\CtaAction\Control;

QUI::$Ajax->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_send',
    function ($attributes, $formData) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $allowed = Control::getAllowedAttributes();
            $attributes = json_decode($attributes, true);

            if (!empty($attributes) && is_array($attributes)) {
                $attributes = array_intersect_key($attributes, array_flip($allowed));
            } else {
                $attributes = [];
            }

            foreach ($attributes as $key => $value) {
                if (is_string($value)) {
                    $attributes[$key] = trim(strip_tags($value));
                }
            }

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
