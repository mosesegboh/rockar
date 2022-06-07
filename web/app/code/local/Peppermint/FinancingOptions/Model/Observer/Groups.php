<?php
/**
 * @category  Peppermint
 * @package   Peppermint_FinancingOptions
 * @author    Kalvis Ostrovskis <techteam@rockar.com>
 * @copyright Copyright (c) 2021 Rockar, Ltd (http://rockar.com)
 */

/**
 * Class Peppermint_FinancingOptions_Model_Observer_Groups
 */
class Peppermint_FinancingOptions_Model_Observer_Groups extends Rockar2_FinancingOptions_Model_Observer_Groups
{
    /**
     * @inheritDoc
     *
     * Overwritten to include header and footer in the select
     */
    public function addDataToFinanceGroups(Varien_Event_Observer $observer)
    {
        /** @var Rockar_FinancingOptions_Model_Resource_Group_Collection $collection */
        $collection = $observer->getFinanceGroupsCollection();

        $optionsIds = $observer->getFinanceOptionsIds() ?: [0];

        $collection->getSelect()
            ->joinLeft(
                ['options' => 'rockar_financing_options'],
                sprintf('options.group_id=main_table.group_id AND options.options_id IN (%s)', implode(',', $optionsIds)),
                ['video' => 'options.video', 'header' => 'options.header', 'footer' => 'options.footer']
            )->group('main_table.group_id');

        return $this;
    }
}
