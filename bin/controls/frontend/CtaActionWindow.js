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
            '$onOpen',
            '$onCreate'
        ],

        options: {
            maxHeight: 800,
            maxWidth: 1200,
            backgroundClosable: false,
            resizable: false,

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

            // buttons
            btnStyle: 'button', // iconRounded, icon, button
            size: 'default',
            whatsapp: '',
            whatsappLabel: '',
            phone: '',
            phoneLabel: '',
            email: '',
            emailLabel: '',
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

            this.$ctaAction = null;

            this.addEvents({
                onOpen: this.$onOpen,
                onCreate: this.$onCreate
            });
        },

        $onCreate: function() {
            this.getElm().classList.add('qui-window-popup--ctaActionWindow');
        },

        $onOpen: function () {
            this.getContent().classList.add('qui-contact-controls-ctaActionWindow');
            this.getContent().innerHTML = '';
            this.getContent().innerHTML = this.$getSkeletonHtml(this.getAttribute('formDesign'));

            let SkeletonLoader = this.getContent().querySelector('[data-name="skeletonLoader"]');
            let ControlContainer =  document.createElement('div');
            ControlContainer.classList.add('qui-contact-controls-ctaActionWindow__controlContainer');
            ControlContainer.style.opacity = '0';
            this.getContent().appendChild(ControlContainer);

            // Prevent flashing of the SkeletonLoader
            // If the CtaAction control is quickly loaded,
            // it is not necessary to show the SkeletonLoader for a short period of time.
            // Show the SkeletonLoader after 250ms, because the Control may take longer to load.
            setTimeout(() => {
                if (SkeletonLoader && SkeletonLoader.isConnected) {
                    SkeletonLoader.style.opacity = '1';
                }
            }, 250);

            require(['package/quiqqer/contact/bin/controls/frontend/CtaAction'], (CtaAction) => {
                this.$ctaAction = new CtaAction(this.getAttributes());

                this.getCtaAction().addEvents({
                    onLoad: () => {
                        this.fireEvent('load', [this, this.getCtaAction()]);
                        ControlContainer.style.opacity = '1';
                        SkeletonLoader.remove();
                    },
                    onSendBegin: () => {
                        this.Loader.show();
                    },
                    onSendEnd: () => {
                        this.Loader.hide();
                    }
                });

                this.getCtaAction().inject(ControlContainer);
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
        },

        /**
         *
         * Get the skeleton HTML. The form design corresponds to the forwarded attribute.
         *
         * @return {string} The skeleton HTML.
         *
         * @param formDesign
         */
        $getSkeletonHtml: function(formDesign) {
            return `
<div class="qui-contact-controls-ctaActionWindow__skeletonLoader" data-name="skeletonLoader" style="opacity: 0;">
    <div class="quiqqer-contact-ctaAction">
        <aside class="quiqqer-contact-ctaAction__leftContent">
        <div class="default-content">
            <span class="skeleton-loader skeleton-loader-header" style="width: 60%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 100%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 90%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 100%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 80%;"></span>
        </div>
        </aside>
        <section class="quiqqer-contact-ctaAction__rightContent">
            <span class="skeleton-loader skeleton-loader-header" style="width: 40%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 90%;"></span>
            <span class="skeleton-loader skeleton-loader-text" style="width: 50%; margin-bottom: 2rem;"></span>
            
            <form class="quiqqer-contact-ctaAction__form form form--${formDesign} quiqqer-contact-ctaAction__form--dummy-content">
                <div class="form-field form-field--name">
                    <div class="form-label">
                        <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 4rem;"></span>
                    </div>
                    <div class="form-control">
                        <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--lg"></div>
                    </div>
                </div>
                <div class="form-field form-field--company">
                    <div class="form-label">
                        <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 5rem;"></span>
                    </div>
                    <div class="form-control">
                        <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--lg"></div>
                    </div>
                </div>
                <div class="form-field form-field--email">
                    <div class="form-label">
                        <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 3rem;"></span>
                    </div>
                    <div class="form-control">
                        <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--lg"></div>
                    </div>
                </div>
                <div class="form-field form-field--phone">
                    <div class="form-label">
                        <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 6rem;"></span>
                    </div>
                    <div class="form-control">
                        <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--lg"></div>
                    </div>
                </div>
                <div class="form-field form-field--message">
                    <div class="form-label">
                        <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 8rem;"></span>
                    </div>
                    <div class="form-control">
                        <div class="skeleton-loader skeleton-loader-text" style="height: 7rem;"></div>
                    </div>
                </div>
                <div class="form-field form-field--privacy">
                    <label class="check">
                        <div style="display: flex; gap: 1ch; margin-bottom: 0.5em;">
                            <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm" style="width: 1.5rem;"></div>
                            <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm"></div>
                        </div>
                        <div class="skeleton-loader skeleton-loader-text skeleton-loader-text--sm"></div>                    
                    </label>
                </div>
                <div class="form-actions">
                    <span class="skeleton-loader skeleton-loader-text skeleton-loader-text--lg" style="width: min(15rem, 100%);"></span>
                </div>
            </form>
        </section>
    </div>
</div>
            `;
        }
    });
});
