<?php

declare(strict_types=1);

namespace QUITests\Contact;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Contact\Handler;
use QUI\FormBuilder\Builder;
use QUI\FormBuilder\Fields\EMail;
use QUI\FormBuilder\Interfaces\Field;
use QUI\Mail\Mailer;
use QUI\Mail\Manager;
use QUI\Projects\Site;

class HandlerTest extends TestCase
{
    private ?Manager $originalMailManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalMailManager = QUI::$MailManager;
    }

    protected function tearDown(): void
    {
        QUI::$MailManager = $this->originalMailManager;

        parent::tearDown();
    }

    public function testSendsAdministratorMailWithReplyToAddresses(): void
    {
        $Mailer = $this->createMock(Mailer::class);
        $Mailer->expects(self::once())
            ->method('addRecipient')
            ->with('admin@example.com', 'Admin');
        $Mailer->expects(self::exactly(2))
            ->method('addReplyTo')
            ->willReturnCallback(static function (string $email): void {
                self::assertContains($email, ['first@example.com', 'second@example.com']);
            });
        $Mailer->expects(self::once())->method('setSubject')->with('New contact request');
        $Mailer->expects(self::once())->method('setBody')->with('<p>Request body</p>');
        $Mailer->expects(self::once())->method('send')->willReturn(true);
        $this->setMailer($Mailer);

        $Email = $this->createMock(EMail::class);
        $Email->method('getAttribute')->with('data')->willReturn([
            'first@example.com',
            'invalid-address',
            'second@example.com'
        ]);
        $Form = $this->createMock(Builder::class);
        $Form->method('getAddresses')->willReturn([[
            'email' => 'admin@example.com',
            'name' => 'Admin'
        ]]);
        $Form->method('getElements')->willReturn([$Email]);
        $Form->method('getMailSubject')->willReturn('New contact request');
        $Form->method('getMailBody')->willReturn('<p>Request body</p>');

        Handler::sendFormAdminMails($Form);
    }

    public function testSendsSuccessMailWithResolvedPlaceholders(): void
    {
        $Mailer = $this->createMock(Mailer::class);
        $Mailer->expects(self::once())->method('addRecipient')->with('user@example.com');
        $Mailer->expects(self::once())->method('setSubject')->with('Thank you');
        $Mailer->expects(self::once())
            ->method('setBody')
            ->with('Email=user@example.com; Name=Alice');
        $Mailer->expects(self::once())->method('send')->willReturn(true);
        $this->setMailer($Mailer);

        $Email = $this->createMock(EMail::class);
        $Email->method('getAttribute')->willReturnCallback(
            static fn(string $name): mixed => match ($name) {
                'data' => 'user@example.com',
                'label' => 'Email',
                default => null
            }
        );
        $Email->method('getValueText')->willReturn('user@example.com');
        $Name = $this->createMock(Field::class);
        $Name->method('getAttribute')->with('label')->willReturn('Name');
        $Name->method('getValueText')->willReturn('Alice');
        $Form = $this->createMock(Builder::class);
        $Form->method('getElements')->willReturn([$Email, $Name]);
        $Site = $this->createMock(Site::class);
        $Site->method('getAttribute')
            ->with('quiqqer.contact.success_mail')
            ->willReturn(json_encode([
                'send' => true,
                'subject' => 'Thank you',
                'body' => '{{label0}}={{value0}}; {{label1}}={{value1}}'
            ]));

        Handler::sendFormSuccessMail($Form, $Site);
    }

    private function setMailer(Mailer $Mailer): void
    {
        $Manager = $this->createMock(Manager::class);
        $Manager->method('getMailer')->willReturn($Mailer);
        QUI::$MailManager = $Manager;
    }
}
