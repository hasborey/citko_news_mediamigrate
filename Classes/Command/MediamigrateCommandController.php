<?php

namespace Citkomm\CitkoNewsMediamigrate\Command;

use GeorgRinger\News\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MediamigrateCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager
     * @inject
     */
    protected $configurationManager;

      /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \GeorgRinger\News\Domain\Repository\NewsRepository
     * @inject
     */
    protected $newsRepository;

    /**
     * @var \GeorgRinger\News\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    private $logger;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    private $resourceFactory;

    /**
     * @var \TYPO3\CMS\Extbase\Service\CacheService
     * @inject
     */
    private $cacheService;

    public function __construct()
    {
        // Adding options to help archive:
        $this->cli_options[] = array('--pid', 'Seite mit den zu migrierenden Datensätzen');
        $this->cli_options[] = array('--folder', 'absoluter Pfad zum Ordner, in dem die FAL Medien abgelegt werden sollen');
        $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
    }

    /**
     * @param int $pid Seiten UID mit News Records
     */
    public function MediamigrateCommand($pid, $folder) {
        if ($pid > 0) {
            $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
            /* @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
            $querySettings->getRespectStoragePage(false);
            $querySettings->setStoragePageIds(array($pid));
            /* @var $newsToMigrate \TYPO3\CMS\Extbase\Persistence\QueryResultInterface */
            $this->newsRepository->setDefaultQuerySettings($querySettings);
            $newsToMigrate = $this->newsRepository->findAll();
            if ($newsToMigrate->count() > 0) {
                foreach ($newsToMigrate as $news) {
                    /* @var $news \GeorgRinger\News\Domain\Model\News */
                    if ($news->getMedia()) {
                        foreach ($news->getMedia() as $media) {
                            /* @var $media \GeorgRinger\News\Domain\Model\Media */
                            var_dump($media->getUid());
                            var_dump($media->getCaption());
                            var_dump($media->getTitle());
                            var_dump($media->getImage());
                            var_dump($media->getShowinpreview());
                        }
                    }
                }
            }
        }
    }
}
