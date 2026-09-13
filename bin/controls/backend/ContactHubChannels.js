/**
 * Compact editor for the three curated contact channels (email, WhatsApp,
 * phone). Each channel is one row: value, displayed label and an active
 * toggle. The state is stored as JSON in the hidden setting input; the button
 * links and normalization happen server-side in ContactHub.php.
 */
define('package/quiqqer/contact/bin/controls/backend/ContactHubChannels', [

    'qui/controls/Control',
    'Locale'

], function (QUIControl, Locale) {
    "use strict";

    const lg = 'quiqqer/contact';
    const CHANNELS = ['email', 'whatsapp', 'phone'];
    const ICONS = {email: 'fa fa-envelope', whatsapp: 'fa fa-whatsapp', phone: 'fa fa-phone'};
    const setting = (key) => Locale.get(lg, 'brick.control.contactHub.setting.' + key);
    const channel = (key) => Locale.get(lg, 'contact.contactHub.channels.' + key);

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/contact/bin/controls/backend/ContactHubChannels',

        Binds: [
            '$onImport',
            '$serialize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input = null;
            this.$fields = {};

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            const Elm = this.getElm();

            this.$Input = Elm.nodeName === 'INPUT' ? Elm : Elm.querySelector('input');

            if (!this.$Input) {
                return;
            }

            this.$Input.type = 'hidden';

            const stored = this.$parse(this.$Input.value);
            const container = document.createElement('div');
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '15px';
            container.style.width = '100%';
            container.style.marginTop = '10px';

            CHANNELS.forEach((type) => {
                container.appendChild(this.$createRow(type, stored[type] || {}));
            });

            container.addEventListener('change', this.$serialize);
            container.addEventListener('input', this.$serialize);
            this.$Input.insertAdjacentElement('afterend', container);
        },

        $createRow: function (type, data) {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.flexDirection = 'column';
            row.style.gap = '8px';
            row.style.padding = '12px';
            row.style.border = '1px solid rgba(0, 0, 0, 0.1)';
            row.style.borderRadius = '6px';

            const head = document.createElement('div');
            head.style.display = 'flex';
            head.style.alignItems = 'center';
            head.style.gap = '10px';
            head.style.fontWeight = '600';
            head.innerHTML = '<span class="' + ICONS[type] + '" aria-hidden="true"></span>' +
                '<span>' + channel(type) + '</span>';

            const toggle = document.createElement('label');
            toggle.style.marginLeft = 'auto';
            toggle.style.display = 'inline-flex';
            toggle.style.alignItems = 'center';
            toggle.style.gap = '6px';
            toggle.style.fontWeight = '400';

            const active = document.createElement('input');
            active.type = 'checkbox';
            active.checked = data.active !== false;

            const activeText = document.createElement('span');
            activeText.textContent = channel('active');

            toggle.append(active, activeText);
            head.appendChild(toggle);
            row.appendChild(head);

            const value = this.$createField(type, 'value', channel(type + '.value'), setting(type + '.placeholder'), data.value);
            const label = this.$createField(type, 'label', channel('text'), setting(type + 'Label.placeholder'), data.label);

            row.append(value.field, label.field);

            this.$fields[type] = {value: value.input, label: label.input, active: active};

            return row;
        },

        $createField: function (type, key, caption, placeholder, value) {
            const id = 'contactHubChannel-' + type + '-' + key;

            const field = document.createElement('label');
            field.className = 'field-container';
            field.setAttribute('for', id);

            const span = document.createElement('span');
            span.className = 'field-container-item';
            span.textContent = caption;

            const input = document.createElement('input');
            input.type = 'text';
            input.id = id;
            input.className = 'field-container-field';
            input.placeholder = placeholder;
            input.value = value || '';

            field.append(span, input);

            return {field: field, input: input};
        },

        $parse: function (raw) {
            let list = [];

            try {
                list = JSON.parse(raw || '[]');
            } catch (e) {
                /* empty configuration */
            }

            const byType = {};

            if (Array.isArray(list)) {
                list.forEach((entry) => {
                    if (entry && CHANNELS.includes(entry.type) && !byType[entry.type]) {
                        byType[entry.type] = entry;
                    }
                });
            }

            return byType;
        },

        $serialize: function () {
            const data = CHANNELS.map((type) => ({
                type: type,
                value: this.$fields[type].value.value.trim(),
                label: this.$fields[type].label.value.trim(),
                active: this.$fields[type].active.checked
            }));

            this.$Input.value = JSON.stringify(data);
            this.$Input.dispatchEvent(new Event('change', {bubbles: true}));
        }
    });
});
