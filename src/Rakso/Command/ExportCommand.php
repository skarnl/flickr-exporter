<?php
/**
 * @author Oskar van Velden <oskar@in10.nl>
 * @copyright IN10 <http://in10.nl>
 */

namespace Rakso\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->progressBar = new ProgressBar($output, 50);

        // starts and displays the progress bar
        $this->progressBar->start();

        $i = 0;
        while ($i++ < 50) {
            // ... do some work

            // advances the progress bar 1 unit
            $this->progressBar->advance();
            usleep(100000);

            // you can also advance the progress bar by more than 1 unit
            // $progressBar->advance(3);
        }

        // ensures that the progress bar is at 100%
        $this->progressBar->finish();
    }
}