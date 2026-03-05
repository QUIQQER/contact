define('package/quiqqer/contact/bin/controls/frontend/CtaAction', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'Ajax',
    'Locale'

], function (QUI, QUIControl, QUILoader, QUIAjax, QUILocale) {
    "use strict";

    const lg = 'quiqqer/contact';

    return new Class({

        Type: 'package/quiqqer/contact/bin/controls/frontend/CtaAction',
        Extends: QUIControl,

        options: {
            'data-brickid': '',
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
            btnStyle: 'iconRounded', // iconRounded, icon, button
            whatsapp: false,
            whatsappLabel: false,
            phone: false,
            phoneLabel: false,
            email: false,
            emailLabel: false,

            // design
            formDesign: 'default', // default, grid, labelLeft
            bgColor: '',
            color: '',
            leftBgColor: '',
            leftColor: '',
        },

        initialize: function (options) {
            this.parent(options);

            this.Loader = new QUILoader();

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            this.getElm().style.width = '100%';
            this.setAttribute('btnStyle', this.getElm().getAttribute('data-qui-options-btnstyle'));
            this.Loader.inject(this.getElm());
            this.$initEvents();
        },

        create: function () {
            const container = this.parent();

            container.innerHTML = '';
            container.style.width = '100%';

            this.$Elm = container;

            QUIAjax.get('package_quiqqer_contact_ajax_ctaAction_get', (html) => {
                container.innerHTML = html;
                this.Loader.inject(this.getElm());

                QUI.parse(container).then(() => {
                    this.fireEvent('load', [this]);
                });
            }, {
                'package': lg,
                attributes: JSON.stringify(this.getControlAttributes())
            });

            return container;
        },

        $initEvents: function () {
            const form = this.getElm().querySelector('form');

            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    this.send().catch(() => {
                        // nothing, error event is triggered in send
                    });
                });
            }

            const privacyLabel = this.getElm().querySelector('[data-name="privacy"]');

            if (privacyLabel) {
                const aNode = privacyLabel.querySelector('a');

                if (aNode) {
                    aNode.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        require(['package/quiqqer/controls/bin/site/Window'], function (Win) {
                            new Win({
                                showTitle: true,
                                project: aNode.dataset.project,
                                lang: aNode.dataset.lang,
                                id: aNode.dataset.id
                            }).open();
                        });
                    });
                }
            }

            this.fireEvent('load', [this]);
        },

        send: function () {
            this.fireEvent('sendBegin');
            this.Loader.show();

            const form = this.getElm().querySelector('form') || document.createElement('form');
            const formData = new FormData(form);

            return new Promise((resolve, reject) => {
                this.getSuccessHtml().then(/** @param {string} successHtml */(successHtml) => {
                    QUIAjax.post('package_quiqqer_contact_ajax_ctaAction_send', () => {
                        // finish mess
                        const success = document.createElement('div');
                        success.classList.add('quiqqer-contact-ctaAction__success');

                        success.innerHTML = successHtml;

                        const nodes = Array.from(
                            this.getElm().querySelectorAll(
                                '[data-name="left"],[data-name="right"]'
                            )
                        );

                        moofx(nodes).animate({
                            opacity: 0
                        }, {
                            callback: () => {
                                nodes.forEach((node) => {
                                    node.parentNode.removeChild(node);
                                });

                                this.getElm().classList.add('quiqqer-contact-ctaAction--success');
                                this.getElm().appendChild(success);

                                this.fireEvent('sendEnd', [this]);
                                this.fireEvent('send', [this]);
                                this.Loader.hide();
                                resolve();
                            }
                        });
                    }, {
                        'package': 'quiqqer/contact',
                        attributes: JSON.stringify(this.getControlAttributes()),
                        formData: JSON.stringify(Object.fromEntries(formData.entries())),
                        onError: () => {
                            this.fireEvent('sendError', [this]);
                            this.Loader.hide();
                            reject();
                        }
                    });
                });
            });
        },

        addButton: function (text, icon, cssClass, href) {
            const buttons = this.getElm().querySelector('[data-name="buttons"]');

            if (!buttons) {
                return;
            }

            const rawBtnStyle = this.getAttribute('btnStyle');
            const btnStyle = rawBtnStyle === 'icon' || rawBtnStyle === 'button'
                ? rawBtnStyle
                : 'iconRounded';

            buttons.classList.remove(
                'quiqqer-contact-ctaAction-buttons--icon',
                'quiqqer-contact-ctaAction-buttons--iconRounded',
                'quiqqer-contact-ctaAction-buttons--button'
            );
            buttons.classList.add(`quiqqer-contact-ctaAction-buttons--${btnStyle}`);

            const button = document.createElement('a');
            button.classList.add('btn');
            button.setAttribute('href', href || '#');

            if (cssClass) {
                cssClass.split(' ')
                    .map((className) => className.trim())
                    .filter((className) => className.length)
                    .forEach((className) => {
                        button.classList.add(className);
                    });
            }

            const showText = btnStyle === 'button';

            if (icon) {
                const iconNode = document.createElement('span');
                iconNode.classList.add('btn-icon');

                icon.split(' ')
                    .map((className) => className.trim())
                    .filter((className) => className.length)
                    .forEach((className) => {
                        iconNode.classList.add(className);
                    });

                button.appendChild(iconNode);
            }

            if (showText && text) {
                if (icon) {
                    button.appendChild(document.createTextNode(' '));
                }

                button.appendChild(document.createTextNode(text));
            }

            if (!showText && text) {
                button.setAttribute('title', text);
                button.setAttribute('aria-label', text);
            }

            buttons.appendChild(button);

            return button;
        },

        getControlAttributes: function () {
            let brickId = false;

            if (this.getElm().getAttribute('data-brickid')) {
                brickId = this.getElm().getAttribute('data-brickid');
            }

            if (!brickId && this.getAttribute('data-brickid')) {
                brickId = this.getAttribute('data-brickid');
            }

            return {
                'data-brickid': brickId,
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
                btnStyle: this.getAttribute('btnStyle'),
                whatsapp: this.getAttribute('whatsapp'),
                whatsappLabel: this.getAttribute('whatsappLabel'),
                phone: this.getAttribute('phone'),
                phoneLabel: this.getAttribute('phoneLabel'),
                email: this.getAttribute('email'),
                emailLabel: this.getAttribute('emailLabel'),

                // design
                formDesign: this.getAttribute('formDesign'),
                bgColor: this.getAttribute('bgColor'),
                color: this.getAttribute('color'),
                leftBgColor: this.getAttribute('leftBgColor'),
                leftColor: this.getAttribute('leftColor'),
            };
        },

        /**
         * @return {Promise<string>}
         */
        getSuccessHtml: function () {
            let message = this.getAttribute('success_message');

            if (!message || message.length === 0) {
                message = QUILocale.get(lg, 'contact.ctaAction.send.success');
            }

            return new Promise((resolve) => {
                require([
                    'Mustache',
                    'text!package/quiqqer/contact/bin/controls/frontend/CtaAction.Success.html',
                    'css!package/quiqqer/contact/bin/controls/frontend/CtaAction.Success.css',
                ], function (Mustache, template) {
                    const html = Mustache.render(template, {
                        message: message
                    });

                    resolve(html);
                });
            });
        }
    });
});
