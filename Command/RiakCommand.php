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
 * Class Riak Command.
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class RiakCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ic:base:riak:bucket:declare')
            ->setDescription('Riak command to declare the buckets')
        ;
    }

    /**
     * Get riak connection
     *
     * @return \Riak\Connection
     */
    public function getConnection()
    {
        return $this->getContainer()->get('ic_base_riak.connection.default');
    }

    /**
     * Get riak connection class
     *
     * @return string
     */
    public function getConnectionClass()
    {
        return $this->getContainer()->getParameter('ic_base_riak.class.connection');
    }

    /**
     * Get riack bucket class
     *
     * @return mixed
     */
    public function getBucketClass()
    {
        return $this->getContainer()->getParameter('ic_base_riak.class.bucket');
    }

    /**
     * Get riack bucket property list class
     *
     * @return mixed
     */
    public function getBucketPropertyListClass()
    {
        return $this->getContainer()->getParameter('ic_base_riak.class.bucket_property_list');
    }

    /**
     * Get riak host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getContainer()->getParameter('cache_host');
    }

    /**
     * Get riak port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->getContainer()->getParameter('cache_port');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Declare riak buckets");

        $host = $this->getHost();
        $port = $this->getPort();

        try {
            $connectionClass         = $this->getConnectionClass();
            $bucketClass             = $this->getBucketClass();
            $bucketPropertyListClass = $this->getBucketPropertyListClass();

            $connection         = new $connectionClass($host, $port);
            $bucket             = new $bucketClass($connection, 'bucket_name');
            $bucketPropertyList = new $bucketPropertyListClass();

            $bucket->setPropertyList($bucketPropertyList);

            $obj = new \Riak\Object('object_name');
            $obj->setContent("test-get plap");
            $bucket->put($obj);

            echo "Connection ok";

        } catch (\Riak\Exception\ConnectionException $ex) {
            echo 'Connection failed: '. $ex->getMessage().PHP_EOL;
        } catch (\Riak\Exception\RiakException $ex) {
            echo 'Something riak related failed: '. $ex->getMessage().PHP_EOL;
        }
    }
}
