<?php

use Iresults\Avif\Core\Filter\FileNameFilter;
use Iresults\Avif\Service\Configuration;

defined('TYPO3') || exit;

(static function () {
    if (Configuration::get('hide_avif')) {
        // Hide avif files in file lists
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['defaultFilterCallbacks'][] = [
            FileNameFilter::class,
            'filterAvifFiles',
        ];
    }
})();
