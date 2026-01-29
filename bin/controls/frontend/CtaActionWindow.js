define('package/quiqqer/contact/bin/controls/frontend/CtaActionWindow', [

    'qui/QUI',
    'qui/controls/windows/SimpleWindow',
    'Ajax',

    'css!package/quiqqer/contact/bin/controls/frontend/CtaActionWindow.css'

], function (QUI, SimpleWindow, QUIAjax) {
    "use strict";

    return new Class({

        Type: 'package/quiqqer/contact/bin/controls/frontend/CtaActionWindow',
        Extends: SimpleWindow,

        Binds: [
            '$onOpen'
        ],

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
            this.setAttributes({
                maxHeight: 800,
                maxWidth: 1200,
            });

            this.parent(options);

            this.$ctaAction = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        $onOpen: function () {
            this.Loader.show();
            this.getContent().classList.add('cta-action-window');
            this.getContent().innerHTML = '';

            require(['package/quiqqer/contact/bin/controls/frontend/CtaAction'], (CtaAction) => {
                this.$ctaAction = new CtaAction(this.getAttributes());

                this.getCtaAction().addEvents({
                    onLoad: () => {
                        this.fireEvent('load', [this, this.getCtaAction()]);
                        this.Loader.hide();
                    },
                    onSendBegin: () => {
                        this.Loader.show();
                    },
                    onSendEnd: () => {
                        this.Loader.hide();
                    }
                });

                this.getCtaAction().inject(this.getContent());
            });
        },

        getCtaAction: function () {
            return this.$ctaAction;
        },

        send: function () {
            if (!this.getCtaAction()) {
                return;
            }

            this.Loader.show();

            this.getCtaAction().send().then(() => {
                this.close();
            }).catch(() => {
                this.Loader.hide();
            });
        }
    });
});
