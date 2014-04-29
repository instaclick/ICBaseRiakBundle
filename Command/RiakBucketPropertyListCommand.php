<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to parametrize Riak buckets
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class RiakBucketPropertyListCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ic:base:riak:bucket:declare')
            ->setDescription('Riak command to declare the buckets properties')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Set riak buckets property list");

        $riakBucketService = $this->getContainer()->get('ic_base_riak.property_list.service');

        $riakBucketService->execute();
    }
}
