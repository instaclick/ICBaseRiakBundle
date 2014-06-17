<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\KernelInterface;
use Riak\Output\GetOutput;

//
// Require 3rd-party libraries here:
//
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * RiakContext
 *
 * @author Mohib Malgi <mohibm@nationalfibre.net>
 * @author David Maignan <davidm@nationalfibre.net>
 */
class RiakContext extends RawMinkContext implements KernelAwareInterface
{
    const RIAK_SERVICE_PREFIX = 'ic_base_riak.bucket.';

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
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
     *
     * @return string
     */
    public function clearSingleBucket($bucket)
    {
        $serviceId = self::RIAK_SERVICE_PREFIX . $bucket;

        $this->clearBucketByServiceId($serviceId);

        return $serviceId;
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
            if (preg_match('/^' . self::RIAK_SERVICE_PREFIX . '/', $id, $matches)) {
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

    /**
     * Clears the bucket
     *
     * @param string $bucket
     *
     * @When /^(?:|I )clear the bucket "([^"]*)"$/
     */
    public function clearTheBucket($bucket)
    {
        $serviceId = $this->clearSingleBucket($bucket);
        $service   = $this->kernel->getContainer()->get($serviceId);

        foreach ($service->getKeyList() as $key) {
            $this->checkResult($service->get($key));
        }
    }

    /**
     * Check the object list to ensure that they are all deleted
     *
     * @param \Riak\Output\GetOutput $result
     *
     * @throws \Exception
     */
    private function checkResult(GetOutput $result)
    {
        foreach ($result->getObjectList() as $object) {
            if ($object->isDeleted()) {
                continue;
            }

            throw new \Exception('The bucket could not be cleared.');
        }
    }

    /**
     * Set property for a bucket
     *
     * @param string $property
     * @param string $value
     * @param string $bucketName
     *
     * @throws \Exception
     *
     * @Given /^I set {([^}]+)} with "([^"]*)" for bucket "([^"]*)"$/
     */
    public function iSetPropertyForBucket($property, $value, $bucketName)
    {
        $response = $this->postBucketPropertyList($bucketName, $property, $value);

        if ($response->getStatusCode() !== 204) {
            throw new \Exception(sprintf("Request failed to set the property %s for the bucket %s", $property, $bucketName));
        }

        //Check the value is set
        $response      = $this->getBucketPropertyList($bucketName);
        $propertyList  = json_decode($response->getBody(true), true);
        $key           = $this->getMatchingKey(array_keys($propertyList['props']), $property);
        $propertyValue = $propertyList['props'][$key];

        assertEquals($value, $propertyValue);
    }

    /**
     * Check default value for bucket property
     *
     * @param string $property
     * @param string $bucketName
     *
     * @Then /^I should check the configured {([^}]+)} for bucket "([^"]*)"$/
     */
    public function iShouldCheckPropertyConfigurationValueForBucket($property, $bucketName)
    {
        $bucketList    = $this->getContainerParameter('ic_base_riak.buckets');
        $defaultValue  = $bucketList[$bucketName]['property_list'][$property];
        $response      = $this->getBucketPropertyList($bucketName);
        $propertyList  = json_decode($response->getBody(true), true);
        $key           = $this->getMatchingKey(array_keys($propertyList['props']), $property);
        $propertyValue = $propertyList['props'][$key];

        assertEquals($defaultValue, $propertyValue);
    }

    /**
     * Match the keys names returned from riak and the properties configured in the application
     *
     * @param array  $keys
     * @param string $value
     *
     * @return mixed
     */
    private function getMatchingKey($keys, $value)
    {
        foreach ($keys as $key) {
            if (preg_match("/^$key/", $value) === 1) {
                return $key;
            }
        }
    }

    /**
     * Generate bucket property list url
     *
     * @param string $bucketName
     *
     * @return string
     */
    private function generateBucketPropertyListUrl($bucketName)
    {
        return sprintf("http://%s:%d/buckets/%s/props", $this->getContainerParameter('cache_host'), 8098, $bucketName);
    }

    /**
     * Post request to set property for a specific bucket
     *
     * @param string $bucketName
     * @param string $property
     * @param string $value
     *
     * @return \Guzzle\Http\Message\Response
     */
    private function postBucketPropertyList($bucketName, $property, $value)
    {
        $request = $this->getServiceById('ic_base_riak.service.status.client')->createRequest(
            "PUT",
            $this->generateBucketPropertyListUrl($bucketName),
            array(
                'Content-Type' => 'application/json'
            ),
            sprintf('{"props":{"%s":"%s"}}', $property, $value)
        );

        return $request->send();
    }


    /**
     * Send get request for bucket property list
     *
     * @param string $bucketName
     *
     * @return \Guzzle\Http\Message\Response
     */
    private function getBucketPropertyList($bucketName)
    {
        $url     = $this->generateBucketPropertyListUrl($bucketName);
        $request = $this->getServiceById('ic_base_riak.service.status.client')->createRequest("GET", $url);

        return $request->send();
    }

    /**
     * Get service by id
     *
     * @param string $id
     *
     * @return mixed
     */
    private function getServiceById($id)
    {
        return $this->kernel->getContainer()->get($id);
    }

    /**
     * Get a the value for a container parameter
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getContainerParameter($name)
    {
        return $this->kernel->getContainer()->getParameter($name);
    }
}
