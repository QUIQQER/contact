<?php

namespace QUI\Contact\CtaAction;

use QUI;
use QUI\Exception;

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
     * @throws Exception|\PHPMailer\PHPMailer\Exception
     */
    public function send(): void
    {
        $mailer = QUI::getMailManager()->getMailer();

        // recipient -> wenn im baustein, dann id vom baustein und email daraus
        // recipient -> wenn kein baustein, contact erhält standard kontakt mail
        // recipient -> wenn alles nix, dann admin mail

        // $mailer->setSubject();
        // $mailer->setFrom();
        // $mailer->addRecipient();

        $mailer->send();
    }

    protected function isInBrick(): bool
    {
        return !empty($this->getAttribute('data-brickid'));
    }
}
