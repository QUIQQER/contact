define('package/quiqqer/contact/bin/controls/backend/ContactHubButtons', [
    'package/quiqqer/bricks/bin/Controls/ButtonsSettings',
    'Locale'
], function (ButtonsSettings, Locale) {
    "use strict";

    const label = (key) => Locale.get('quiqqer/contact', 'contact.contactHub.' + key);

    return new Class({
        Extends: ButtonsSettings,
        Type: 'package/quiqqer/contact/bin/controls/backend/ContactHubButtons',

        $normalizeEntry: function (entry) {
            return Object.assign(this.parent(entry), {
                group: entry.group === 'secondary' ? 'secondary' : 'primary',
                display: ['text', 'icon'].includes(entry.display) ? entry.display : 'icon-text'
            });
        },

        refresh: function () {
            this.parent();

            // The shared editor projects a fixed set of fields into its rows.
            // Keep our local fields there as well for edit and reorder operations.
            const rows = this.$Grid.getData();
            rows.forEach((row, index) => {
                const entry = this.$normalizeEntry(this.$data[index]);
                row.group = entry.group;
                row.display = entry.display;
                row.preview = this.$createPreviewNode(entry);
            });
            this.$Grid.setData({data: rows});
        },

        update: function () {
            this.parent();
            this.$Input.dispatchEvent(new Event('change', {bubbles: true}));
        },

        $openAddDialog: function () {
            this.$adding = true;
            const result = this.parent();
            this.$adding = false;
            return result;
        },

        $createDialog: function () {
            const selected = this.$Grid.getSelectedIndices();
            const entry = this.$normalizeEntry(!this.$adding && selected.length ? this.$data[selected[0]] : {});

            return this.parent().then((Dialog) => {
                const submit = Dialog.submit.bind(Dialog);

                Dialog.submit = () => {
                    if (this.$validateDialog()) {
                        submit();
                    }
                };

                Dialog.addEvent('openAfterCreate', () => {
                    const fields = document.createElement('fieldset');
                    fields.dataset.name = 'contactHubFields';
                    const legend = document.createElement('legend');
                    legend.textContent = label('group');
                    fields.appendChild(legend);

                    const addSelect = (name, values) => {
                        const wrapper = document.createElement('label');
                        wrapper.className = 'field-container';
                        const caption = document.createElement('span');
                        caption.className = 'field-container-item';
                        caption.textContent = label(name);
                        const select = document.createElement('select');
                        select.dataset.name = name;
                        select.className = 'field-container-field';
                        values.forEach((value) => select.add(new Option(label(name + '.' + value), value)));
                        select.value = entry[name];
                        wrapper.append(caption, select);
                        fields.appendChild(wrapper);
                        return select;
                    };

                    const group = addSelect('group', ['primary', 'secondary']);
                    const display = addSelect('display', ['icon-text', 'text', 'icon']);
                    const updateDisplay = () => {
                        const hidden = group.value !== 'secondary';
                        display.parentElement.hidden = hidden;
                        // The backend field-container class overrides the native hidden rule.
                        display.parentElement.style.display = hidden ? 'none' : '';
                    };
                    group.addEventListener('change', updateDisplay);
                    updateDisplay();
                    Dialog.getContent().prepend(fields);
                    this.$actionFields = fields;
                    this.$prepareDialogValidation(Dialog, group, display);
                });
                Dialog.addEvent('close', () => {
                    this.$actionFields = null;
                    this.$dialogFields = null;
                });
                return Dialog;
            });
        },

        $prepareDialogValidation: function (Dialog, group, display) {
            const Form = Dialog.getContent().getElementsByTagName('form')[0];

            if (!Form) {
                return;
            }

            const TextInput = Form.elements.text;
            const IconInput = Form.elements.iconClass;

            if (!TextInput || !IconInput) {
                return;
            }

            const TextLabel = this.$getParentLabel(TextInput);
            const IconLabel = this.$getParentLabel(IconInput);
            const IconCaption = IconLabel ? IconLabel.firstElementChild : null;
            const iconCaption = IconCaption ? IconCaption.textContent : '';

            TextInput.required = true;
            TextInput.setAttribute('aria-required', 'true');

            if (TextLabel && TextLabel.firstElementChild) {
                TextLabel.firstElementChild.textContent = label('buttonLabel') + ' *';

                const Description = document.createElement('div');
                Description.className = 'field-container-item-desc';
                Description.id = 'contactHubButtonLabelDescription';
                Description.dataset.name = 'buttonLabelDescription';
                Description.textContent = label('buttonLabel.description');
                TextLabel.parentElement.appendChild(Description);
                TextInput.setAttribute('aria-describedby', Description.id);
            }

            const updateRequirements = () => {
                const iconRequired = group.value === 'secondary' && display.value === 'icon';

                IconInput.required = iconRequired;

                if (iconRequired) {
                    IconInput.setAttribute('aria-required', 'true');
                } else {
                    IconInput.removeAttribute('aria-required');
                }

                if (IconCaption) {
                    IconCaption.textContent = iconCaption + (iconRequired ? ' *' : '');
                }

                this.$updateDialogValidity();
            };

            this.$dialogFields = {
                text: TextInput,
                icon: IconInput,
                group: group,
                display: display
            };

            TextInput.addEventListener('input', () => this.$updateDialogValidity());
            IconInput.addEventListener('input', () => this.$updateDialogValidity());
            IconInput.addEventListener('change', () => this.$updateDialogValidity());
            group.addEventListener('change', updateRequirements);
            display.addEventListener('change', updateRequirements);
            updateRequirements();
        },

        $getParentLabel: function (Input) {
            let Parent = Input.parentElement;

            while (Parent && Parent.tagName !== 'LABEL') {
                Parent = Parent.parentElement;
            }

            return Parent;
        },

        $updateDialogValidity: function () {
            if (!this.$dialogFields) {
                return;
            }

            const fields = this.$dialogFields;
            const iconRequired = fields.group.value === 'secondary' && fields.display.value === 'icon';

            fields.text.setCustomValidity(
                fields.text.value.trim() === '' ? label('buttonLabel.required') : ''
            );
            fields.icon.setCustomValidity(
                iconRequired && fields.icon.value.trim() === '' ? label('icon.required') : ''
            );
        },

        $validateDialog: function () {
            if (!this.$dialogFields) {
                return false;
            }

            this.$updateDialogValidity();

            const fields = [this.$dialogFields.text, this.$dialogFields.icon];
            const InvalidField = fields.find((Field) => !Field.checkValidity());

            if (!InvalidField) {
                return true;
            }

            InvalidField.focus();
            InvalidField.reportValidity();
            return false;
        },

        $readActionFields: function (params) {
            if (this.$actionFields) {
                params.group = this.$actionFields.querySelector('[data-name="group"]').value;
                params.display = this.$actionFields.querySelector('[data-name="display"]').value;
            }

            return params;
        },

        add: function (params) {
            return this.parent(this.$readActionFields(params));
        },

        edit: function (index, params) {
            return this.parent(index, this.$readActionFields(params));
        },

        $createPreviewNode: function (entry) {
            const preview = document.createElement('span');
            const type = entry.btnType || (entry.group === 'secondary' ? '' : 'primary');
            preview.className = 'btn' + (type ? ' btn-' + type : '');
            const iconOnly = entry.group === 'secondary' && entry.display === 'icon';

            if (iconOnly) {
                preview.classList.add('btn-icon');
            }

            if ((entry.group !== 'secondary' || entry.display !== 'text') && entry.iconClass) {
                const icon = document.createElement('span');
                icon.className = entry.iconClass;
                icon.setAttribute('aria-hidden', 'true');
                preview.appendChild(icon);
            }

            const text = entry.text || entry.ariaLabel || entry.titleAttribute || 'Button';
            preview.setAttribute('aria-label', text);
            preview.title = label('group.' + (entry.group || 'primary'));

            if (!iconOnly) {
                preview.appendChild(document.createTextNode(text));
            }

            return preview;
        }
    });
});
