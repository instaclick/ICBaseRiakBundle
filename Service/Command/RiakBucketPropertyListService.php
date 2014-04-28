<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Service\Command;

use Symfony\Component\DependencyInjection\Container;

/**
 * Service to set property list for each riak buckets
 *
 * @author David Maignan <davidm@nationalfibre.net>
 */
class RiakBucketPropertyListService
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var array
     */
    private $propertyList;

    /**
     * Set container
     *
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get container
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    private function getContainer()
    {
        return  $this->container;
    }

    /**
     * Set bucket property list
     *
     * @param array $propertyList
     */
    public function setBucketPropertyList($propertyList)
    {
        $this->propertyList = $propertyList;
    }

    /**
     * Get bucket property list
     *
     * @return array
     */
    public function getBucketPropertyList()
    {
        return $this->propertyList;
    }

    /**
     * Execute service
     */
    public function execute()
    {
        foreach ($this->getBucketPropertyList() as $bucketName => $value) {

            $bucketService = $this->getRiakBucketService($bucketName);

            if (isset($value['property_list'])) {
                $bucketPropertyListClass = $this->getBucketPropertyListClass();
                $bucketPropertyList      = new $bucketPropertyListClass();

                foreach ($value['property_list'] as $key => $value) {
                    $method = $this->normalizeName($key);

                    $bucketPropertyList->$method($value);
                }

                $bucketService->setPropertyList($bucketPropertyList);
            }
        }
    }

    /**
     * Get bucket service by name
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getRiakBucketService($name)
    {
        $bucketServiceName = sprintf("ic_base_riak.bucket.%s", $name);

        return $this->getContainer()->get($bucketServiceName);
    }

    /**
     * Get riack bucket property list class
     *
     * @return mixed
     */
    private function getBucketPropertyListClass()
    {
        return $this->getContainer()->getParameter('ic_base_riak.class.bucket_property_list');
    }

    /**
     * Normalize names, e.g., "property_list" becomes "setPropertyList"
     *
     * @param string $name
     *
     * @return string
     */
    private function normalizeName($name)
    {
        $name = (preg_replace('/([a-z])_([a-z])/e', '"$1".ucfirst("$2")', $name));

        return 'set' . ucfirst($name);
    }
}
