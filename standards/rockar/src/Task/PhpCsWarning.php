<?php
/**
 * @category  Rockar
 * @package   Rockar_Task
 * @author    Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */

namespace Rockar\Task;

use GrumPHP\Task\PhpCs;

class PhpcsWarning extends Phpcs
{
    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'phpcs_warning';
    }
}
