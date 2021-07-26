<?php

/**
 * CRM_SumfieldsAddon_Service Class
 */
class CRM_SumfieldsAddon_Service
{
    /**
     * Set generate_schema_and_data to scheduled
     * so the summary fields will be (re)generated on the next run
     *
     * @param CRM_Core_DAO_Job $job The executed job
     * @param mixed $result Array returned by the job / Exception that interrupted the execution of the job
     */
    public static function statusToScheduled($job, $result)
    {
        // Check result and select relevant job
        if (!is_array($result) || $job->api_entity !== 'SumFields' || $job->api_action !== 'Gendata') {
            return;
        }

        // Parse status
        $status = $result['values'][0] ?? null;
        if (preg_match('/New Status: ([a-z]+):([0-9 -:]+)$/', $status, $matches)) {
            $status_name = $matches[1];
            $status_date = $matches[2];

            // Check if the last run was successful to avoid loops of failed runs
            if ($status_name == 'success') {
                // Change status to scheduled and save so on the next run the data will be regenerated
                $new_status = 'scheduled:'.$status_date;
                sumfields_save_setting('generate_schema_and_data', $new_status);
            }
        }
    }
}
