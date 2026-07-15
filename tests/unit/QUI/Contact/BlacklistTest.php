<?php

declare(strict_types=1);

namespace QUITests\Contact;

use PHPUnit\Framework\TestCase;
use QUI\Contact\Blacklist;
use ReflectionMethod;
use ReflectionProperty;

class BlacklistTest extends TestCase
{
    /** @var array<string, mixed>|null */
    private ?array $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $Config = new ReflectionProperty(Blacklist::class, 'conf');
        $this->originalConfig = $Config->getValue();
        $Config->setValue(null, [
            'ipAddresses' => json_encode([
                '0.0.0.0',
                '192.0.2.10',
                '198.51.100.1-198.51.100.10'
            ]),
            'emailAddresses' => json_encode([
                'blocked@example.com',
                '*@spam.example',
                'sales*@example.com'
            ]),
            'useDNSBL' => false,
            'DNSBLProviders' => '[]'
        ]);
    }

    protected function tearDown(): void
    {
        $Config = new ReflectionProperty(Blacklist::class, 'conf');
        $Config->setValue(null, $this->originalConfig);

        parent::tearDown();
    }

    public function testBuildsDnsblHostnameExactlyOnce(): void
    {
        $buildDnsblHost = new ReflectionMethod(Blacklist::class, 'buildDnsblHost');

        self::assertSame(
            '4.3.2.1.zen.spamhaus.org.',
            $buildDnsblHost->invoke(null, '1.2.3.4', 'zen.spamhaus.org')
        );
        self::assertSame(
            '4.3.2.1.zen.spamhaus.org.',
            $buildDnsblHost->invoke(null, '1.2.3.4', 'zen.spamhaus.org.')
        );
    }

    public function testChecksExactAndRangeIpEntries(): void
    {
        self::assertTrue(Blacklist::isIpBlacklistedByIpList('0.0.0.0'));
        self::assertTrue(Blacklist::isIpBlacklistedByIpList('192.0.2.10'));
        self::assertTrue(Blacklist::isIpBlacklistedByIpList('198.51.100.5'));
        self::assertFalse(Blacklist::isIpBlacklistedByIpList('203.0.113.1'));
        self::assertFalse(Blacklist::isIpBlacklistedByIpList('invalid-address'));
    }

    public function testChecksExactAndWildcardEmailEntries(): void
    {
        self::assertTrue(Blacklist::isEmailAddressBlacklisted('blocked@example.com'));
        self::assertTrue(Blacklist::isEmailAddressBlacklisted('person@spam.example'));
        self::assertTrue(Blacklist::isEmailAddressBlacklisted('sales-team@example.com'));
        self::assertFalse(Blacklist::isEmailAddressBlacklisted('presales@example.com'));
        self::assertFalse(Blacklist::isEmailAddressBlacklisted('person@example.com'));
        self::assertFalse(Blacklist::isEmailAddressBlacklisted('invalid-address'));
    }

    public function testDnsblCheckIsDisabledByConfiguration(): void
    {
        self::assertFalse(Blacklist::isIpBlacklistedByDNSBL('1.2.3.4'));
    }
}
