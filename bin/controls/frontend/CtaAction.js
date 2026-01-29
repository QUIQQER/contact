define('package/quiqqer/contact/bin/controls/frontend/CtaAction', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',

], function (QUI, QUIControl, QUIAjax) {
    "use strict";

    return new Class({

        Type: 'package/quiqqer/contact/bin/controls/frontend/CtaAction',
        Extends: QUIControl,

        options: {
            header: '',
            content: '',
            title: '',
            description: '',

            name_label: '',
            name_placeholder: '',
            company_label: '',
            company_placeholder: '',
            email_label: '',
            email_placeholder: '',
            phone_label: '',
            phone_placeholder: '',
            message_label: '',
            message_placeholder: '',
            submit_label: '',

            // buttons
            whatsapp: '',
            phone: '',
            email: ''
        },

        initialize: function (options) {
            this.parent(options);
        },

        create: function () {
            const container = this.parent();

            container.classList.add('cta-action');
            container.innerHTML = '';

            QUIAjax.get('package_quiqqer_contact_ajax_ctaAction_get', (html) => {
                container.innerHTML = html;

                QUI.parse(container).then(() => {
                    const form = container.querySelector('form');

                    form.addEventListener('submit', (e) => {
                        e.preventDefault();

                        this.send().catch(() => {
                            this.fireEvent('sendError', [this]);
                        });
                    });

                    this.fireEvent('load', [this]);
                });
            }, {
                'package': 'quiqqer/contact',
                attributes: JSON.stringify({
                    header: this.getAttribute('header'),
                    content: this.getAttribute('content'),
                    title: this.getAttribute('title'),
                    description: this.getAttribute('description'),

                    name_label: this.getAttribute('name_label'),
                    name_placeholder: this.getAttribute('name_placeholder'),
                    company_label: this.getAttribute('company_label'),
                    company_placeholder: this.getAttribute('company_placeholder'),
                    email_label: this.getAttribute('email_label'),
                    email_placeholder: this.getAttribute('email_placeholder'),
                    phone_label: this.getAttribute('phone_label'),
                    phone_placeholder: this.getAttribute('phone_placeholder'),
                    message_label: this.getAttribute('message_label'),
                    message_placeholder: this.getAttribute('message_placeholder'),
                    submit_label: this.getAttribute('submit_label'),

                    // buttons
                    whatsapp: this.getAttribute('whatsapp'),
                    phone: this.getAttribute('phone'),
                    email: this.getAttribute('email'),
                })
            });

            return container;
        },

        send: function () {
            this.fireEvent('sendBegin');

            return new Promise(() => {
                QUIAjax.post('package_quiqqer_contact_ajax_ctaAction_send', () => {

                }, {
                    'package': 'quiqqer/contact',
                    onError: () => {
                        this.fireEvent('sendError', [this])
                    }
                });
            });
        }
    });
});
