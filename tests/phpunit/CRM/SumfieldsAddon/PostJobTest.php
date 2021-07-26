<?php

use Civi\Test;
use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * postJob Hook Test
 *
 * @group headless
 */
class CRM_SumfieldsAddon_PostJobTest extends TestCase implements HeadlessInterface
{
    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     */
    public static function setUpBeforeClass(): void
    {
        // Reset DB and install depended extensions
        // Don't install sumfields-addon extension yet
        Test::headless()
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
     * @throws \CRM_Extension_Exception_ParseException
     * @throws \CiviCRM_API3_Exception
     */
    public function testHook()
    {
        // Set job status to scheduled so it will run
        sumfields_save_setting('generate_schema_and_data', 'scheduled:'.date('Y-m-d H:i:s'));
        $status_no_hook_init = preg_replace('/:\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', '', sumfields_get_setting('generate_schema_and_data'));
        self::assertSame('scheduled', $status_no_hook_init, 'Failed to set status');

        // Execute scheduled jobs without postJob hook
        $result = civicrm_api3('Job', 'execute');
        self::assertSame(0, $result['is_error'], 'Failed to execute scheduled jobs');

        // Check status after execution
        $status_no_hook = preg_replace('/:\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', '', sumfields_get_setting('generate_schema_and_data'));
        self::assertSame('success', $status_no_hook, 'Job was not successful');

        // Install extension, so hook will be called
        Test::headless()
            ->installMe(__DIR__)
            ->install('net.ourpowerbase.sumfields')
            ->apply();

        // Set job status to scheduled so it will run
        sumfields_save_setting('generate_schema_and_data', 'scheduled:'.date('Y-m-d H:i:s'));
        $status_hook_init = preg_replace('/:\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', '', sumfields_get_setting('generate_schema_and_data'));
        self::assertSame('scheduled', $status_hook_init, 'Failed to set status');

        // Execute scheduled jobs with postJob hook
        $result = civicrm_api3('Job', 'execute');
        self::assertSame(0, $result['is_error'], 'Failed to execute scheduled jobs');

        // Check status after execution
        $status_hook = preg_replace('/:\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', '', sumfields_get_setting('generate_schema_and_data'));
        self::assertSame('scheduled', $status_hook, 'Failed to change status');
    }
}
