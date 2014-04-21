<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Riak;

use Riak\Bucket as RiakBucket;

/**
 * This is the class that adds prefix to keys in all Riak buckets
 *
 * @author Eldar Gafurov <eldarg@nationalfibre.net>
 * @author Fabio B. Silva <fabios@nationalfibre.net>
 */
class Bucket extends RiakBucket
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * Set the prefix to be used for riak keys.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function put($object, $putInput = null)
    {
        $object->setKey(sprintf('%s%s', $this->prefix, $object->getKey()));

        return $putInput
          ? parent::put($object, $putInput)
          : parent::put($object);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $getInput = null)
    {
        $key = sprintf('%s%s', $this->prefix, $key);

        return $getInput
          ? parent::get($key, $getInput)
          : parent::get($key);
    }
}
