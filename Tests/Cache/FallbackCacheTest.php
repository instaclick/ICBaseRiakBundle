<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\RiakBundle\Tests\Cache;

use IC\Bundle\Base\TestBundle\Test\TestCase;
use IC\Bundle\Base\RiakBundle\Cache\FallbackCache;

/**
 * Unit Test for FallbackCache
 *
 * @group Unit
 * @group Cache
 *
 * @author Juti Noppornpitak <jutin@nationalfibre.net>
 */
class FallbackCacheTest extends TestCase
{
    /**
     * @var \IC\Bundle\Base\RiakBundle\Cache\FallbackCache
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $primaryProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $secondaryProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->primaryProvider   = $this->createAbstractMock('Doctrine\Common\Cache\CacheProvider');
        $this->secondaryProvider = $this->createAbstractMock('Doctrine\Common\Cache\CacheProvider');

        $this->cache = new FallbackCache($this->primaryProvider, $this->secondaryProvider);
        $this->cache->setPrimaryTtl(123); // nothing but to satisfy the code coverage.
    }

    /**
     * Test fetch with the primary provider.
     */
    public function testDoFetchWithPrimaryProvider()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue('p1'));

        $this->secondaryProvider
            ->expects($this->never())
            ->method('fetch');

        $panda = $this->cache->fetch('panda');

        $this->assertEquals('p1', $panda);
    }

    /**
     * Test fetch with the secondary provider which has the data.
     */
    public function testDoFetchWithSecondaryProviderGettingHit()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->primaryProvider
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue('p2'));

        $panda = $this->cache->fetch('panda');

        $this->assertEquals('p2', $panda);
    }

    /**
     * Test fetch with the secondary provider which does not has the data.
     */
    public function testDoFetchWithSecondaryProviderGettingMiss()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->primaryProvider
            ->expects($this->any())
            ->method('save');

        $this->secondaryProvider
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $panda = $this->cache->fetch('panda');

        $this->assertEquals(false, $panda);
    }

    /**
     * Test the check if the primary provider contains the ID.
     */
    public function testDoContainsWithPrimaryProvider()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->contains('panda'));
    }

    /**
     * Test the check if the secondary provider contains the ID.
     */
    public function testDoContainsWithSecondaryProviderWithHit()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(false));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->contains('panda'));
    }

    /**
     * Test the check if the secondary provider does not contain the ID.
     */
    public function testDoContainsWithSecondaryProviderWithMiss()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(false));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(false));

        $this->assertEquals(false, $this->cache->contains('panda'));
    }

    /**
     * Test saving with the default lifetime
     */
    public function testDoSaveWithDefaultLifeTime()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->save('panda', 'asdf'));
    }

    /**
     * Test saving with the defined lifetime
     */
    public function testDoSaveWithDefinedLifeTime()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->save('panda', 'asdf', 123));
    }

    /**
     * Test deleting
     */
    public function testDoDelete()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->delete('panda'));
    }

    /**
     * Test flushing
     */
    public function testDoFlush()
    {
        $this->primaryProvider
            ->expects($this->any())
            ->method('flushAll')
            ->will($this->returnValue(true));

        $this->secondaryProvider
            ->expects($this->any())
            ->method('flushAll')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->cache->flushAll());
    }

    /**
     * Test getting stats
     */
    public function testDoGetStats()
    {
        $this->assertEquals(null, $this->cache->getStats());
    }
}
