<?php

namespace Citkomm\CitkoNewsMediamigrate\Command;

use GeorgRinger\News\Domain\Model\FileReference;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
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

    public function __construct()
    {
        // Adding options to help archive:
        $this->cli_options[] = array('-pid', 'Seite mit den zu migrierenden Datensätzen');
        $this->cli_options[] = array('-folder', 'Pfad zum Ordner (ohne abschließendes Slash), in dem die FAL Medien abgelegt werden sollen ab PATH_site');
        $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
    }

    /**
     * @param int $pid Seiten UID mit News Records
     * @param string $folder absoluter Pfad für FAL-Dateien ab PATH_site
     */
    public function MediamigrateCommand($pid, $folder) {
        if ($pid > 0 && is_dir(PATH_site . $folder)) {

            $FU = new BasicFileUtility;

            $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

            /* @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
            $querySettings->getRespectStoragePage(false);
            $querySettings->setStoragePageIds(array($pid));

            /* @var $newsToMigrate \TYPO3\CMS\Extbase\Persistence\QueryResultInterface */
            $this->newsRepository->setDefaultQuerySettings($querySettings);
            $newsToMigrate = $this->newsRepository->findAll();

            if ($newsToMigrate->count() > 0) {
                foreach ($newsToMigrate as $news) {
                    $clear =  new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
                    // Media -> FAL Media

                    /* @var $news \GeorgRinger\News\Domain\Model\News */
                    if ($news->getMedia()) {
                        $news->setFalMedia($clear);
                        foreach ($news->getMedia() as $media) {
                            /* @var $media \GeorgRinger\News\Domain\Model\Media */
                            // Ist es ein Bild?
                            if(strlen($media->getImage()) > 0) {
                                try {
                                    $newfilename = $FU->getUniqueName($media->getImage(), PATH_site . $folder);
                                    copy(PATH_site . 'uploads/tx_news/' . $media->getImage(), $newfilename);
                                    /* @var $file \TYPO3\CMS\Core\Resource\File */
                                    $file = $this->resourceFactory->retrieveFileOrFolderObject($newfilename);
                                } catch (\TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException $exception) {
                                    $this->logger->error($exception->getMessage());
                                    $this->logger->error($exception->getTraceAsString());
                                    $file = false;
                                }
                                if (is_object($file)) {
                                    $reference = new FileReference();
                                    $reference->setFileUid($file->getUid());
                                    $reference->setDescription($media->getCaption());
                                    $reference->setTitle($media->getTitle());
                                    $reference->setAlternative($media->getAlt());
                                    $reference->setShowinpreview($media->getShowinpreview());
                                    $reference->setPid($pid);
                                    $news->addFalMedia($reference);
                                }
                                else {
                                    $this->logger->error('Kann Datei ' . $media->getImage() . ' von News ' . $news->getUid() . ' nicht migrieren');
                                }
                            }
                            elseif (strlen($media->getMultimedia()) > 0 && strpos($media->getMultimedia(), 'file:') !== false) {
                                $reference = new FileReference();
                                $reference->setFileUid(str_replace('file:', '', $media->getMultimedia()));
                                $reference->setDescription($media->getCaption());
                                $reference->setTitle($media->getTitle());
                                $reference->setAlternative($media->getAlt());
                                $reference->setShowinpreview($media->getShowinpreview());
                                $reference->setPid($pid);
                                $news->addFalMedia($reference);
                            }
                        }
                    }

                    $this->newsRepository->update($news);
                    $this->persistenceManager->persistAll();
                    $this->logger->info('Media von News ' . $news->getUid() . ' migriert');

                    unset($file);
                    unset($newfilename);

                    /* @var $newsToMigrate \TYPO3\CMS\Extbase\Persistence\QueryResultInterface */
                    $this->newsRepository->setDefaultQuerySettings($querySettings);
                    $newsToMigrate = $this->newsRepository->findAll();

                    foreach ($newsToMigrate as $news) {
                        $clear =  new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
                        // Related Files -> FAL Related Files
                        if($news->getRelatedFiles()) {
                            $news->setFalRelatedFiles($clear);
                            /* @var $relatedFile \GeorgRinger\News\Domain\Model\File */
                            foreach ($news->getRelatedFiles() as $relatedFile) {
                                try {
                                    $newfilename = $FU->getUniqueName($relatedFile->getFile(), PATH_site . $folder);
                                    copy(PATH_site . 'uploads/tx_news/' . $relatedFile->getFile(), $newfilename);
                                    /* @var $file \TYPO3\CMS\Core\Resource\File */
                                    $file = $this->resourceFactory->retrieveFileOrFolderObject($newfilename);
                                } catch (\TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException $exception) {
                                    $this->logger->error($exception->getMessage());
                                    $this->logger->error($exception->getTraceAsString());
                                    $file = false;
                                }
                                if (is_object($file)) {
                                    $reference = new FileReference();
                                    $reference->setFileUid($file->getUid());
                                    $reference->setDescription($relatedFile->getDescription());
                                    $reference->setTitle($relatedFile->getTitle());
                                    $reference->setPid($pid);
                                    $news->addFalRelatedFile($reference);
                                }
                                else {
                                    $this->logger->error('Kann Datei ' . $relatedFile->getFile() . ' von News ' . $news->getUid() . ' nicht migrieren');
                                }
                            }
                        }

                        $this->newsRepository->update($news);
                        $this->persistenceManager->persistAll();
                        $this->logger->info('RelatedFiles von News ' . $news->getUid() . ' migriert');

                    }

                }
            }
        }
    }

    /**
     * Alte Medien wirklich löschen - erst nach Migration ausführen
     * @param int $pid PID mit Datensätzen
     * @param int $really Wirklisch löschen
     */
    public function MediadeleteCommand ($pid, $really) {
        if ($pid > 0 && intval($really) === 1) {
            $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

            /* @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
            $querySettings->getRespectStoragePage(false);
            $querySettings->setStoragePageIds(array($pid));

            /* @var $newsToMigrate \TYPO3\CMS\Extbase\Persistence\QueryResultInterface */
            $this->newsRepository->setDefaultQuerySettings($querySettings);
            $newsToDelete = $this->newsRepository->findAll();

            if ($newsToDelete->count() > 0) {
                /* @var $news \GeorgRinger\News\Domain\Model\News */
                foreach ($newsToDelete as $news) {
                    if ($news->getMedia()) {
                        $clear = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
                        foreach ($news->getMedia() as $media) {
                            /* @var $media \GeorgRinger\News\Domain\Model\Media */
                            // Ist es ein Bild?
                            if (strlen($media->getImage()) > 0) {
                                unlink(PATH_site . 'uploads/tx_news/' . $media->getImage());
                            }
                        }
                        $news->setMedia($clear);
                        $this->newsRepository->update($news);
                    }
                }
                $this->persistenceManager->persistAll();
            }

            /* @var $newsToMigrate \TYPO3\CMS\Extbase\Persistence\QueryResultInterface */
            $this->newsRepository->setDefaultQuerySettings($querySettings);
            $newsToDelete = $this->newsRepository->findAll();

            if ($newsToDelete->count() > 0) {
                /* @var $news \GeorgRinger\News\Domain\Model\News */
                foreach ($newsToDelete as $news) {
                    if ($news->getRelatedFiles()) {
                        $clear =  new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
                        foreach ($news->getRelatedFiles() as $file) {
                            /* @var $file \GeorgRinger\News\Domain\Model\File */
                            if (strlen($file->getFile()) > 0) {
                                unlink(PATH_site.'uploads/tx_news/'.$file->getFile());
                            }
                        }
                        $news->setRelatedFiles($clear);
                        $this->newsRepository->update($news);
                    }
                }
                $this->persistenceManager->persistAll();
            }
        }
    }
}
