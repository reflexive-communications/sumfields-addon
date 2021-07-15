<?php

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
        sumfields_gen_data($results);

        self::assertCount(1, $results);
        self::assertStringContainsString('New Status: success', $results[0], 'Failed to enable summary field');
    }

    public function testContributionLargestLast12Months()
    {
        $fields = ['contribution_largest_last_12_months'];
        $this->enableSummaryField($fields);
        self::markTestIncomplete('jocogeza');
    }
}
