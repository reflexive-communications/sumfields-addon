<?php

use Civi\Api4\Contact;
use Civi\Api4\Contribution;
use Civi\Api4\CustomField;
use Civi\Test;
use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * Sumfield Definitions Test
 *
 * @group headless
 */
class CRM_SumfieldsAddon_DefinitionsTest extends TestCase implements HeadlessInterface
{
    /**
     * Contact sequence counter
     *
     * @var int
     */
    protected static $contactSequence = 0;

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Reset DB and install depended extensions
        // Install sumfields-addon first so when installing net.ourpowerbase.sumfields and calling hook_civicrm_sumfields_definitions()
        // sumfields-addon is already installed and sumfields_addon_civicrm_sumfields_definitions() gets called
        Test::headless()
            ->installMe(__DIR__)
            ->install('net.ourpowerbase.sumfields')
            ->apply(true);
    }

    /**
     * The setupHeadless function runs at the start of each test case, right before
     * the headless environment reboots.
     *
     * It should perform any necessary steps required for putting the database
     * in a consistent baseline -- such as loading schema and extensions.
     *
     * The utility `\Civi\Test::headless()` provides a number of helper functions
     * for managing this setup, and it includes optimizations to avoid redundant
     * setup work.
     *
     * @see \Civi\Test
     */
    public function setUpHeadless()
    {
    }

    /**
     * Get next contact sequence number (auto-increment)
     *
     * @return int Next ID
     */
    protected static function getNextContactSequence(): int
    {
        self::$contactSequence++;

        return self::$contactSequence;
    }

    /**
     * Create contact
     *
     * @return int Contact ID
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function createContact(): int
    {
        $result = Contact::create()
            ->addValue('contact_type', 'Individual')
            ->addValue('first_name', 'user_'.self::getNextContactSequence())
            ->execute();
        self::assertCount(1, $result, 'Failed to create contact');
        $contact = $result->first();
        self::assertArrayHasKey('id', $contact, 'Contact ID not found');
        return (int)$contact['id'];
    }

    /**
     * Add contribution
     *
     * @param int $contact_id Contact ID
     * @param float $amount Total amount
     * @param string $date Receive date
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function addContribution(int $contact_id, float $amount, string $date = ''): void
    {
        $result = Contribution::create()
            ->addValue('contact_id', $contact_id)
            ->addValue('financial_type_id', 1)
            ->addValue('total_amount', $amount)
            ->addValue('receive_date', $date)
            ->execute();
        self::assertCount(1, $result, 'Failed to create contribution');
        self::assertArrayHasKey('id', $result->first(), 'Contribution ID not found');
    }

    /**
     * Get custom field value
     *
     * @param int $entity_id Entity ID
     * @param string $custom_field_label Custom field label
     *
     * @return mixed Raw field value
     *
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function getCustomFieldValue(int $entity_id, string $custom_field_label)
    {
        // Get custom field ID from label
        $result = CustomField::get()
            ->addSelect('id')
            ->addWhere('label', '=', $custom_field_label)
            ->setLimit(1)
            ->execute();
        self::assertCount(1, $result, 'Failed to find custom field');
        $custom_field_id = $result->first()['id'];

        // Get field value
        $result = civicrm_api3('CustomValue', 'getdisplayvalue', [
            'entity_id' => $entity_id,
            'custom_field_id' => $custom_field_id,
        ]);
        self::assertCount(1, $result['values'], 'Failed to find custom field value');
        return (array_shift($result['values']))['raw'];
    }

    /**
     * Enable individual summary fields
     *
     * @param array $fields List of fields to enable
     */
    protected function enableSummaryField(array $fields)
    {
        // Save fields
        sumfields_save_setting('new_active_fields', $fields);

        // Regenerate fields
        $results = [];
        sumfields_save_setting('generate_schema_and_data', 'scheduled:'.date('Y-m-d H:i:s'));
        self::assertTrue(sumfields_gen_data($results), 'Failed to generate data');

        self::assertCount(1, $results);
        self::assertStringContainsString('New Status: success', $results[0], 'Failed to enable summary field');
    }

    /**
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testContributionLargestLast12Months()
    {
        // Enable fields
        $fields = ['contribution_largest_last_12_months'];
        $this->enableSummaryField($fields);

        // Add contributions
        $contact_id = $this->createContact();
        $amount_1 = 1500;
        $amount_2 = 1200;
        $amount_3 = 120;
        $date_1 = new DateTime();
        $date_1->sub(DateInterval::createFromDateString('15 months'));
        $date_2 = new DateTime();
        $date_2->sub(DateInterval::createFromDateString('8 months'));
        $date_3 = new DateTime();
        $date_3->sub(DateInterval::createFromDateString('7 months'));
        $this->addContribution($contact_id, $amount_1, $date_1->format('Y-m-d'));
        $this->addContribution($contact_id, $amount_2, $date_2->format('Y-m-d'));
        $this->addContribution($contact_id, $amount_3, $date_3->format('Y-m-d'));

        // Check value
        $value = $this->getCustomFieldValue($contact_id, 'Largest Contribution in the last 12 Months');
        self::assertEquals($amount_2, $value, 'Wrong value returned');
    }

    /**
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testNumberOfContributionsInLastIntervals()
    {
        // Enable fields
        $fields = [
            'contribution_total_number_1_months',
            'contribution_total_number_45_days',
            'contribution_total_number_62_days',
            'contribution_total_number_110_days',
            'contribution_total_number_3_months',
            'contribution_total_number_6_months',
            'contribution_total_number_12_months',
        ];
        $this->enableSummaryField($fields);

        // Add contributions
        $contact_id = $this->createContact();
        $amount = 100;
        $date_now = new DateTime();
        $date_15_days = new DateTime();
        $date_15_days->sub(DateInterval::createFromDateString('15 days'));
        $date_40_days = new DateTime();
        $date_40_days->sub(DateInterval::createFromDateString('40 days'));
        $date_60_days = new DateTime();
        $date_60_days->sub(DateInterval::createFromDateString('60 days'));
        $date_75_days = new DateTime();
        $date_75_days->sub(DateInterval::createFromDateString('75 days'));
        $date_111_days = new DateTime();
        $date_111_days->sub(DateInterval::createFromDateString('111 days'));
        $date_4_months = new DateTime();
        $date_4_months->sub(DateInterval::createFromDateString('4 months'));
        $date_7_months = new DateTime();
        $date_7_months->sub(DateInterval::createFromDateString('7 months'));
        $date_13_months = new DateTime();
        $date_13_months->sub(DateInterval::createFromDateString('13 months'));

        $this->addContribution($contact_id, $amount, $date_now->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_15_days->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_40_days->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_60_days->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_75_days->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_111_days->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_4_months->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_7_months->format('Y-m-d'));
        $this->addContribution($contact_id, $amount, $date_13_months->format('Y-m-d'));

        // Check value
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 1 Month');
        self::assertEquals(2, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 45 Days');
        self::assertEquals(3, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 62 Days');
        self::assertEquals(4, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 3 Months');
        self::assertEquals(5, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 110 Days');
        self::assertEquals(5, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 6 Months');
        self::assertEquals(7, $value, 'Wrong value returned');
        $value = $this->getCustomFieldValue($contact_id, 'Count of Contributions in Last 12 Months');
        self::assertEquals(8, $value, 'Wrong value returned');
    }
}
