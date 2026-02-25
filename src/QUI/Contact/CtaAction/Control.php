<?php

namespace QUI\Contact\CtaAction;

use DOMDocument;
use DOMElement;
use DOMNode;
use QUI;
use QUI\Exception;

/**
 * This class represents a control for managing a contact call-to-action (CTA) element in a QUI application.
 * It provides functionality to configure its attributes and render its content dynamically. Additionally,
 * it supports form submission for collecting user input and validating the required fields.
 */
class Control extends QUI\Control
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'header' => '',
            'content' => '',
            'title' => '',
            'description' => '',

            'name_label' => '',
            'name_placeholder' => '',
            'company_label' => '',
            'company_placeholder' => '',
            'email_label' => '',
            'email_placeholder' => '',
            'phone_label' => '',
            'phone_placeholder' => '',
            'message_label' => '',
            'message_placeholder' => '',
            'submit_label' => '',

            // buttons
            'btnStyle' => 'iconRounded', // iconRounded, icon, button
            'whatsapp' => '',
            'whatsappLabel' => '',
            'phone' => '',
            'phoneLabel' => '',
            'email' => '',
            'emailLabel' => '',

            // design
            'formDesign' => '', // default, grid, labelLeft
            'bgColor' => '',
            'color' => '',
            'leftBgColor' => '',
            'leftColor' => ''
        ]);

        parent::__construct($attributes);

        $this->setJavaScriptControl('package/quiqqer/contact/bin/controls/frontend/CtaAction');
        $this->addCSSClass('quiqqer-contact-ctaAction');
        $this->addCSSFile(dirname(__FILE__) . '/Control.css');
    }

    public function getBody(): string
    {
        $brickId = $this->getAttribute('data-brickid');
        $logo = null;

        // set brick data
        if (!empty($brickId)) {
            try {
                $brick = QUI\Bricks\Manager::init()?->getBrickById((int)$brickId);

                if ($brick !== null) {
                    if ($brick->getAttribute('frontendTitle')) {
                        $this->setAttribute('title', $brick->getAttribute('frontendTitle'));
                    }

                    if ($brick->getAttribute('ctaDescription')) {
                        $this->setAttribute('description', $brick->getAttribute('ctaDescription'));
                    }
                }

                if (!empty($this->getAttribute('logo'))) {
                    try {
                        $logo = QUI\Projects\Media\Utils::getImageByUrl((string)$this->getAttribute('logo'));
                    } catch (QUI\Exception) {
                    }
                }
            } catch (QUI\Exception) {
            }
        }

        $formDesign = match ($this->getAttribute('formDesign')) {
            'grid', 'labelLeft' => $this->getAttribute('formDesign'),
            default => 'default'
        };

        $Engine = QUI::getTemplateManager()->getEngine();

        if (empty($logo)) {
            $Project = QUI::getRewrite()->getProject();

            if ($Project) {
                $logo = $Project->getMedia()->getLogoImage();
            }
        }

        $title = $this->getAttribute('title');
        //$header = $this->getAttribute('header');
        $description = $this->getAttribute('description');
        $content = $this->getAttribute('content');

        $nameLabel = $this->getAttribute('name_label');
        $namePlaceholder = $this->getAttribute('name_placeholder');
        $companyLabel = $this->getAttribute('company_label');
        $companyPlaceholder = $this->getAttribute('company_placeholder');
        $emailLabel = $this->getAttribute('email_label');
        $emailPlaceholder = $this->getAttribute('email_placeholder');
        $phoneLabel = $this->getAttribute('phone_label');
        $phonePlaceholder = $this->getAttribute('phone_placeholder');
        $messageLabel = $this->getAttribute('message_label');
        $messagePlaceholder = $this->getAttribute('message_placeholder');
        $submitLabel = $this->getAttribute('submit_label');

        if (empty($title)) {
            $title = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_title'
            );
        }

        if (empty($description)) {
            $description = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_description'
            );
        }

        if (empty($content)) {
            $content = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_content'
            );
        }

        if (empty($nameLabel)) {
            $nameLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_name_label'
            );
        }

        if (empty($namePlaceholder)) {
            $namePlaceholder = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_name_placeholder'
            );
        }

        if (empty($companyLabel)) {
            $companyLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_company_label'
            );
        }

        if (empty($companyPlaceholder)) {
            $companyPlaceholder = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_company_placeholder'
            );
        }

        if (empty($emailLabel)) {
            $emailLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_email_label'
            );
        }
        if (empty($emailPlaceholder)) {
            $emailPlaceholder = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_email_placeholder'
            );
        }

        if (empty($phoneLabel)) {
            $phoneLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_phone_label'
            );
        }
        if (empty($phonePlaceholder)) {
            $phonePlaceholder = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_phone_placeholder'
            );
        }

        if (empty($messageLabel)) {
            $messageLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_message_label'
            );
        }
        if (empty($messagePlaceholder)) {
            $messagePlaceholder = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_message_placeholder'
            );
        }

        if (empty($submitLabel)) {
            $submitLabel = QUI::getLocale()->get(
                'quiqqer/contact',
                'contact.ctaAction.default_submit_label'
            );
        }

        $this->setCustomVariable('bgColor', $this->getAttribute('bgColor'));
        $this->setCustomVariable('color', $this->getAttribute('color'));
        $this->setCustomVariable('left-bgColor', $this->getAttribute('leftBgColor'));
        $this->setCustomVariable('left-color', $this->getAttribute('leftColor'));

        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $content = $this->sanitizeContentHtml($content);
        $nameLabel = htmlspecialchars($nameLabel, ENT_QUOTES, 'UTF-8');
        $namePlaceholder = htmlspecialchars($namePlaceholder, ENT_QUOTES, 'UTF-8');
        $companyLabel = htmlspecialchars($companyLabel, ENT_QUOTES, 'UTF-8');
        $companyPlaceholder = htmlspecialchars($companyPlaceholder, ENT_QUOTES, 'UTF-8');
        $emailLabel = htmlspecialchars($emailLabel, ENT_QUOTES, 'UTF-8');
        $emailPlaceholder = htmlspecialchars($emailPlaceholder, ENT_QUOTES, 'UTF-8');
        $phoneLabel = htmlspecialchars($phoneLabel, ENT_QUOTES, 'UTF-8');
        $phonePlaceholder = htmlspecialchars($phonePlaceholder, ENT_QUOTES, 'UTF-8');
        $messageLabel = htmlspecialchars($messageLabel, ENT_QUOTES, 'UTF-8');
        $messagePlaceholder = htmlspecialchars($messagePlaceholder, ENT_QUOTES, 'UTF-8');
        $submitLabel = htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8');

        // buttons
        if (!empty($this->getAttribute('whatsapp'))) {
            $whatsapp = $this->getAttribute('whatsapp');
            $whatsapp = preg_replace('/\D+/', '', (string)$whatsapp) ?? '';

            $whatsappLabel = (string)$this->getAttribute('whatsappLabel');

            // Wenn Nummer mit 0 beginnt, ersetze führende 0 durch 49 (DE)
            if ($whatsapp !== '' && preg_match('/^0\d+$/', $whatsapp)) {
                $whatsapp = '49' . substr($whatsapp, 1);
            }

            if ($whatsappLabel === '') {
                $whatsappLabel = $whatsapp;
            }

            $this->setAttribute('whatsapp', $whatsapp);
            $this->setAttribute('whatsappLabel', $whatsappLabel);
            $Engine->assign('whatsapp', $whatsapp);
            $Engine->assign('whatsappLabel', $whatsappLabel);
        }

        if (!empty($this->getAttribute('phone'))) {
            $phone = $this->getAttribute('phone');
            $phone = preg_replace('/\D+/', '', (string)$phone) ?? '';

            $phoneLabel = (string)$this->getAttribute('phoneLabel');

            // Wenn Nummer mit 0 beginnt, ersetze führende 0 durch 49 (DE)
            if ($phone !== '' && preg_match('/^0\d+$/', $phone)) {
                $phone = '49' . substr($phone, 1);
            }

            if ($phoneLabel === '') {
                $phoneLabel = $phone;
            }

            $this->setAttribute('phone', $phone);
            $this->setAttribute('phoneLabel', $phoneLabel);
            $Engine->assign('phone', $phone);
            $Engine->assign('phoneLabel', $phoneLabel);
        }

        if (!empty($this->getAttribute('email'))) {
            $email = $this->getAttribute('email');

            $emailLabel = (string)$this->getAttribute('emailLabel');

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8');

                $this->setAttribute(
                    'email',
                    htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
                );

                if ($emailLabel === '') {
                    $emailLabel = $email;
                }

                $this->setAttribute('emailLabel', $emailLabel);

                $Engine->assign('email', $email);
                $Engine->assign('emailLabel', $emailLabel);
            } else {
                $this->setAttribute('email', '');
                $this->setAttribute('emailLabel', '');
            }
        }

        $btnStyle = match ($this->getAttribute('btnStyle')) {
            'icon', 'button' => $this->getAttribute('btnStyle'),
            default => 'iconRounded'
        };

        $Engine->assign([
            'self' => $this,
            'logo' => $logo,
            'content' => $content,
            'title' => $title,
            'description' => $description,
            'nameLabel' => $nameLabel,
            'namePlaceholder' => $namePlaceholder,
            'companyLabel' => $companyLabel,
            'companyPlaceholder' => $companyPlaceholder,
            'emailLabel' => $emailLabel,
            'emailPlaceholder' => $emailPlaceholder,
            'phoneLabel' => $phoneLabel,
            'phonePlaceholder' => $phonePlaceholder,
            'messageLabel' => $messageLabel,
            'messagePlaceholder' => $messagePlaceholder,
            'submitLabel' => $submitLabel,
            'privacyText' => QUI::getLocale()->get('quiqqer/contact', 'contact.ctaAction.privacy', [
                'privacyLink' => $this->getPrivacyLink()
            ]),
            'formDesign' => $formDesign,
            'btnStyle' => $btnStyle
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/Control.html');
    }

    /**
     * @param array<string, mixed> $formData
     * @throws Exception
     */
    public function send(array $formData = []): void
    {
        $name = trim((string)($formData['name'] ?? ''));
        $email = trim((string)($formData['email'] ?? ''));
        $phone = trim((string)($formData['phone'] ?? ''));
        $hasEmail = $email !== '';
        $hasPhone = $phone !== '';

        if ($name === '') {
            throw new QUI\Exception(
                QUI::getLocale()->get('quiqqer/contact', 'brick.control.ctaAction.exception.nameNeeded')
            );
        }

        if (!$hasEmail && !$hasPhone) {
            throw new QUI\Exception(
                QUI::getLocale()->get('quiqqer/contact', 'brick.control.ctaAction.exception.emailNeeded')
            );
        }

        if ($hasEmail && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new QUI\Exception(
                QUI::getLocale()->get('quiqqer/contact', 'brick.control.ctaAction.exception.invalidEmail')
            );
        }

        $mailer = QUI::getMailManager()->getMailer();
        $recipient = '';
        $brick = null;

        // recipient -> wenn im baustein, dann id vom baustein und email daraus
        if ($this->isInBrick()) {
            try {
                $brick = QUI\Bricks\Manager::init()?->getBrickById(
                    (int)$this->getAttribute('data-brickid')
                );

                $recipient = $brick?->getAttribute('recipient');

                if (empty($this->getAttribute('title')) && $brick) {
                    $this->setAttribute('title', $brick->getAttribute('title'));
                }
            } catch (QUI\Exception) {
            }
        }

        // recipient -> wenn kein baustein, contact erhält standard kontakt mail
        // @todo contact -> standard kontakt email als einstellung

        // recipient -> wenn alles nix, dann admin mail
        if (empty($recipient)) {
            $recipient = QUI::conf('mail', 'admin_mail');
        }

        $mailer->addRecipient($recipient);
        $mailer->setSubject($this->getAttribute('title'));

        if (
            !empty($formData['email'])
            && filter_var($formData['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $mailer->addReplyTo($formData['email']);
        }

        // body
        $locale = QUI::getLocale();

        $html = $this->buildHtmlHeading(
            $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.title')
        );

        // source data
        $html .= $this->buildHtmlHeading(
            $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.brick.title')
        );
        $html .= '<ul>';

        if ($brick) {
            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.brick.brickId'),
                (string)$brick->getAttribute('id')
            );

            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.brick.brickTitle'),
                (string)$brick->getAttribute('title')
            );
        }
        $html .= '</ul>';

        // contact data
        $html .= $this->buildHtmlHeading(
            $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact')
        );
        $html .= '<ul>';

        if (!empty($formData['name'])) {
            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact.name'),
                $formData['name']
            );
        }

        if (!empty($formData['company'])) {
            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact.company'),
                $formData['company']
            );
        }

        if (!empty($formData['email'])) {
            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact.email'),
                $formData['email']
            );
        }

        if (!empty($formData['phone'])) {
            $html .= $this->buildHtmlListItem(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact.phone'),
                $formData['phone']
            );
        }

        $html .= '</ul>';

        if (!empty($formData['message'])) {
            $html .= $this->buildHtmlHeading(
                $locale->get('quiqqer/contact', 'brick.control.ctaAction.mail.contact.message')
            );
            $html .= $this->buildHtmlParagraph($formData['message'], true);
        }

        $mailer->setHtml(true);
        $mailer->setBody($html);

        try {
            $mailer->send();
        } catch (\Exception $e) {
            QUI\System\Log::addError($e->getMessage());
            throw new QUI\Exception(
                QUI::getLocale()->get('quiqqer/contact', 'brick.control.ctaAction.exception.mailSendFailed')
            );
        }
    }

    protected function isInBrick(): bool
    {
        return !empty($this->getAttribute('data-brickid'));
    }

    public function getPrivacyLink(): string
    {
        try {
            $Project = QUI::getRewrite()->getProject();
        } catch (QUI\Exception) {
            return '';
        }

        $values = null;

        try {
            $Config = QUI::getPackage('quiqqer/erp')->getConfig();
            $values = $Config?->get('sites', 'privacy_policy');
        } catch (QUI\Exception) {
        }

        if (empty($Project)) {
            return '';
        }

        $url = '';
        $project = '';
        $lang = '';
        $id = '';
        $title = '';

        if (!empty($values)) {
            $lang = $Project->getLang();
            $values = json_decode($values, true);

            if (!empty($values[$lang])) {
                try {
                    $Site = QUI\Projects\Site\Utils::getSiteByLink($values[$lang]);

                    $url = $Site->getUrlRewritten();
                    $title = $Site->getAttribute('title');
                    $project = $Site->getProject()->getName();
                    $lang = $Site->getProject()->getLang();
                    $id = $Site->getId();
                } catch (QUI\Exception) {
                }
            }
        }

        if (
            empty($url)
            && empty($project)
            && empty($id)
            && empty($title)
        ) {
            try {
                $privacy = $Project->getSites([
                    'where' => [
                        'type' => 'quiqqer/sitetypes:types/privacypolicy'
                    ],
                    'limit' => 1
                ]);

                if (isset($privacy[0])) {
                    $Site = $privacy[0];
                    $url = $Site->getUrlRewritten();
                    $title = $Site->getAttribute('title');
                    $project = $Site->getProject()->getName();
                    $lang = $Site->getProject()->getLang();
                    $id = $Site->getId();
                }
            } catch (QUI\Exception) {
            }
        }

        if (
            empty($url)
            || empty($project)
            || empty($id)
            || empty($title)
        ) {
            return '';
        }

        return '<a href="' . $url . '" target="_blank" rel="noopener" 
            data-project="' . $project . '" 
            data-lang="' . $lang . '" 
            data-id="' . $id . '">' . $title . '</a>';
    }

    private function buildHtmlListItem(string $label, string $value): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return '<li><strong>' . $safeLabel . ':</strong> ' . $safeValue . '</li>';
    }

    private function buildHtmlHeading(string $text): string
    {
        return '<h3>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</h3>';
    }

    private function buildHtmlParagraph(string $text, bool $allowLineBreaks = false): string
    {
        $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        if ($allowLineBreaks) {
            $safeText = nl2br($safeText);
        }

        return '<p>' . $safeText . '</p>';
    }

    private function sanitizeContentHtml(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $allowedTags = [
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'p',
            'br',
            'ul',
            'ol',
            'li',
            'strong',
            'em',
            'b',
            'i',
            'u',
            'a',
            'small',
            'sup',
            'sub',
            'blockquote',
            'div',
            'img'
        ];

        $allowedAttrs = [
            'a' => ['href', 'title', 'target', 'rel', 'class', 'style'],
            'img' => ['src', 'alt', 'title', 'class', 'style'],
            'div' => ['class', 'style']
        ];

        $dropTags = [
            'script',
            'style',
            'iframe',
            'object',
            'embed',
            'link',
            'meta',
            'base'
        ];

        $doc = new DOMDocument('1.0', 'UTF-8');
        $prevUseErrors = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        $this->sanitizeDomNode($doc, $allowedTags, $allowedAttrs, $dropTags);

        $sanitized = $doc->saveHTML();
        return $sanitized === false ? '' : trim($sanitized);
    }

    /**
     * @param DOMNode $node
     * @param array<int, string> $allowedTags
     * @param array<string, array<int, string>> $allowedAttrs
     * @param array<int, string> $dropTags
     * @return void
     */
    private function sanitizeDomNode(
        DOMNode $node,
        array $allowedTags,
        array $allowedAttrs,
        array $dropTags
    ): void {
        if ($node->hasChildNodes()) {
            // Copy to array to avoid live NodeList issues when removing nodes
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }

            foreach ($children as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    /** @var DOMElement $child */
                    $tag = strtolower($child->nodeName);

                    if (in_array($tag, $dropTags, true)) {
                        $node->removeChild($child);
                        continue;
                    }

                    if (!in_array($tag, $allowedTags, true)) {
                        // unwrap the element: keep its children/text, drop the element itself
                        while ($child->firstChild) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                        $node->removeChild($child);
                        continue;
                    }

                    if ($child->hasAttributes()) {
                        $allowedForTag = $allowedAttrs[$tag] ?? [];
                        $attrs = [];
                        foreach ($child->attributes as $attr) {
                            $attrs[] = $attr;
                        }

                        foreach ($attrs as $attr) {
                            $attrName = strtolower($attr->name);
                            if (!in_array($attrName, $allowedForTag, true)) {
                                $child->removeAttribute($attrName);
                                continue;
                            }

                            if ($tag === 'a' && $attrName === 'href') {
                                $href = trim($attr->value);
                                if (
                                    $href === '' ||
                                    preg_match('#^\s*javascript:#i', $href) ||
                                    preg_match('#^\s*data:#i', $href)
                                ) {
                                    $child->removeAttribute($attrName);
                                }
                                continue;
                            }

                            if ($tag === 'a' && $attrName === 'target') {
                                $target = strtolower(trim($attr->value));
                                if ($target !== '_blank' && $target !== '_self') {
                                    $child->removeAttribute($attrName);
                                    continue;
                                }

                                if ($target === '_blank') {
                                    $rel = $child->getAttribute('rel');
                                    $relParts = preg_split('/\s+/', strtolower(trim($rel))) ?: [];
                                    $relParts = array_filter($relParts);
                                    $relParts[] = 'noopener';
                                    $relParts[] = 'noreferrer';
                                    $relParts = array_unique($relParts);
                                    $child->setAttribute('rel', implode(' ', $relParts));
                                }
                            }
                        }
                    }
                }

                $this->sanitizeDomNode($child, $allowedTags, $allowedAttrs, $dropTags);
            }
        }
    }

    /**
     * Retrieves the list of allowed attributes for the current context.
     *
     * @return array<string> An array of strings representing the allowed attribute keys.
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'data-brickid',
            'header',
            'content',
            'title',
            'description',
            'name_label',
            'name_placeholder',
            'company_label',
            'company_placeholder',
            'email_label',
            'email_placeholder',
            'phone_label',
            'phone_placeholder',
            'message_label',
            'message_placeholder',
            'submit_label',
            'success_message',
            'whatsapp',
            'whatsappLabel',
            'phone',
            'phoneLabel',
            'email',
            'emailLabel',
            'formDesign',
            'btnStyle',
            'bgColor',
            'color',
            'leftBgColor',
            'leftColor'
        ];
    }

    /**
     * Set custom CSS variable to the control as inline style
     * --_q-controlSetting-$name: var(--qui-contact-ctaAction-$name, $value);
     *
     * Example:
     *     --_q-controlSetting-bgColor: var(--qui-contact-ctaAction-bgColor, #ffffff);
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    private function setCustomVariable(string $name, string $value): void
    {
        if (!$name || !$value) {
            return;
        }

        $this->setStyle(
            '--_q-controlSetting-' . $name,
            'var(--qui-contact-ctaAction-' . $name . ', ' . $value . ')'
        );
    }
}
