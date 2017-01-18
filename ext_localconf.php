<?php

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Citkomm\CitkoNewsMediamigrate\Command\MediamigrateCommandController';
}

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Citkomm']['CitkoNewsMediamigrate']['writerConfiguration'] = array(
    \TYPO3\CMS\Core\Log\LogLevel::INFO => array(
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            'logFile' => 'typo3temp/logs/CitkoNewsMediamigrate.log'
        ),
    ),
    \TYPO3\CMS\Core\Log\LogLevel::WARNING => array(
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            'logFile' => 'typo3temp/logs/CitkoNewsMediamigrate.log'
        ),
    ),
    \TYPO3\CMS\Core\Log\LogLevel::ERROR => array(
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            'logFile' => 'typo3temp/logs/CitkoNewsMediamigrate.log'
        ),
    ),
);