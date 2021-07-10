<?php

require_once 'sumfields_addon.civix.php';

// phpcs:disable
use CRM_SumfieldsAddon_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sumfields_addon_civicrm_config(&$config)
{
    _sumfields_addon_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function sumfields_addon_civicrm_xmlMenu(&$files)
{
    _sumfields_addon_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sumfields_addon_civicrm_install()
{
    _sumfields_addon_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function sumfields_addon_civicrm_postInstall()
{
    _sumfields_addon_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function sumfields_addon_civicrm_uninstall()
{
    _sumfields_addon_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sumfields_addon_civicrm_enable()
{
    _sumfields_addon_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function sumfields_addon_civicrm_disable()
{
    _sumfields_addon_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function sumfields_addon_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _sumfields_addon_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function sumfields_addon_civicrm_managed(&$entities)
{
    _sumfields_addon_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function sumfields_addon_civicrm_caseTypes(&$caseTypes)
{
    _sumfields_addon_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function sumfields_addon_civicrm_angularModules(&$angularModules)
{
    _sumfields_addon_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function sumfields_addon_civicrm_alterSettingsFolders(&$metaDataFolders = null)
{
    _sumfields_addon_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function sumfields_addon_civicrm_entityTypes(&$entityTypes)
{
    _sumfields_addon_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function sumfields_addon_civicrm_themes(&$themes)
{
    _sumfields_addon_civix_civicrm_themes($themes);
}

/*
 * Custom Code
 */

/**
 * Implements hook_civicrm_sumfields_definitions()
 */
function sumfields_addon_civicrm_sumfields_definitions(&$custom)
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
    // Number of contributions in the last month
    $custom['fields']['contribution_total_number_1_months'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 1 Month'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 45 days
    $custom['fields']['contribution_total_number_45_days'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 45 Days'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 45 DAY) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 62 days
    $custom['fields']['contribution_total_number_62_days'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 62 Days'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 62 DAY) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 110 days
    $custom['fields']['contribution_total_number_110_days'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 110 Days'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 110 DAY) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 3 months
    $custom['fields']['contribution_total_number_3_months'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 3 Months'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 3 MONTH) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 6 months
    $custom['fields']['contribution_total_number_6_months'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 6 Months'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 6 MONTH) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // Number of contributions in the last 12 months
    $custom['fields']['contribution_total_number_12_months'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Count of Contributions in Last 12 Months'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT COALESCE(COUNT(DISTINCT t1.id), 0) FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN DATE_SUB(NOW(), INTERVAL 12 MONTH) AND NOW() AND
        t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND t1.contribution_status_id = 1 AND
        t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // 30x Total lifetime contributions
    $custom['fields']['30x_contribution_total_lifetime'] = [
        'optgroup' => 'fundraising',
        'label' => ts('30x Total Lifetime Contributions'),
        'data_type' => 'Money',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT (COALESCE(SUM(line_total),0) * 30)
        FROM civicrm_contribution t1 JOIN
        civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND
        t1.contribution_status_id = 1 AND t1.is_test = 0 AND
        t2.financial_type_id IN (%financial_type_ids))',
    ];
    // 30x Total contributions in this year
    $custom['fields']['30x_contribution_total_this_year'] = [
        'optgroup' => 'fundraising',
        'label' => ts('30x Total Contributions this Fiscal Year'),
        'data_type' => 'Money',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT (COALESCE(SUM(line_total),0) * 30)
        FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE CAST(receive_date AS DATE) BETWEEN "%current_fiscal_year_begin"
        AND "%current_fiscal_year_end" AND t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id) AND
        t1.contribution_status_id = 1 AND t2.financial_type_id IN (%financial_type_ids) AND t1.is_test = 0)',
    ];
    // 30x Amount of last contribution
    $custom['fields']['30x_contribution_amount_last'] = [
        'optgroup' => 'fundraising',
        'label' => ts('30x Amount of last contribution'),
        'data_type' => 'Money',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT (COALESCE(total_amount,0) * 30)
        FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id)
        AND t1.contribution_status_id = 1 AND t2.financial_type_id IN
        (%financial_type_ids) AND t1.is_test = 0 ORDER BY t1.receive_date DESC LIMIT 1)',
    ];
    // Number of packages made from last contribution
    $custom['fields']['number_of_packages_last'] = [
        'optgroup' => 'fundraising',
        'label' => ts('Number of packages made from last contribution'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'weight' => '10',
        'text_length' => '32',
        'trigger_table' => 'civicrm_line_item',
        'trigger_sql' => '(SELECT ROUND(COALESCE(total_amount,0) / 56)
        FROM civicrm_contribution t1
        JOIN civicrm_line_item t2 ON t1.id = t2.contribution_id
        WHERE t1.contact_id = (SELECT contact_id FROM civicrm_contribution cc WHERE cc.id = NEW.contribution_id)
        AND t1.contribution_status_id = 1 AND t2.financial_type_id IN
        (%financial_type_ids) AND t1.is_test = 0 ORDER BY t1.receive_date DESC LIMIT 1)',
    ];
}
