<?php

$EM_CONF[$_EXTKEY] = array(
   'title' => 'News Media Migrate',
   'description' => 'Migriert alte Media Records zu FAL',
   'category' => 'plugin',
   'author' => 'Sascha Schieferdecker',
   'author_email' => 'schieferdecker@citkomm.de',
   'author_company' => 'Citkomm services GmbH',
   'shy' => '',
   'priority' => '',
   'module' => '',
   'state' => 'alpha',
   'internal' => '',
   'uploadfolder' => '0',
   'createDirs' => '',
   'modify_tables' => '',
   'clearCacheOnLoad' => 0,
   'lockType' => '',
   'version' => '0.0.1',
   'constraints' => array(
      'depends' => array(
         'typo3' => '6.2.0-7.6.99',
         'news' => '',
      ),
      'conflicts' => array(
      ),
      'suggests' => array(
      ),
   ),
);