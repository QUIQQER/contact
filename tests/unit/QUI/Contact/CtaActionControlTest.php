<?php

declare(strict_types=1);

namespace QUITests\Contact;

use PHPUnit\Framework\TestCase;
use QUI\Contact\CtaAction\Control;

class CtaActionControlTest extends TestCase
{
    public function testContactButtonsDoNotOverwriteFormFieldLabels(): void
    {
        $Control = new class ([
            'title' => 'Contact',
            'description' => 'Description',
            'content' => '<p>Content</p>',
            'name_label' => 'Name',
            'name_placeholder' => 'Name',
            'company_label' => 'Company',
            'company_placeholder' => 'Company',
            'email_label' => 'Form email label',
            'email_placeholder' => 'Email',
            'phone_label' => 'Form phone label',
            'phone_placeholder' => 'Phone',
            'message_label' => 'Message',
            'message_placeholder' => 'Message',
            'submit_label' => 'Send',
            'email' => 'a&b@example.com',
            'emailLabel' => 'Email button',
            'phone' => '+49 123 456',
            'phoneLabel' => 'Phone button',
            'btnStyle' => 'button'
        ]) extends Control {
            public function getPrivacyLink(): string
            {
                return '';
            }
        };

        $html = $Control->getBody();

        self::assertStringContainsString('Form email label', $html);
        self::assertStringContainsString('Form phone label', $html);
        self::assertStringContainsString('Email button', $html);
        self::assertStringContainsString('Phone button', $html);
        self::assertStringContainsString('mailto:a&amp;b@example.com', $html);
        self::assertStringNotContainsString('mailto:a&amp;amp;b@example.com', $html);
    }
}
