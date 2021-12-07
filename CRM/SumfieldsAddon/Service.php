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

    /**
     * Adds extra summary fields
     *
     * @param array $custom Summary fields
     */
    public static function summaryFieldDefinitions(&$custom): void
    {
        // Largest contribution in the last 12 months
        $custom['fields']['contribution_largest_last_12_months'] = [
            'optgroup' => 'fundraising',
            'label' => ts('Largest Contribution in the last 12 Months'),
            'data_type' => 'Money',
            'html_type' => 'Text',
            'weight' => '10',
            'text_length' => '32',
            'trigger_table' => 'civicrm_line_item',
            'trigger_sql' => '(SELECT COALESCE(MAX(total_amount), 0)
            FROM civicrm_contribution t1
            JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
            WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 12 MONTH) AND NOW() AND
            t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND
            t1.contribution_status_id = 1 AND t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
        ];
        // Number of contributions in the last X days
        $last_days = [45, 62, 110];
        foreach ($last_days as $day) {
            $custom['fields']['contribution_total_number_'.$day.'_days'] = [
                'optgroup' => 'fundraising',
                'label' => ts('Count of Contributions in Last '.$day.' Days'),
                'data_type' => 'Int',
                'html_type' => 'Text',
                'weight' => '10',
                'text_length' => '32',
                'trigger_table' => 'civicrm_line_item',
                'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
                JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
                WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL '.$day.' DAY) AND NOW() AND
                t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
                t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
            ];
        }

        // Number of contributions in the last X month
        $last_months = [1, 3, 6, 12];
        foreach ($last_months as $month) {
            $custom['fields']['contribution_total_number_'.$month.'_months'] = [
                'optgroup' => 'fundraising',
                'label' => ts('Count of Contributions in Last '.$month.' Months'),
                'data_type' => 'Int',
                'html_type' => 'Text',
                'weight' => '10',
                'text_length' => '32',
                'trigger_table' => 'civicrm_line_item',
                'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
                JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
                WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL '.$month.' MONTH) AND NOW() AND
                t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
                t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
            ];
        }
        // Number of contributions in the last 2 years
        $custom['fields']['contribution_total_number_2_years'] = [
            'optgroup' => 'fundraising',
            'label' => ts('Count of Contributions in Last 2 Years'),
            'data_type' => 'Int',
            'html_type' => 'Text',
            'weight' => '10',
            'text_length' => '32',
            'trigger_table' => 'civicrm_line_item',
            'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
            JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
            WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 2 YEAR) AND NOW() AND
            t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
            t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
        ];
    }
}
