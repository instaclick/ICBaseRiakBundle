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
     * Get the prefix key
     *
     * @param string $key
     *
     * @return string
     */
    public function getPrefixKey($key)
    {
        if ($this->prefix == null) {
            return $key;
        }

        $size   = strlen($this->prefix);
        $prefix = $this->prefix;

        if (substr($key, 0, $size) === $prefix) {
            return $key;
        }

        return sprintf('%s%s', $this->prefix, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function put($object, $putInput = null)
    {
        $object->setKey($this->getPrefixKey($object->getKey()));

        return $putInput
            ? parent::put($object, $putInput)
            : parent::put($object);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $getInput = null)
    {
        $key = $this->getPrefixKey($key);

        return $getInput
            ? parent::get($key, $getInput)
            : parent::get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object, $deleteInput = null)
    {
        $key = $this->getPrefixKey(
            is_object($object)
            ? $object->getKey()
            : $object
        );

        return $deleteInput
            ? parent::delete($key, $deleteInput)
            : parent::delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function counter($key)
    {
        $key = $this->getPrefixKey($key);

        return parent::get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyList()
    {
        $size   = strlen($this->prefix);
        $list   = parent::getKeyList();
        $prefix = $this->prefix;

        if ($this->prefix == null) {
            return $list;
        }

        return array_filter($list, function ($key) use ($prefix, $size) {
            return substr($key, 0, $size) === $prefix;
        });
    }
}
