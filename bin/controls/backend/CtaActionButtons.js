define('package/quiqqer/contact/bin/controls/backend/CtaActionButtons', [
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'Locale',
    'qui/utils/Form',
    'css!package/quiqqer/contact/bin/controls/backend/CtaActionButtons.css'
], function (QUIControl, QUIButton, QUIConfirm, QUILocale, QUIFormUtils) {
    'use strict';

    const lg = 'quiqqer/contact';

    return new Class({
        Extends: QUIControl,
        Type: 'package/quiqqer/contact/bin/controls/backend/CtaActionButtons',

        Binds: [
            '$onImport',
            '$onInject',
            '$openAddWindow',
            '$refreshData'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input = null;
            this.$Container = null;
            this.$List = null;
            this.$Message = null;
            this.$Buttons = [];

            this.addEvents({
                onImport: this.$onImport,
                onInject: this.$onInject
            });
        },

        create: function () {
            this.$Elm = new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons',
                html: '<div class="quiqqer-contact-ctaActionButtons-list"></div>' +
                    '<div class="quiqqer-contact-ctaActionButtons-empty"></div>' +
                    '<div class="quiqqer-contact-ctaActionButtons-actions"></div>'
            });

            this.$List = this.$Elm.getElement('.quiqqer-contact-ctaActionButtons-list');
            this.$Message = this.$Elm.getElement('.quiqqer-contact-ctaActionButtons-empty');
            this.$Container = this.$Elm.getElement('.quiqqer-contact-ctaActionButtons-actions');

            this.$Message.set('text', QUILocale.get(
                lg,
                'brick.control.ctaAction.setting.customButtons.empty'
            ));

            new QUIButton({
                text: QUILocale.get(lg, 'brick.control.ctaAction.setting.customButtons.btn.add'),
                textimage: 'fa fa-plus',
                events: {
                    onClick: this.$openAddWindow
                }
            }).inject(this.$Container);

            return this.$Elm;
        },

        $onImport: function () {
            if (this.$Elm.nodeName === 'INPUT') {
                this.$Input = this.$Elm;
                this.create().wraps(this.$Input);
            } else {
                this.inject(this.$Elm);
            }

            this.$Input.type = 'hidden';
            this.$loadFromInput();
            this.$refreshList();
        },

        $onInject: function () {
            if (!this.$Input) {
                this.$Input = new Element('input', {
                    type: 'hidden'
                }).inject(this.$Elm);
            }
        },

        $loadFromInput: function () {
            this.$Buttons = [];

            if (!this.$Input.value) {
                return;
            }

            let data = [];

            try {
                data = JSON.decode(this.$Input.value);
            } catch (e) {
                data = [];
            }

            if (typeOf(data) !== 'array') {
                return;
            }

            data.forEach((entry) => {
                if (typeOf(entry) !== 'object') {
                    return;
                }

                this.$Buttons.push({
                    text: String(entry.text || ''),
                    icon: String(entry.icon || ''),
                    cssClass: String(entry.cssClass || ''),
                    href: String(entry.href || '#')
                });
            });
        },

        $refreshData: function () {
            if (!this.$Input) {
                return;
            }

            this.$Input.value = JSON.encode(this.$Buttons);
            this.$refreshList();
        },

        $refreshList: function () {
            this.$List.set('html', '');

            if (!this.$Buttons.length) {
                this.$Message.setStyle('display', null);
                return;
            }

            this.$Message.setStyle('display', 'none');

            this.$Buttons.forEach((buttonData, index) => {
                this.$createEntry(buttonData, index);
            });
        },

        $createEntry: function (buttonData, index) {
            const entry = new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons-entry'
            }).inject(this.$List);

            const details = new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons-entry-details'
            }).inject(entry);

            new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons-entry-title',
                text: buttonData.text || '-'
            }).inject(details);

            new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons-entry-meta',
                text: buttonData.href || '#'
            }).inject(details);

            const actions = new Element('div', {
                'class': 'quiqqer-contact-ctaActionButtons-entry-actions'
            }).inject(entry);

            new QUIButton({
                icon: 'fa fa-pencil',
                title: QUILocale.get(
                    lg,
                    'brick.control.ctaAction.setting.customButtons.btn.edit.title'
                ),
                events: {
                    onClick: () => {
                        this.$openEditWindow(index);
                    }
                }
            }).inject(actions);

            new QUIButton({
                icon: 'fa fa-trash',
                title: QUILocale.get(
                    lg,
                    'brick.control.ctaAction.setting.customButtons.btn.delete.title'
                ),
                events: {
                    onClick: () => {
                        this.$removeEntry(index);
                    }
                }
            }).inject(actions);
        },

        $openAddWindow: function () {
            this.$openEntryWindow({
                text: '',
                icon: '',
                cssClass: '',
                href: '#'
            }, (data) => {
                this.$Buttons.push(data);
                this.$refreshData();
            }, QUILocale.get(
                lg,
                'brick.control.ctaAction.setting.customButtons.window.add.title'
            ), 'fa fa-plus');
        },

        $openEditWindow: function (index) {
            const currentData = this.$Buttons[index];

            if (!currentData) {
                return;
            }

            this.$openEntryWindow(currentData, (data) => {
                this.$Buttons[index] = data;
                this.$refreshData();
            }, QUILocale.get(
                lg,
                'brick.control.ctaAction.setting.customButtons.window.edit.title'
            ), 'fa fa-pencil');
        },

        $openEntryWindow: function (data, onSubmit, title, texticon) {
            new QUIConfirm({
                title: title,
                icon: 'fa fa-edit',
                texticon: texticon || false,
                maxWidth: 600,
                maxHeight: 400,
                autoclose: false,
                events: {
                    onOpen: function (Win) {
                        Win.getContent().set('html', this.$getFormHtml());

                        const form = Win.getContent().getElement('form');

                        if (!form) {
                            return;
                        }

                        QUIFormUtils.setDataToForm(data, form);
                    }.bind(this),
                    onSubmit: function (Win) {
                        const form = Win.getContent().getElement('form');

                        if (!form) {
                            return;
                        }

                        const formData = QUIFormUtils.getFormData(form);

                        onSubmit({
                            text: String(formData.text || ''),
                            icon: String(formData.icon || ''),
                            cssClass: String(formData.cssClass || ''),
                            href: String(formData.href || '#')
                        });

                        Win.close();
                    }
                }
            }).open();
        },

        $removeEntry: function (index) {
            const removeTitle = QUILocale.get(
                lg,
                'brick.control.ctaAction.setting.customButtons.window.delete.title'
            );
            const removeText = QUILocale.get(
                lg,
                'brick.control.ctaAction.setting.customButtons.window.delete.text'
            );

            new QUIConfirm({
                title: removeTitle,
                icon: 'fa fa-trash',
                information: '<strong>' + removeTitle + '</strong><br>' + removeText,
                maxWidth: 400,
                maxHeight: 250,
                events: {
                    onOpen: function (Win) {
                        const textNode = Win.getContent().getElement('.submit-body .text');

                        if (textNode) {
                            textNode.setStyle('display', 'none');
                        }
                    },
                    onSubmit: function () {
                        this.$Buttons.splice(index, 1);
                        this.$refreshData();
                    }.bind(this)
                }
            }).open();
        },

        $getFormHtml: function () {
            return '<form class="quiqqer-contact-ctaActionButtons-form">' +
                '<label>' + QUILocale.get(lg, 'brick.control.ctaAction.setting.customButtons.form.text') + '</label>' +
                '<input type="text" name="text" />' +
                '<label>' + QUILocale.get(lg, 'brick.control.ctaAction.setting.customButtons.form.icon') + '</label>' +
                '<input type="text" name="icon" placeholder="fa fa-comment" />' +
                '<label>' + QUILocale.get(lg, 'brick.control.ctaAction.setting.customButtons.form.cssClass') + '</label>' +
                '<input type="text" name="cssClass" placeholder="btn-secondary btn--custom" />' +
                '<label>' + QUILocale.get(lg, 'brick.control.ctaAction.setting.customButtons.form.href') + '</label>' +
                '<input type="text" name="href" placeholder="https://example.com" />' +
                '</form>';
        }
    });
});
