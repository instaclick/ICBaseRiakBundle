<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RiakBundle\Tests\Service;

use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RiakBundle\Service\CacheManagerService;

/**
 * Unit Test for FallbackCache
 *
 * @group ICBaseRiakBundle
 * @group Unit
 * @group Cache
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class CacheManagerServiceTest extends TestCase
{
    /**
     * Test flush all
     */
    public function testFlushAll()
    {
        $service       = new CacheManagerService();
        $cacheProvider = $this->createAbstractMock('Doctrine\Common\Cache\CacheProvider');

        $service->addCacheProviderService($cacheProvider);

        $cacheProvider->expects($this->once())
            ->method('flushAll');

        $service->flushAll();
    }
}
