<?php

use QUI\Contact\CtaAction\Control;

QUI::$Ajax->registerFunction(
    'package_quiqqer_contact_ajax_ctaAction_get',
    function ($attributes) {
        $control = new Control();

        if (!empty($attributes) && is_string($attributes)) {
            $allowed = [
                'header',
                'content',
                'title',
                'description',
                'name_label',
                'name_placeholder',
                'company_label',
                'company_placeholder',
                'email_label',
                'email_placeholder',
                'phone_label',
                'phone_placeholder',
                'message_label',
                'message_placeholder',
                'submit_label',
                'success_message',
                'whatsapp',
                'phone',
                'email'
            ];

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
