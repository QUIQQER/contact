<?php

namespace QUI\Contact;

use DateTime;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception as DBALException;
use QUI;
use QUI\Exception;
use QUI\Security\Encryption;
use QUI\Utils\Doctrine;
use QUI\Utils\Grid;

use function array_map;
use function json_decode;

/**
 * Class RequestList
 *
 * Manages single submitted form requests that are saved in the database
 */
class RequestList
{
    /**
     * Save a form request to the database
     *
     * @param QUI\FormBuilder\Interfaces\Field[] $formFields - The form fields submit data
     * @param QUI\Interfaces\Projects\Site $FormSite - The Site the form was submitted from
     * @return void
     *
     * @throws QUI\Exception
     */
    public static function saveFormRequest(array $formFields, QUI\Interfaces\Projects\Site $FormSite): void
    {
        $Now = new DateTime();
        $submitData = [];
        $Conf = QUI::getPackage('quiqqer/contact')->getConfig();
        $encrypt = boolval($Conf?->get('settings', 'encryptContactRequests'));

        foreach ($formFields as $FormField) {
            $submitData[$FormField->getName()] = $FormField->getValueText();
        }

        $formId = self::getFormIdByIdentifier(self::getFormIdentifier($FormSite));

        if (!$formId) {
            throw new ContactException([
                'quiqqer/contact',
                'exception.RequestList.no_form_id'
            ]);
        }

        $submitData = json_encode($submitData);

        if ($encrypt) {
            $submitData = Encryption::encrypt((string)$submitData);
        }

        QUI::getDataBaseConnection()->insert(
            Doctrine::quoteIdentifier(self::getRequestsTable()),
            [
                'formId' => $formId,
                'submitDate' => $Now->format('Y-m-d H:i:s'),
                'submitData' => $submitData,
            ]
        );
    }

    /**
     * Get all forms that save requests
     *
     * @return array<int, array{title: string, identifier: string, dataFields: mixed, id: int|string}>
     * @throws DBALException
     */
    public static function getForms(): array
    {
        $Connection = QUI::getDataBaseConnection();
        $result = $Connection->createQueryBuilder()
            ->select(
                Doctrine::quoteIdentifier('id'),
                Doctrine::quoteIdentifier('identifier'),
                Doctrine::quoteIdentifier('title'),
                Doctrine::quoteIdentifier('dataFields')
            )
            ->from(Doctrine::quoteIdentifier(self::getFormsTable()))
            ->executeQuery()
            ->fetchAllAssociative();

        //$parsed = [];

        /** @var array<string, int> $parsedTitles */
        $parsedTitles = [];
        $forms = [];

        foreach ($result as $row) {
            $title = $row['title'];
            $titleHash = md5($title);
            //$identifier = (string)$row['identifier'];

            //if (isset($parsed[$identifier])) {
            //    continue;
            //}

            if (!isset($parsedTitles[$titleHash])) {
                $parsedTitles[$titleHash] = 0;
            }

            $parsedTitles[$titleHash]++;

            if ($parsedTitles[$titleHash] > 1) {
                $title .= ' [' . ($parsedTitles[$titleHash] - 1) . ']';
            }

            $forms[] = [
                'id' => (int)$row['id'],
                'identifier' => (string)$row['identifier'],
                'title' => $title,
                'dataFields' => $row['dataFields']
            ];
        }

        return $forms;
    }

    /**
     * Get the request list
     *
     * @param array<string, mixed> $searchParams
     * @param bool $countOnly
     * @return array<int, array<string, mixed>>|int
     * @throws Exception
     */
    public static function getList(array $searchParams, bool $countOnly = false): array|int
    {
        $Grid = new Grid($searchParams);
        $gridParams = $Grid->parseDBParams($searchParams);
        $Conf = QUI::getPackage('quiqqer/contact')->getConfig();
        $encrypt = boolval($Conf?->get('settings', 'encryptContactRequests'));

        $Connection = QUI::getDataBaseConnection();
        $QueryBuilder = $Connection->createQueryBuilder();
        $QueryBuilder->from(Doctrine::quoteIdentifier(self::getRequestsTable()));

        if ($countOnly) {
            $QueryBuilder->select('COUNT(*)');
        } else {
            $QueryBuilder->select('*');
        }

        if (!empty($searchParams['id'])) {
            $QueryBuilder
                ->andWhere(Doctrine::quoteIdentifier('formId') . ' = :formId')
                ->setParameter('formId', (int)$searchParams['id']);
        }

        if (!empty($searchParams['search'])) {
            $searchValue = (string)$searchParams['search'];
            $QueryBuilder
                ->andWhere(Doctrine::quoteIdentifier('submitData') . ' LIKE :search')
                ->setParameter('search', '%' . $searchValue . '%');
        }

        if (!$countOnly) {
            [$offset, $limit] = self::parseGridLimit($gridParams['limit'] ?? null);
            $QueryBuilder
                ->orderBy(Doctrine::quoteIdentifier('id'), 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        try {
            $Result = $QueryBuilder->executeQuery();
        } catch (DBALException $Exception) {
            QUI\System\Log::addError(
                self::class . ' :: search() -> ' . $Exception->getMessage()
            );

            return [];
        }

        if ($countOnly) {
            return (int)$Result->fetchOne();
        }

        $result = $Result->fetchAllAssociative();

        if ($encrypt) {
            foreach ($result as $k => $row) {
                $submitData = (string)$row['submitData'];

                if (!self::isJSON($submitData)) {
                    $result[$k]['submitData'] = Encryption::decrypt($submitData);
                }
            }
        }

        return $result;
    }

    /**
     * Check if a string is in JSON format
     *
     * @param string $str
     * @return bool
     */
    protected static function isJSON(string $str): bool
    {
        $str = json_decode($str, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($str);
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected static function parseGridLimit(mixed $gridLimit): array
    {
        $offset = 0;
        $limit = 20;

        if (is_string($gridLimit) && str_contains($gridLimit, ',')) {
            [$gridOffset, $gridMax] = explode(',', $gridLimit, 2);
            $offset = max(0, (int)$gridOffset);
            $limit = max(1, (int)$gridMax);
        } elseif (is_numeric($gridLimit)) {
            $limit = max(1, (int)$gridLimit);
        }

        return [$offset, $limit];
    }

    /**
     * Delete contact requests
     *
     * @param array<int> $requestIds
     * @return void
     * @throws DBALException
     */
    public static function deleteRequests(array $requestIds): void
    {
        $requestIds = array_map('intval', $requestIds);

        if ($requestIds === []) {
            return;
        }

        $Connection = QUI::getDataBaseConnection();

        foreach ($requestIds as $requestId) {
            try {
                $requestData = $Connection->createQueryBuilder()
                    ->select(Doctrine::quoteIdentifier('formId'), Doctrine::quoteIdentifier('submitData'))
                    ->from(Doctrine::quoteIdentifier(self::getRequestsTable()))
                    ->where(Doctrine::quoteIdentifier('id') . ' = :requestId')
                    ->setParameter('requestId', $requestId)
                    ->executeQuery()
                    ->fetchAssociative();

                if ($requestData === false) {
                    continue;
                }

                $formData = $Connection->createQueryBuilder()
                    ->select('*')
                    ->from(Doctrine::quoteIdentifier(self::getFormsTable()))
                    ->where(Doctrine::quoteIdentifier('id') . ' = :formId')
                    ->setParameter('formId', (int)$requestData['formId'])
                    ->executeQuery()
                    ->fetchAssociative();

                if ($formData === false) {
                    continue;
                }

                if (
                    empty($formData['projectName']) ||
                    empty($formData['projectLang']) ||
                    empty($formData['siteId'])
                ) {
                    continue;
                }

                $Project = QUI::getProject($formData['projectName'], $formData['projectLang']);
                $Site = $Project->get($formData['siteId']);

                QUI::getEvents()->fireEvent(
                    'quiqqerContactDeleteFormRequest',
                    [
                        $requestId,
                        json_decode($requestData['submitData'], true),
                        $Site
                    ]
                );
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $QueryBuilder = $Connection->createQueryBuilder();
        $QueryBuilder
            ->delete(Doctrine::quoteIdentifier(self::getRequestsTable()))
            ->where($QueryBuilder->expr()->in(Doctrine::quoteIdentifier('id'), ':requestIds'))
            ->setParameter('requestIds', $requestIds, ArrayParameterType::INTEGER)
            ->executeStatement();
    }

    /**
     * Get unique form identifier of a quiqqer/contact Site
     *
     * @param QUI\Interfaces\Projects\Site $Site
     * @return string
     */
    public static function getFormIdentifier(QUI\Interfaces\Projects\Site $Site): string
    {
        $Project = $Site->getProject();
        $formData = $Site->getAttribute('quiqqer.contact.settings.form');

        if (empty($formData)) {
            $formHash = '';
        } else {
            $formData = json_decode($formData, true);
            $hashData = [];

            foreach ($formData['elements'] as $element) {
                $hashData[] = $element['type'];
            }

            $formHash = json_encode($hashData);
        }

        $identifierParts = [
            $Site->getId(),
            $Project->getName(),
            $Project->getLang(),
            $formHash
        ];

        return hash('sha256', implode('', $identifierParts));
    }

    /**
     * Get contact form ID by identifier
     *
     * @param string $identifier
     * @return int|false - ID if found; false if not found
     * @throws DBALException
     */
    public static function getFormIdByIdentifier(string $identifier): bool | int
    {
        $result = QUI::getDataBaseConnection()->createQueryBuilder()
            ->select(Doctrine::quoteIdentifier('id'))
            ->from(Doctrine::quoteIdentifier(self::getFormsTable()))
            ->where(Doctrine::quoteIdentifier('identifier') . ' = :identifier')
            ->setParameter('identifier', $identifier)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        if ($result === false) {
            return false;
        }

        return (int)$result;
    }

    /**
     * Get the table where forms are saved
     *
     * @return string
     */
    public static function getFormsTable(): string
    {
        return QUI::getDBTableName('quiqqer_contact_forms');
    }

    /**
     * Get the table where requests are saved
     *
     * @return string
     */
    public static function getRequestsTable(): string
    {
        return QUI::getDBTableName('quiqqer_contact_requests');
    }
}
