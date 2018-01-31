<?php
namespace Atompulse\Bundle\FusionIncludeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;


/**
 * Class AutoDiscoverAssetsCommand
 * @package Atompulse\Bundle\FusionIncludeBundle\Command
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class DiscoverAssetsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('fusion:include:discover-assets')
            ->setDescription('Read folder and build a fusion asset');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Analyzing..</info>");
    }
}
