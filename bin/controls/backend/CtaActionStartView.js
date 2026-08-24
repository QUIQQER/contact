/**
 * Start view select ("Ansicht beim Öffnen") for the CTA action brick.
 *
 * Extends the native select of the brick setting: the "Auswahl" and "KI Agent"
 * options are only kept when the AI agent view is available. When it is not
 * available, only the "Formular" option remains and a previously selected
 * AI-agent-dependent option falls back to the form.
 *
 * @todo TEMPORARY SOLUTION. Availability is checked via ajax against the
 *       hardcoded pcsg/sales-agent integration (see the PHP Control). As soon as
 *       a general AI-agent-view provider exists, replace this availability
 *       check accordingly.
 *
 * @module package/quiqqer/contact/bin/controls/backend/CtaActionStartView
 */
define('package/quiqqer/contact/bin/controls/backend/CtaActionStartView', [

    'qui/controls/Control',
    'Ajax'

], function (QUIControl, QUIAjax) {
    "use strict";

    // view options that require the AI agent view to be available
    const AI_AGENT_VIEWS = ['select', 'ai'];

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/contact/bin/controls/backend/CtaActionStartView',

        initialize: function (options) {
            this.parent(options);

            this.$Select = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            const Elm = this.getElm();

            this.$Select = Elm.nodeName === 'SELECT' ? Elm : Elm.querySelector('select');

            if (!this.$Select) {
                return;
            }

            QUIAjax.get('package_quiqqer_contact_ajax_ctaAction_isAiAgentViewAvailable', (available) => {
                if (available) {
                    return;
                }

                this.$removeAiAgentOptions();
            }, {
                'package': 'quiqqer/contact'
            });
        },

        /**
         * Remove the options that depend on the AI agent view and fall back to
         * the form view when one of them was selected.
         */
        $removeAiAgentOptions: function () {
            let wasSelected = false;

            AI_AGENT_VIEWS.forEach((value) => {
                const Option = this.$Select.querySelector('option[value="' + value + '"]');

                if (!Option) {
                    return;
                }

                if (Option.selected) {
                    wasSelected = true;
                }

                Option.remove();
            });

            if (wasSelected) {
                this.$Select.value = 'form';
                this.$Select.dispatchEvent(new Event('change'));
            }
        }
    });
});
