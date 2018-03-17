<?php

namespace Rakso\Service;

use DateTime;
use phpFlickr;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Zicht\Util\Str;

//TODO check if we can neatly use this library instead of require-ing it like this
require_once(__DIR__ . '/../../../vendor/zgetro/phpflickr/phpFlickr.php');

class FlickrService {

    /** @var Input $input */
    protected $input;

    /** @var Output $output */
    protected $output;

    /** @var phpFlickr $flickr */
    protected $flickr;

    /** @var ProgressBar $progressBar */
    protected $progressBar;

    /** @var string */
    protected $destination;

    /** @var Filesystem $fileSystem */
    protected $fileSystem;

    /**
     * FlickrService constructor.
     */
    public function __construct($apiKey, $apiSecret, $token)
    {
        $this->flickr = new phpFlickr($apiKey, $apiSecret);
	    $this->flickr->setToken($token);

	    $this->fileSystem = new Filesystem();
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $setList = $this->flickr->photosets_getList(null, 1 , 1); //TEMP ONLY 1

        $totalPhotoCount = 0;
        foreach ($setList['photoset'] as $set) {
            $this->debug('setid = ' . $set['id']);
            $this->debug('photos = ' . $set['photos']);

            $totalPhotoCount += $set['photos'];
        }
        $this->debug("Total photocount = " . $totalPhotoCount);

        $this->progressBar = new ProgressBar($this->output, $totalPhotoCount);
        $this->progressBar->start();

        foreach( $setList['photoset'] as $set) {
            $this->exportPhotosFromSet($set);
        }

//        $this->progressBar->finish();
    }

    private function exportPhotosFromSet($set)
    {
        $this->debug('Start exporting set ' . $set['title']['_content']);

        $setPath = $this->prepareDestination($set);

        $this->debug('Setpath: ' . $setPath);

        $photos = $this->flickr->photosets_getPhotos($set['id']);

        foreach ($photos['photoset']['photo'] as $photo) {
            $this->downloadAndStorePhoto($photo, $setPath);

            $this->progressBar->advance();
            exit;
        }
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    private function prepareDestination($set)
    {
        $setPath = $this->destination . $this->buildSetFolderName($set) . '/';
        $this->fileSystem->mkdir($setPath);

        return $setPath;
    }

    /**
     * Guess the DateTime of the set, based on the title
     *
     * @param $set
     * @return string
     */
    private function buildSetFolderName($set) {
        //this isn't the date of the set, but the title of the set containing the period
        $setDate = DateTime::createFromFormat('d M Y', '01 ' . $set['title']['_content']);

        if ($setDate !== false) {
            return sprintf("%s/%s", $setDate->format('Y'), $setDate->format('M'));
        }

        return Str::systemize($set['title']);
    }

    private function getUniqueFileName($photo, $setPath)
    {
        $fileName = Str::systemize($photo['title']) . '.jpg';

        $increment = 1;
        while($this->fileSystem->exists($setPath . $fileName)) {
            $fileName = Str::systemize($photo['title']) . '_' . $increment . '.jpg';
            $increment++;
        }

        return $fileName;
    }

    private function downloadAndStorePhoto($photo, $setPath)
    {
        $photoUrl = $this->flickr->buildPhotoURL($photo, "original");
        $photoFileName = $this->getUniqueFileName($photo, $setPath);

        $this->debug('Photo url: ' . $photoUrl);
        $this->debug('Photo filename: ' . $photoFileName);
        $this->debug('Photo destination path: ' . $setPath . $photoFileName);

        $this->fileSystem->dumpFile($setPath . $photoFileName, $photoUrl);
    }

    private function debug($message) {
        if ($this->output->isDebug()) {
            $this->output->writeln($message);
        }
    }
}