<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Cache;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Fallback Cache Provider
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
class FallbackCache extends CacheProvider
{
    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $primary;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $fallback;

    /**
     * @var integer
     */
    private $primaryTtl;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Cache\CacheProvider $primary
     * @param \Doctrine\Common\Cache\CacheProvider $fallback
     */
    public function __construct(CacheProvider $primary, CacheProvider $fallback)
    {
        $this->primary  = $primary;
        $this->fallback = $fallback;
    }

    /**
     * Define the default primary TTL for fallback retrievals.
     *
     * @param integer $ttl
     */
    public function setPrimaryTtl($ttl)
    {
        $this->primaryTtl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $primaryFetch = $this->primary->fetch($id);

        if ($primaryFetch !== false) {
            return $primaryFetch;
        }

        $fallbackFetch = $this->fallback->fetch($id);

        if ($fallbackFetch !== false) {
            $this->primary->save($id, $fallbackFetch, $this->primaryTtl);
        }

        return $fallbackFetch;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        $primaryContains = $this->primary->contains($id);

        return $primaryContains ?: $this->fallback->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $this->fallback->save($id, $data, (int) $lifeTime);

        return (bool) $this->primary->save($id, $data, (int) $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        $this->fallback->delete($id);

        return $this->primary->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        $this->fallback->flushAll();

        return $this->primary->flushAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        // Only exposed through HTTP stats API, not Protocol Buffers API
        return null;
    }
}
