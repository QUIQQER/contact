<?php

namespace QUI\Contact\CtaAction;

use DOMDocument;
use DOMElement;
use DOMNode;
use QUI;
use QUI\Exception;
use QUI\Components\Controls\Button;

/**
 * This class represents a control for managing a contact call-to-action (CTA) element in a QUI application.
 * It provides functionality to configure its attributes and render its content dynamically. Additionally,
 * it supports form submission for collecting user input and validating the required fields.
 */
class Control extends QUI\Control
{
    /**
     * @var array<int, array{text: string, icon: string, cssClass: string, href: string}>
     */
    protected array $customButtons = [];

    /**
     * Temporary hardcoded integration of the AI agent view.
     *
     * @todo TEMPORARY SOLUTION. The AI agent view (currently the
     *       pcsg/sales-agent chat agent) is detected by hardcoding the package
     *       name, its PHP class and its frontend control module path below.
     *       As soon as a general provider/registry for AI agent views exists,
     *       replace this with a lookup over that module instead of hardcoding
     *       pcsg/sales-agent here.
     *       https://dev.quiqqer.com/pcsg/sales-agent/-/work_items/1
     */
    private const AI_AGENT_VIEW_PACKAGE = 'pcsg/sales-agent';

    private const AI_AGENT_VIEW_CLASS = 'PCSG\SalesAgentControl';

    private const AI_AGENT_VIEW_JS_CONTROL =
        'package/pcsg/sales-agent/bin/js/frontend/controls/SalesAgent';

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

            // views
            'startView' => 'form', // form, select, ai
            'aiControl' => '',
            'aiControlOptions' => '',
            'aiSidebar' => false, // show the branding sidebar in the ai view

            // buttons
            'btnStyle' => 'iconRounded', // iconRounded, icon, button
            'size' => 'default',
            'whatsapp' => '',
            'whatsappLabel' => '',
            'phone' => '',
            'phoneLabel' => '',
            'email' => '',
            'emailLabel' => '',
            'customButtons' => '',

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

        // views: form (default), select, ai
        //
        // @todo TEMPORARY SOLUTION: the AI agent view is currently detected by
        //       hardcoding the pcsg/sales-agent package (see
        //       self::AI_AGENT_VIEW_PACKAGE). The editor setting for a manual
        //       control module path was removed; the control is resolved here
        //       automatically. Replace this with a general AI-agent-view
        //       provider lookup later.
        $aiControl = self::getAiAgentViewControl();
        $hasAiControl = $aiControl !== '';
        $aiControlOptions = $this->sanitizeAiControlOptions((string)$this->getAttribute('aiControlOptions'));

        // "select" (choice between AI agent and form) only makes sense when the
        // AI agent view is available, otherwise fall back to the form.
        $startView = match ($this->getAttribute('startView')) {
            'select' => $hasAiControl ? 'select' : 'form',
            'ai' => $hasAiControl ? 'ai' : 'form',
            default => 'form'
        };

        $aiSidebar = $this->getAttribute('aiSidebar') === true
            || $this->getAttribute('aiSidebar') === 1
            || $this->getAttribute('aiSidebar') === '1';

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

            $contactPhoneLabel = (string)$this->getAttribute('phoneLabel');

            // Wenn Nummer mit 0 beginnt, ersetze führende 0 durch 49 (DE)
            if ($phone !== '' && preg_match('/^0\d+$/', $phone)) {
                $phone = '49' . substr($phone, 1);
            }

            if ($contactPhoneLabel === '') {
                $contactPhoneLabel = $phone;
            }

            $this->setAttribute('phone', $phone);
            $this->setAttribute('phoneLabel', $contactPhoneLabel);
            $Engine->assign('phone', $phone);
            $Engine->assign('contactPhoneLabel', $contactPhoneLabel);
        }

        if (!empty($this->getAttribute('email'))) {
            $email = $this->getAttribute('email');

            $contactEmailLabel = (string)$this->getAttribute('emailLabel');

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = (string)$email;
                $this->setAttribute('email', $email);

                if ($contactEmailLabel === '') {
                    $contactEmailLabel = $email;
                }

                $this->setAttribute('emailLabel', $contactEmailLabel);

                $Engine->assign('email', $email);
                $Engine->assign('contactEmailLabel', $contactEmailLabel);
            } else {
                $this->setAttribute('email', '');
                $this->setAttribute('emailLabel', '');
            }
        }

        $btnStyle = match ($this->getAttribute('btnStyle')) {
            'icon', 'button' => $this->getAttribute('btnStyle'),
            default => 'iconRounded'
        };

        $this->setJavaScriptControlOption('btnStyle', $btnStyle);
        $buttons = $this->getButtons($btnStyle);

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
            'btnStyle' => $btnStyle,
            'hasButtons' => !empty($buttons),
            'buttons' => $buttons,
            'startView' => $startView,
            'aiControl' => $aiControl,
            'aiControlOptions' => $aiControlOptions,
            'aiSidebar' => $aiSidebar,
            'hasAiControl' => $hasAiControl,
            'aiChoiceLabel' => QUI::getLocale()->get('quiqqer/contact', 'contact.ctaAction.choice.ai'),
            'formChoiceLabel' => QUI::getLocale()->get('quiqqer/contact', 'contact.ctaAction.choice.form'),
            'selectTrust' => QUI::getLocale()->get('quiqqer/contact', 'contact.ctaAction.select.trust'),
            'selectPrivacyHint' => QUI::getLocale()->get('quiqqer/contact', 'contact.ctaAction.select.privacyHint')
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/Control.html');
    }

    public function addButton(
        string $text = '',
        string $icon = '',
        string $cssClass = '',
        string $href = '#'
    ): static {
        $this->customButtons[] = [
            'text' => trim($text),
            'icon' => trim($icon),
            'cssClass' => trim($cssClass),
            'href' => trim($href)
        ];

        return $this;
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
        $wrapperId = 'quiqqer-contact-ctaAction-sanitize-root';

        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="' . $wrapperId . '">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        $wrapper = $doc->getElementById($wrapperId);

        if (!$wrapper instanceof DOMElement) {
            return '';
        }

        $this->sanitizeDomNode($wrapper, $allowedTags, $allowedAttrs, $dropTags);

        $sanitized = '';

        foreach (iterator_to_array($wrapper->childNodes) as $childNode) {
            $nodeHtml = $doc->saveHTML($childNode);

            if ($nodeHtml !== false) {
                $sanitized .= $nodeHtml;
            }
        }

        return trim($sanitized);
    }

    /**
     * @return array<int, Button>
     */
    private function getButtons(string $btnStyle): array
    {
        $buttons = [];
        $displayMode = $this->getButtonDisplayMode($btnStyle);
        $defaultSize = $this->sanitizeDefaultButtonSize((string)$this->getAttribute('size'));

        foreach ($this->getDefaultButtons() as $button) {
            if (empty($button['size'])) {
                $button['size'] = $defaultSize;
            }

            $Button = $this->createButtonControl($button, $displayMode);

            if ($Button !== null) {
                $buttons[] = $Button;
            }
        }

        foreach ($this->getCustomButtons() as $button) {
            if (empty($button['size'])) {
                $button['size'] = $defaultSize;
            }

            $Button = $this->createButtonControl($button, $displayMode);

            if ($Button !== null) {
                $buttons[] = $Button;
            }
        }

        return $buttons;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDefaultButtons(): array
    {
        $buttons = [];

        if ($this->getAttribute('email')) {
            $buttons[] = [
                'text' => (string)$this->getAttribute('emailLabel'),
                'icon' => 'fa fa-envelope',
                'href' => 'mailto:' . (string)$this->getAttribute('email'),
                'title' => QUI::getLocale()->get(
                    'quiqqer/contact',
                    'brick.control.ctaAction.frontend.btn.email.title'
                ),
                'ariaLabel' => (string)$this->getAttribute('emailLabel'),
                'customClass' => 'btn--mail',
                'btnType' => '',
            ];
        }

        if ($this->getAttribute('whatsapp')) {
            $buttons[] = [
                'text' => (string)$this->getAttribute('whatsappLabel'),
                'icon' => 'fa fa-whatsapp',
                'href' => 'whatsapp://send?phone=' . (string)$this->getAttribute('whatsapp'),
                'title' => QUI::getLocale()->get(
                    'quiqqer/contact',
                    'brick.control.ctaAction.frontend.btn.whatsapp.title'
                ),
                'ariaLabel' => (string)$this->getAttribute('whatsappLabel'),
                'customClass' => 'btn--whatsapp',
                'btnType' => '',
            ];
        }

        if ($this->getAttribute('phone')) {
            $buttons[] = [
                'text' => (string)$this->getAttribute('phoneLabel'),
                'icon' => 'fa fa-phone',
                'href' => 'tel:' . (string)$this->getAttribute('phone'),
                'title' => QUI::getLocale()->get(
                    'quiqqer/contact',
                    'brick.control.ctaAction.frontend.btn.phone.title'
                ),
                'ariaLabel' => (string)$this->getAttribute('phoneLabel'),
                'customClass' => 'btn--phone',
                'btnType' => 'secondary',
            ];
        }

        return $buttons;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCustomButtons(): array
    {
        $buttons = [];
        $configuredButtons = $this->getAttribute('customButtons');

        foreach ($this->customButtons as $button) {
            $buttons[] = $this->normalizeButtonConfig($button);
        }

        if (is_string($configuredButtons) && trim($configuredButtons) !== '') {
            $decodedButtons = json_decode($configuredButtons, true);

            if (is_array($decodedButtons)) {
                $configuredButtons = $decodedButtons;
            }
        }

        if (!is_array($configuredButtons)) {
            return $buttons;
        }

        foreach ($configuredButtons as $button) {
            if (!is_array($button)) {
                continue;
            }

            $buttons[] = $this->normalizeButtonConfig($button);
        }

        return $buttons;
    }

    /**
     * @param array<string, mixed> $button
     * @return array<string, mixed>
     */
    private function normalizeButtonConfig(array $button): array
    {
        $icon = (string)($button['icon'] ?? $button['iconClass'] ?? '');
        $customClass = (string)($button['customClass'] ?? $button['cssClass'] ?? '');
        $title = trim((string)($button['title'] ?? ''));
        $ariaLabel = trim((string)($button['ariaLabel'] ?? ''));
        $text = trim((string)($button['text'] ?? ''));

        if ($title === '' && $text !== '') {
            $title = $text;
        }

        if ($ariaLabel === '' && $text !== '') {
            $ariaLabel = $text;
        }

        return [
            'text' => $text,
            'identifier' => $this->sanitizeIdentifier((string)($button['identifier'] ?? '')),
            'icon' => $this->sanitizeCssClassList($icon),
            'iconPosition' => ($button['iconPosition'] ?? '') === 'end' ? 'end' : 'start',
            'btnType' => $this->sanitizeButtonType((string)($button['btnType'] ?? '')),
            'size' => $this->sanitizeButtonSize((string)($button['size'] ?? '')),
            'openBrickId' => max(0, (int)($button['openBrickId'] ?? 0)),
            'openBrickWinWidth' => $this->sanitizeDimension($button['openBrickWinWidth'] ?? 0),
            'openBrickWinHeight' => $this->sanitizeDimension($button['openBrickWinHeight'] ?? 0),
            'href' => $this->sanitizeButtonHref((string)($button['href'] ?? '#')),
            'targetBlank' => $this->normalizeBooleanFlag($button['targetBlank'] ?? false),
            'title' => strip_tags($title),
            'ariaLabel' => strip_tags($ariaLabel),
            'disabled' => $this->normalizeBooleanFlag($button['disabled'] ?? false),
            'fullWidth' => $this->normalizeBooleanFlag($button['fullWidth'] ?? false),
            'onClick' => $this->sanitizeOnClick((string)($button['onClick'] ?? '')),
            'customClass' => $this->sanitizeCssClassList($customClass),
            'isDisabled' => $this->normalizeBooleanFlag($button['isDisabled'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $button
     */
    private function createButtonControl(array $button, string $displayMode): ?Button
    {
        if (!empty($button['isDisabled'])) {
            return null;
        }

        return new Button(array_merge($button, [
            'displayMode' => $displayMode,
        ]));
    }

    private function getButtonDisplayMode(string $btnStyle): string
    {
        return match ($btnStyle) {
            'icon' => 'icon-only',
            'iconRounded' => 'icon-only-rounded',
            default => 'button',
        };
    }

    private function sanitizeCssClassList(string $classList): string
    {
        $classes = preg_split('/\s+/', trim($classList)) ?: [];
        $classes = array_filter($classes, static function ($class): bool {
            return (bool)preg_match('/^[a-zA-Z0-9_-]+$/', $class);
        });

        return implode(' ', $classes);
    }

    private function sanitizeIdentifier(string $identifier): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', trim($identifier)) ?? '';
    }

    private function sanitizeButtonType(string $type): string
    {
        $allowed = [
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
            'link-body',
        ];

        $type = trim($type);

        if (!in_array($type, $allowed, true)) {
            return 'primary';
        }

        return $type;
    }

    private function sanitizeButtonSize(string $size): string
    {
        return match (trim($size)) {
            'sm', 'lg', 'default' => trim($size),
            default => '',
        };
    }

    private function sanitizeDefaultButtonSize(string $size): string
    {
        return match (trim($size)) {
            'sm', 'lg' => trim($size),
            default => 'default',
        };
    }

    private function sanitizeDimension(mixed $value): int
    {
        $dimension = (int)$value;

        if ($dimension > 0) {
            return $dimension;
        }

        return 0;
    }

    private function normalizeBooleanFlag(mixed $value): bool
    {
        return $value === true
            || $value === 1
            || $value === '1';
    }

    private function sanitizeButtonHref(string $href): string
    {
        $href = trim($href);

        if ($href === '') {
            return '#';
        }

        if (
            preg_match('#^\s*javascript:#i', $href)
            || preg_match('#^\s*data:#i', $href)
        ) {
            return '#';
        }

        if (
            str_starts_with($href, '#')
            || str_starts_with($href, '/')
            || str_starts_with($href, '?')
        ) {
            return $href;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);

        if ($scheme === null || $scheme === false) {
            return $href;
        }

        $scheme = strtolower($scheme);
        $allowedSchemes = ['http', 'https', 'mailto', 'tel', 'whatsapp'];

        if (!in_array($scheme, $allowedSchemes, true)) {
            return '#';
        }

        return $href;
    }

    private function sanitizeOnClick(string $onClick): string
    {
        $onClick = trim($onClick);

        if ($onClick === '') {
            return '';
        }

        if (str_ends_with($onClick, ';')) {
            $onClick = rtrim(substr($onClick, 0, -1));
        }

        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$.]*$/', $onClick)) {
            return $onClick . '();';
        }

        if (preg_match('/^([A-Za-z_$][A-Za-z0-9_$.]*)\((.*)\)$/s', $onClick, $matches)) {
            $args = trim($matches[2]);

            if (
                str_contains($args, ';') ||
                str_contains($args, '<') ||
                str_contains($args, '>') ||
                str_contains($args, '`')
            ) {
                return '';
            }

            return $matches[1] . '(' . $args . ');';
        }

        return '';
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
     * Whether the AI agent view is available.
     *
     * @todo TEMPORARY SOLUTION: hardcoded to pcsg/sales-agent, see
     *       self::AI_AGENT_VIEW_PACKAGE. Replace with a general
     *       AI-agent-view provider lookup later.
     */
    public static function isAiAgentViewAvailable(): bool
    {
        try {
            if (!QUI::getPackageManager()->isInstalled(self::AI_AGENT_VIEW_PACKAGE)) {
                return false;
            }
        } catch (QUI\Exception) {
            return false;
        }

        return class_exists(self::AI_AGENT_VIEW_CLASS);
    }

    /**
     * Frontend control module path of the AI agent view, or an empty string
     * when the AI agent view is not available.
     *
     * @todo TEMPORARY SOLUTION: hardcoded to pcsg/sales-agent, see
     *       self::AI_AGENT_VIEW_PACKAGE. Replace with a general
     *       AI-agent-view provider lookup later.
     */
    public static function getAiAgentViewControl(): string
    {
        if (!self::isAiAgentViewAvailable()) {
            return '';
        }

        return self::AI_AGENT_VIEW_JS_CONTROL;
    }

    /**
     * Sanitize the opaque options object that is forwarded to the AI agent view control.
     * Must be a JSON object; returns a normalized JSON string or an empty string.
     */
    private function sanitizeAiControlOptions(string $json): string
    {
        $json = trim($json);

        if ($json === '') {
            return '';
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return '';
        }

        $encoded = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '' : $encoded;
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
            'startView',
            'aiControl',
            'aiControlOptions',
            'aiSidebar',
            'whatsapp',
            'whatsappLabel',
            'phone',
            'phoneLabel',
            'email',
            'emailLabel',
            'customButtons',
            'formDesign',
            'btnStyle',
            'size',
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
