<?php

/**
 * @category     Peppermint
 * @package      Peppermint\AschroderEmail
 * @author       Catalin Lungu <catalin.lungu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

$this->startSetup();
Mage::getModel('core/config')->saveConfig('aschroder_email/general/bypass_queue', 1)
    ->cleanCache();
$this->endSetup();
