<?php
/**
 * @category  Peppermint
 * @package   Peppermint_Importer
 * @author    Jevgenijs Goreliks <techteam@rockar.com>
 * @copyright Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

class Peppermint_Importer_Model_ModelMatrixMapping extends Mage_Core_Model_Abstract
{
    /**
     * Track imported models to remove outdated ones
     *
     * @var array
     */
    protected $_importedModels = [];

    /**
     * Callback method that will receive queue message and sending to be processed.
     *
     * @param string $message
     * @return $this
     * @throws Exception
     */
    public function processMatrixMapping(string $message)
    {
        $messageData = json_decode($message, true);
        $mappingCollection = Mage::getModel('peppermint_catalog/matrixMapping')->getCollection();

        // Reset imported models list
        $this->_importedModels = [];

        foreach ($messageData as $data) {
            if (!empty($data)) {
                $model = Mage::getModel('peppermint_catalog/matrixMapping')->load($data['matrix']);

                if (!$model->getData('model_matrix_carousel')) {
                    $existingRecord = $mappingCollection->addFieldToFilter('model_carousel', $data['series'])
                        ->getFirstItem();

                    if ($existingRecord->getPosition()) {
                        $model->addData([
                            'position' => $existingRecord->getPosition(),
                        ]);
                    }

                    $mappingCollection->clear()->getSelect()->reset(\Zend_Db_Select::WHERE);
                }

                $model->addData([
                    'model_matrix_carousel' => $data['matrix'],
                    'model_carousel' => $data['series'],
                    'brand' => $data['brand']
                ]);

                $runOutDate = \DateTime::createFromFormat('Ymd', $data['runOutDate']);
                $savedRunOutDate = \DateTime::createFromFormat('Y-m-d', $model->getRunOutDate());

                if ($savedRunOutDate < $runOutDate) {
                    $model->addData(['run_out_date' => $runOutDate->format('Y-m-d')]);
                }

                $model->save();

                $this->_importedModels[] = $data['matrix'];
            }
        }

        // Remove models that are not present in import message
        $outdatedModels = Mage::getModel('peppermint_catalog/matrixMapping')->getCollection()
            ->addFieldToSelect('model_matrix_carousel')
            ->addFieldToFilter('model_matrix_carousel', ['nin' => $this->_importedModels]);

        foreach ($outdatedModels as $outdatedModel) {
            $outdatedModel->delete();
        }


        return $this;
    }
}
