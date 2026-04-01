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
            btnStyle: 'button', // iconRounded, icon, button
            size: 'default',
            whatsapp: false,
            whatsappLabel: false,
            phone: false,
            phoneLabel: false,
            email: false,
            emailLabel: false,
            customButtons: '',

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
            this.setAttribute('size', this.getElm().getAttribute('data-qui-options-size'));
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

        addButton: function (buttonData) {
            const button = this.$normalizeButtonConfig(buttonData);
            const displayMode = this.$getDisplayMode();

            if (
                button.isDisabled ||
                (
                    (displayMode === 'icon-only' || displayMode === 'icon-only-rounded') &&
                    !button.icon
                )
            ) {
                return null;
            }

            const container = this.$getButtonsContainer();

            if (!container) {
                return null;
            }

            const element = this.$createButtonElement(button);
            container.appendChild(element);

            if (element.getAttribute('data-qui')) {
                QUI.parse(element);
            }

            return element;
        },

        $getButtonsContainer: function () {
            const btnStyle = this.$getBtnStyle();
            let buttons = this.getElm().querySelector('[data-name="buttons"]');

            if (!buttons) {
                const leftContent = this.getElm().querySelector('[data-name="left"]');

                if (!leftContent) {
                    return null;
                }

                buttons = document.createElement('div');
                buttons.setAttribute('data-name', 'buttons');
                buttons.classList.add('quiqqer-contact-ctaAction-buttons');
                leftContent.appendChild(buttons);
            }

            buttons.classList.remove(
                'quiqqer-contact-ctaAction-buttons--icon',
                'quiqqer-contact-ctaAction-buttons--iconRounded',
                'quiqqer-contact-ctaAction-buttons--button'
            );
            buttons.classList.add(`quiqqer-contact-ctaAction-buttons--${btnStyle}`);

            return buttons;
        },

        $createButtonElement: function (button) {
            const displayMode = this.$getDisplayMode();
            const isIconOnly = displayMode === 'icon-only' || displayMode === 'icon-only-rounded';
            const hasOpenBrick = button.openBrickId > 0;
            const tagName = hasOpenBrick || !button.href ? 'button' : 'a';
            const Button = document.createElement(tagName);
            const title = button.title || button.text || '';
            const ariaLabel = button.ariaLabel || button.text || '';

            Button.className = this.$getButtonClassName(button, displayMode);

            if (button.identifier) {
                Button.setAttribute('data-identifier', button.identifier);
            }

            if (title) {
                Button.setAttribute('title', title);
            }

            if (ariaLabel) {
                Button.setAttribute('aria-label', ariaLabel);
            }

            if (hasOpenBrick) {
                Button.setAttribute('type', 'button');
                Button.setAttribute(
                    'data-qui',
                    'package/quiqqer/components/bin/Controls/Button/OpenBrick'
                );
                Button.setAttribute('data-open-brick-id', String(button.openBrickId));

                if (button.openBrickWinWidth > 0) {
                    Button.setAttribute('data-win-width', String(button.openBrickWinWidth));
                }

                if (button.openBrickWinHeight > 0) {
                    Button.setAttribute('data-win-height', String(button.openBrickWinHeight));
                }
            } else if (tagName === 'a') {
                Button.setAttribute('href', button.href);

                if (button.targetBlank) {
                    Button.setAttribute('target', '_blank');
                    Button.setAttribute('rel', 'noopener noreferrer');
                }
            } else {
                Button.setAttribute('type', 'button');
            }

            if (button.disabled) {
                Button.setAttribute('aria-disabled', 'true');

                if (tagName === 'button' || hasOpenBrick) {
                    Button.setAttribute('disabled', 'disabled');
                } else {
                    Button.setAttribute('tabindex', '-1');
                }
            }

            if (button.onClick) {
                Button.setAttribute('onclick', button.onClick);
            }

            if (button.icon && button.iconPosition === 'start') {
                Button.appendChild(this.$createButtonIcon(button.icon));
            }

            if (!isIconOnly && button.text) {
                const TextNode = document.createElement('span');
                TextNode.className = 'btn__text';
                TextNode.textContent = button.text;
                Button.appendChild(TextNode);
            }

            if (button.icon && button.iconPosition === 'end') {
                Button.appendChild(this.$createButtonIcon(button.icon));
            }

            return Button;
        },

        $createButtonIcon: function (iconClass) {
            const Icon = document.createElement('span');
            Icon.className = `btn__icon ${iconClass}`.trim();
            Icon.setAttribute('aria-hidden', 'true');

            return Icon;
        },

        $getButtonClassName: function (button, displayMode) {
            const classNames = ['btn'];

            if (button.btnType) {
                classNames.push(`btn-${button.btnType}`);
            }

            if (button.size === 'sm') {
                classNames.push('btn-sm');
            }

            if (button.size === 'lg') {
                classNames.push('btn-lg');
            }

            if (button.customClass) {
                classNames.push(button.customClass);
            }

            if (displayMode === 'icon-only' || displayMode === 'icon-only-rounded') {
                classNames.push('btn-icon');
            }

            if (displayMode === 'icon-only-rounded') {
                classNames.push('btn-rounded');
            }

            if (button.fullWidth) {
                classNames.push('btn-full');
            }

            if (button.disabled) {
                classNames.push('disabled');
            }

            return classNames.join(' ');
        },

        $normalizeButtonConfig: function (buttonData) {
            buttonData = typeOf(buttonData) === 'object' ? buttonData : {};

            const text = (buttonData.text || '').toString().trim();
            const title = (buttonData.title || text).toString().trim();
            const ariaLabel = (buttonData.ariaLabel || text).toString().trim();

            return {
                text: text,
                identifier: (buttonData.identifier || '').toString().replace(/[^A-Za-z0-9_-]/g, ''),
                icon: this.$sanitizeClassList(
                    (buttonData.icon || buttonData.iconClass || '').toString()
                ),
                iconPosition: buttonData.iconPosition === 'end' ? 'end' : 'start',
                btnType: this.$normalizeButtonType(buttonData.btnType),
                size: this.$normalizeButtonSize(buttonData.size || this.getAttribute('size')),
                openBrickId: this.$normalizeDimension(buttonData.openBrickId),
                openBrickWinWidth: this.$normalizeDimension(buttonData.openBrickWinWidth),
                openBrickWinHeight: this.$normalizeDimension(buttonData.openBrickWinHeight),
                href: this.$normalizeHref((buttonData.href || '#').toString()),
                targetBlank: this.$normalizeFlag(buttonData.targetBlank),
                title: title,
                ariaLabel: ariaLabel,
                disabled: this.$normalizeFlag(buttonData.disabled),
                fullWidth: this.$normalizeFlag(buttonData.fullWidth),
                onClick: this.$normalizeOnClick((buttonData.onClick || '').toString()),
                customClass: this.$sanitizeClassList(
                    (buttonData.customClass || buttonData.cssClass || '').toString()
                ),
                isDisabled: this.$normalizeFlag(buttonData.isDisabled)
            };
        },

        $getBtnStyle: function () {
            const rawBtnStyle = this.getAttribute('btnStyle');

            if (rawBtnStyle === 'icon' || rawBtnStyle === 'button') {
                return rawBtnStyle;
            }

            return 'iconRounded';
        },

        $getDisplayMode: function () {
            const btnStyle = this.$getBtnStyle();

            if (btnStyle === 'icon') {
                return 'icon-only';
            }

            if (btnStyle === 'iconRounded') {
                return 'icon-only-rounded';
            }

            return 'button';
        },

        $sanitizeClassList: function (classList) {
            return classList.split(/\s+/)
                .map((className) => className.trim())
                .filter((className) => /^[A-Za-z0-9_-]+$/.test(className))
                .join(' ');
        },

        $normalizeButtonType: function (btnType) {
            const allowed = [
                '',
                'primary',
                'primary-outline',
                'secondary',
                'secondary-outline',
                'success',
                'success-outline',
                'danger',
                'danger-outline',
                'warning',
                'warning-outline',
                'info',
                'info-outline',
                'light',
                'light-outline',
                'dark',
                'dark-outline',
                'white',
                'white-outline',
                'link',
                'link-body'
            ];

            btnType = (btnType || '').toString().trim();

            if (allowed.contains(btnType)) {
                return btnType;
            }

            return 'primary';
        },

        $normalizeButtonSize: function (size) {
            size = (size || '').toString().trim();

            if (size === 'sm' || size === 'lg' || size === 'default') {
                return size;
            }

            return '';
        },

        $normalizeDimension: function (value) {
            value = parseInt(value, 10);

            if (!isNaN(value) && value > 0) {
                return value;
            }

            return 0;
        },

        $normalizeFlag: function (value) {
            return value === true || value === 1 || value === '1';
        },

        $normalizeHref: function (href) {
            href = href.trim();

            if (!href) {
                return '#';
            }

            if (/^\s*(javascript|data):/i.test(href)) {
                return '#';
            }

            return href;
        },

        $normalizeOnClick: function (onClick) {
            onClick = onClick.trim();

            if (!onClick) {
                return '';
            }

            if (onClick.endsWith(';')) {
                onClick = onClick.slice(0, -1).trim();
            }

            if (/^[A-Za-z_$][A-Za-z0-9_$.]*$/.test(onClick)) {
                return `${onClick}();`;
            }

            const matches = onClick.match(/^([A-Za-z_$][A-Za-z0-9_$.]*)\((.*)\)$/s);

            if (!matches) {
                return '';
            }

            const args = matches[2].trim();

            if (/[;<>`]/.test(args)) {
                return '';
            }

            return `${matches[1]}(${args});`;
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
                size: this.getAttribute('size'),
                whatsapp: this.getAttribute('whatsapp'),
                whatsappLabel: this.getAttribute('whatsappLabel'),
                phone: this.getAttribute('phone'),
                phoneLabel: this.getAttribute('phoneLabel'),
                email: this.getAttribute('email'),
                emailLabel: this.getAttribute('emailLabel'),
                customButtons: this.getAttribute('customButtons'),

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
