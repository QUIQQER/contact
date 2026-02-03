define('package/quiqqer/contact/bin/controls/frontend/CtaAction', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'Locale'

], function (QUI, QUIControl, QUIAjax, QUILocale) {
    "use strict";

    const lg = 'quiqqer/contact';

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
            success_message: '',

            // buttons
            whatsapp: false,
            phone: false,
            email: false
        },

        initialize: function (options) {
            this.parent(options);
        },

        create: function () {
            const container = this.parent();

            container.classList.add('cta-action');
            container.innerHTML = '';
            container.style.width = '100%';

            this.$Elm = container;

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
                'package': lg,
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
                let message = this.getAttribute('success_message');

                if (!message || message.length === 0) {
                    message = QUILocale.get(lg, 'contact.ctaAction.send.success');
                }

                QUIAjax.post('package_quiqqer_contact_ajax_ctaAction_send', () => {
                    // finish mess
                    const success = document.createElement('div');
                    success.classList.add('quiqqer-contact-ctaAction-success');

                    const icon = document.createElement('span');
                    icon.classList.add('fa', 'fa-check');

                    const text = document.createElement('div');
                    text.textContent = message;

                    success.appendChild(icon);
                    success.appendChild(text);

                    const container = this.getElm().querySelector('.quiqqer-contact-ctaAction')
                    const nodes = Array.from(
                        this.getElm().querySelectorAll(
                            '[data-name="left"],[data-name="right"]'
                        )
                    );

                    moofx(nodes).animate({
                        opacity: 0
                    }, {
                        callback: () => {
                            container.innerHTML = '';
                            container.classList.add('quiqqer-contact-ctaAction--success');
                            container.appendChild(success);

                            this.fireEvent('sendEnd', [this])
                            this.fireEvent('send', [this])
                        }
                    });
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
