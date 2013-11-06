<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Service;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cache Manager Service.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class CacheManagerService
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $cacheProviderList;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cacheProviderList = new ArrayCollection();
    }

    /**
     * Add a cache provider service to the stack.
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function addCacheProviderService(CacheProvider $cacheProvider)
    {
        $this->cacheProviderList->add($cacheProvider);
    }

    /**
     * Flush the data of the cache provider in the stack.
     */
    public function flushAll()
    {
        foreach ($this->cacheProviderList as $cacheProvider) {
            $cacheProvider->flushAll();
        }
    }
}
