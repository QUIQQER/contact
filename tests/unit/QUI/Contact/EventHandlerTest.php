<?php

declare(strict_types=1);

namespace QUITests\Contact;

use PHPUnit\Framework\TestCase;
use QUI\Contact\EventHandler;
use QUI\Projects\Site;
use QUI\Projects\Site\Edit;

class EventHandlerTest extends TestCase
{
    public function testPersistsDefaultSuccessValuesOnEditableSite(): void
    {
        $savedAttributes = [];
        $SiteEdit = $this->createMock(Edit::class);
        $SiteEdit->expects(self::exactly(2))
            ->method('setAttribute')
            ->willReturnCallback(
                static function (string $name, mixed $value) use (&$savedAttributes): void {
                    $savedAttributes[$name] = $value;
                }
            );
        $SiteEdit->expects(self::once())->method('save');

        $Site = $this->createMock(Site::class);
        $Site->method('getEdit')->willReturn($SiteEdit);
        $Site->method('getAttribute')->willReturnCallback(
            static fn(string $name): mixed => match ($name) {
                'type' => 'quiqqer/contact:types/contact',
                'quiqqer.contact.settings.form',
                'quiqqer.contact.success',
                'quiqqer.contact.success_mail' => null,
                default => null
            }
        );
        $Site->expects(self::never())->method('setAttribute');

        EventHandler::onSiteSave($Site);

        self::assertArrayHasKey('quiqqer.contact.success', $savedAttributes);
        self::assertArrayHasKey('quiqqer.contact.success_mail', $savedAttributes);
        $successMail = json_decode((string)$savedAttributes['quiqqer.contact.success_mail'], true);
        self::assertFalse($successMail['send']);
        self::assertNotEmpty($successMail['subject']);
        self::assertNotEmpty($successMail['body']);
    }
}
