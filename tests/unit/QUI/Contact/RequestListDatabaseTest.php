<?php

declare(strict_types=1);

namespace QUITests\Contact;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Contact\RequestList;
use ReflectionProperty;

class RequestListDatabaseTest extends TestCase
{
    private Connection $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = QUI::getDataBaseConnection();
        $Connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true
        ]);
        $Schema = new Schema();
        $Forms = $Schema->createTable(RequestList::getFormsTable());
        $Forms->addColumn('id', 'integer', ['autoincrement' => true]);
        $Forms->addColumn('identifier', 'string');
        $Forms->addColumn('title', 'string');
        $Forms->addColumn('dataFields', 'text');
        $Forms->addColumn('projectName', 'string', ['notnull' => false]);
        $Forms->addColumn('projectLang', 'string', ['notnull' => false]);
        $Forms->addColumn('siteId', 'integer', ['notnull' => false]);
        $Forms->setPrimaryKey(['id']);

        $Requests = $Schema->createTable(RequestList::getRequestsTable());
        $Requests->addColumn('id', 'integer', ['autoincrement' => true]);
        $Requests->addColumn('formId', 'integer');
        $Requests->addColumn('submitDate', 'string');
        $Requests->addColumn('submitData', 'text');
        $Requests->setPrimaryKey(['id']);

        foreach ($Schema->toSql($Connection->getDatabasePlatform()) as $statement) {
            $Connection->executeStatement($statement);
        }

        $this->setConnection($Connection);
        $this->insertForm($Connection, 1, 'form-a', 'Contact');
        $this->insertForm($Connection, 2, 'form-b', 'Contact');
        $this->insertForm($Connection, 3, 'form-c', 'Support');
        $this->insertRequest($Connection, 1, 1, '{"message":"alpha"}');
        $this->insertRequest($Connection, 2, 1, '{"message":"beta"}');
        $this->insertRequest($Connection, 3, 2, '{"message":"alpha"}');
    }

    protected function tearDown(): void
    {
        $this->setConnection($this->originalConnection);

        parent::tearDown();
    }

    public function testListsFormsAndDisambiguatesDuplicateTitles(): void
    {
        self::assertSame([
            [
                'id' => 1,
                'identifier' => 'form-a',
                'title' => 'Contact',
                'dataFields' => '[]'
            ],
            [
                'id' => 2,
                'identifier' => 'form-b',
                'title' => 'Contact [1]',
                'dataFields' => '[]'
            ],
            [
                'id' => 3,
                'identifier' => 'form-c',
                'title' => 'Support',
                'dataFields' => '[]'
            ]
        ], RequestList::getForms());

        self::assertSame(2, RequestList::getFormIdByIdentifier('form-b'));
        self::assertFalse(RequestList::getFormIdByIdentifier('missing'));
    }

    public function testFiltersSortsAndPaginatesRequests(): void
    {
        $firstPage = RequestList::getList([
            'id' => 1,
            'page' => 1,
            'perPage' => 1
        ]);
        $secondPage = RequestList::getList([
            'id' => 1,
            'page' => 2,
            'perPage' => 1
        ]);

        self::assertIsArray($firstPage);
        self::assertIsArray($secondPage);
        self::assertSame(2, (int)$firstPage[0]['id']);
        self::assertSame(1, (int)$secondPage[0]['id']);
        self::assertSame(2, RequestList::getList(['id' => 1], true));
        self::assertSame(2, RequestList::getList(['search' => 'alpha'], true));
    }

    public function testTreatsGridLimitAsDataInsteadOfSql(): void
    {
        $result = RequestList::getList([
            'limit' => '0,1; DROP TABLE contact_requests'
        ]);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertSame(3, RequestList::getList([], true));
    }

    public function testDeletesSelectedRequestsAndAcceptsEmptySelection(): void
    {
        RequestList::deleteRequests([]);
        RequestList::deleteRequests([1]);

        self::assertSame(2, RequestList::getList([], true));
        self::assertSame(1, RequestList::getList(['id' => 1], true));
    }

    private function insertForm(Connection $Connection, int $id, string $identifier, string $title): void
    {
        $Connection->insert(RequestList::getFormsTable(), [
            'id' => $id,
            'identifier' => $identifier,
            'title' => $title,
            'dataFields' => '[]',
            'projectName' => null,
            'projectLang' => null,
            'siteId' => null
        ]);
    }

    private function insertRequest(Connection $Connection, int $id, int $formId, string $submitData): void
    {
        $Connection->insert(RequestList::getRequestsTable(), [
            'id' => $id,
            'formId' => $formId,
            'submitDate' => '2026-07-15 08:00:00',
            'submitData' => $submitData
        ]);
    }

    private function setConnection(Connection $Connection): void
    {
        $QueryBuilder = new ReflectionProperty(QUI::class, 'QueryBuilder');
        $QueryBuilder->setValue(null, $Connection);
    }
}
