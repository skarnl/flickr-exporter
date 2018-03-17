<?php
/**
 * @author Oskar van Velden <oskar@in10.nl>
 * @copyright IN10 <http://in10.nl>
 */

namespace Rakso\Command;

use Rakso\Service\FlickrService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class ExportCommand extends Command
{
    protected $progressBar;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('flickr:export')
            ->setDescription('Download flickr albums')
            ->setHelp('This command allows you download flickr album')
            ->addArgument('destination', InputArgument::OPTIONAL, 'Where to put the files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //read .env file
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../../.env');

        $flickr = new FlickrService(getenv('FLICKR_API_KEY'), getenv('FLICKR_API_SECRET'), getenv('FLICKR_TOKEN'));
        $flickr->setDestination(getenv('DESTINATION_PATH'));

        //not sure if we want this
        $flickr->setOutput($output);

        $flickr->setOutput($output);
        $flickr->execute();
    }
}