<?php

namespace QUI\Contact\CtaAction;

use QUI;
use QUI\Exception;

/**
 * This class represents a control for managing a contact call-to-action (CTA) element in a QUI application.
 * It provides functionality to configure its attributes and render its content dynamically. Additionally,
 * it supports form submission for collecting user input and validating the required fields.
 *
 * @todo datenschutz checkbox -> sprachvariable
 * @todo exceptions -> sprachvariablen
 */
class Control extends QUI\Control
{
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
            'whatsapp' => '',
            'phone' => '',
            'email' => '',
        ]);

        parent::__construct($attributes);

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
                QUI\Bricks\Manager::init()->getBrickById((int)$brickId);

                $this->setAttribute('title', $this->getAttribute('frontendTitle'));
                $this->setAttribute('description', $this->getAttribute('ctaDescription'));

                if (!empty($this->getAttribute('logo'))) {
                    try {
                        $logo = QUI\Projects\Media\Utils::getImageByUrl($this->getAttribute('logo'));
                    } catch (QUI\Exception) {
                    }
                }
            } catch (QUI\Exception) {
            }
        }

        $Engine = QUI::getTemplateManager()->getEngine();

        if (empty($logo)) {
            $logo = QUI::getRewrite()->getProject()->getMedia()->getLogoImage();
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

        // buttons
        if (!empty($this->getAttribute('whatsapp'))) {
            $whatsapp = $this->getAttribute('whatsapp');
            $whatsapp = preg_replace('/\D+/', '', (string)$whatsapp);

            // Wenn Nummer mit 0 beginnt, ersetze führende 0 durch 49 (DE)
            if (preg_match('/^0\d+$/', $whatsapp)) {
                $whatsapp = '49' . substr($whatsapp, 1);
            }

            $Engine->assign('whatsapp', $whatsapp);
        }

        if (!empty($this->getAttribute('phone'))) {
            $phone = $this->getAttribute('phone');
            $phone = preg_replace('/\D+/', '', (string)$phone);

            // Wenn Nummer mit 0 beginnt, ersetze führende 0 durch 49 (DE)
            if (preg_match('/^0\d+$/', $phone)) {
                $phone = '49' . substr($phone, 1);
            }

            $Engine->assign('phone', $phone);
        }


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
            'submitLabel' => $submitLabel
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/Control.html');
    }

    /**
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
            throw new QUI\Exception('Name is required');
        }

        if (!$hasEmail && !$hasPhone) {
            throw new QUI\Exception('Provide either email or phone');
        }

        if ($hasEmail && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new QUI\Exception('Invalid email');
        }

        $mailer = QUI::getMailManager()->getMailer();
        $recipient = '';
        $brick = null;

        // recipient -> wenn im baustein, dann id vom baustein und email daraus
        if ($this->isInBrick()) {
            try {
                $brick = QUI\Bricks\Manager::init()->getBrickById(
                    (int)$this->getAttribute('data-brickid')
                );

                $recipient = $brick->getAttribute('recipient');
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
            throw new QUI\Exception('Something went wrong. Please try again later.');
        }
    }

    protected function isInBrick(): bool
    {
        return !empty($this->getAttribute('data-brickid'));
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
}
