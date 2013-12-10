<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * RiakContext
 *
 * @author Mohib Malgi <mohibm@nationalfibre.net>
 */
class RiakContext extends RawMinkContext implements KernelAwareInterface
{
    const RIAK_SERVICE_PREFIX = 'ic_base_riak.bucket.';

    /**
     * @var KernelInterface Kernel
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Clear the cache
     */
    public function clearRiak()
    {
        $serviceIdList = $this->getRiakBucketIdList();

        foreach ($serviceIdList as $serviceId) {
            $this->clearBucketByServiceId($serviceId);
        }
    }

    /**
     * Clears a Riak bucket by a given name
     *
     * @param string $bucket
     */
    public function clearSingleBucket($bucket)
    {
        $this->clearBucketByServiceId(self::RIAK_SERVICE_PREFIX . $bucket);
    }

    /**
     * Clear a riak bucket for a given service id
     *
     * @param string $serviceId
     */
    private function clearBucketByServiceId($serviceId)
    {
        $service = $this->kernel->getContainer()->get($serviceId);
        $keyList = $service->getKeyList();

        foreach ($keyList as $key) {
            $service->delete($key);
        }
    }

    /**
     * Retrieves collection of riak bucket ids.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection;
     */
    private function getRiakBucketIdList()
    {
        $riakBucketList = new ArrayCollection();
        $idList         = $this->kernel->getContainer()->getServiceIds();

        foreach ($idList as $id) {
            if (preg_match('/^ic_base_riak.bucket./', $id, $matches)) {
                $riakBucketList->add($id);
            }
        }

        return $riakBucketList;
    }

    /**
     * Clear the cache
     *
     * @When /^(?:|I )clear the cache$/
     */
    public function clearTheCache()
    {
        $this->clearRiak();
    }
}
