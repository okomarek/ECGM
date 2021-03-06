<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\UndefinedException;
use ECGM\Int\DistanceFuncInterface;
use ECGM\Int\GroupingImplementationInterface;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Parameter;

/**
 * Class KmeansPlusPlus
 * Based on
 * https://github.com/bdelespierre/php-kmeans
 * @package ECGM\Util
 */
class KmeansPlusPlus implements GroupingImplementationInterface
{
    protected $dimension;
    protected $initialGroups;
    protected $groups;
    protected $customers;

    /**
     * @var DistanceFuncInterface
     */
    protected $distanceFunctions;

    /**
     * KmeansPlusPlus constructor.
     * @param int $dimension
     * @throws InvalidArgumentException
     * @throws UndefinedException
     */
    public function __construct($dimension)
    {
        if ($dimension < 1) {
            throw new UndefinedException("A space dimension cannot be null or negative.");
        }

        $this->dimension = $dimension;
        $this->initialGroups = new BaseArray(null, CustomerGroup::class);
        $this->groups = new BaseArray(null, CustomerGroup::class);
        $this->customers = new BaseArray(null, Customer::class);

        $this->distanceFunctions = new DistanceFunctions();
    }

    /**
     * @return DistanceFuncInterface
     */
    public function getDistanceFunctions()
    {
        return $this->distanceFunctions;
    }

    /**
     * @param DistanceFuncInterface $distanceFunctions
     */
    public function setDistanceFunctions(DistanceFuncInterface $distanceFunctions)
    {
        $this->distanceFunctions = $distanceFunctions;
    }

    /**
     * @param BaseArray $initialGroups
     * @throws InvalidArgumentException
     */
    public function setInitialGroups(BaseArray $initialGroups)
    {
        $this->initialGroups->set($initialGroups);
    }

    /**
     * @param Customer $customer
     * @throws InvalidArgumentException
     */
    public function addCustomer(Customer $customer)
    {
        return $this->customers->add($customer);
    }

    /**
     * @param BaseArray $customers
     * @throws InvalidArgumentException
     */
    public function setCustomers(BaseArray $customers)
    {
        $this->customers->set($customers);
    }

    /**
     * @return int
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @param int $nbGroups
     * @return BaseArray|mixed|null
     * @throws InvalidArgumentException
     */
    public function solve($nbGroups)
    {
        if ($this->initialGroups->size() && $nbGroups != $this->initialGroups->size()) {
            throw new InvalidArgumentException("Required number of groups $nbGroups is not equal to set number of groups " . $this->initialGroups->size() . ".");
        }

        if (!$this->initialGroups->size()) {
            $this->groups = $this->initializeGroups($nbGroups);
        } else {
            $this->groups = $this->initialGroups;
        }

        if ($this->groups->size() == 1) {
            return $this->groups;
        }

        do {
            $continue = $this->iterate();
        } while ($continue);

        return $this->groups;
    }

    /**
     * @param BaseArray $parameters
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCustomerParametersAsArray(BaseArray $parameters)
    {
        $parameters = new BaseArray($parameters, Parameter::class);

        $ret = array();
        /**
         * @var Parameter $parameter
         */
        foreach ($parameters as $parameter) {
            $ret[] = $parameter->getValue();
        }
        return $ret;
    }

    /**
     * @param int $clusterNumber
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function initializeGroups($clusterNumber)
    {

        if (!is_numeric($clusterNumber)) {
            throw new InvalidArgumentException("Number of clusters has to be numeric.");
        }

        if ($clusterNumber <= 0) {
            throw new InvalidArgumentException("Number of clusters has to be greater than 0, but is " . $clusterNumber);
        }

        $groups = new BaseArray(null, CustomerGroup::class);

        $position = rand(1, $this->customers->size());
        for ($i = 1, $this->customers->rewind(); $i < $position; $i++) {
            $this->customers->next();
        }

        $groups->add(new CustomerGroup($groups->nextKey(), new BaseArray($this->customers->current()->getParameters(), Parameter::class)));

        // retains the distances between points and their closest clusters
        $distances = array();

        // create k clusters
        for ($i = 1; $i < $clusterNumber; $i++) {
            $sum = 0;

            // for each points, get the distance with the closest centroid already choosen

            /**
             * @var Customer $customer
             */
            foreach ($this->customers as $customer) {
                $distance = $this->getDistance($customer->getParameters(), $this->getClosest($customer, $groups)->getParameters());
                $sum += $distances[$customer->getId()] = $distance;
            }

            // choose a new random point using a weighted probability distribution
            $sum = rand(0, intval($sum));

            foreach ($this->customers as $customer) {

                if (($sum -= $distances[$customer->getId()]) > 0) {
                    continue;
                }

                $groups->add(new CustomerGroup($groups->nextKey(), new BaseArray($customer->getParameters(), Parameter::class)));
                break;
            }
        }

        /**
         * @var CustomerGroup $firstGroup
         */
        $firstGroup = $groups->getObj(0);
        $firstGroup->mergeCustomers($this->customers);

        return $groups;
    }

    /**
     * @param BaseArray $p1
     * @param BaseArray $p2
     * @return float
     * @throws InvalidArgumentException
     */
    protected function getDistance(BaseArray $p1, BaseArray $p2)
    {
        if ($p1->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Required class for parameters array has to be equal to " . Parameter::class . " but is " . $p1->requiredBaseClass() . ".");
        }

        if ($p2->requiredBaseClass() != Parameter::class) {
            throw new InvalidArgumentException("Required class for parameters array has to be equal to " . Parameter::class . " but is " . $p2->requiredBaseClass() . ".");
        }

        return $this->distanceFunctions->distancePrecise($this->getCustomerParametersAsArray($p1), $this->getCustomerParametersAsArray($p2));

    }

    /**
     * @param Customer $c1
     * @param BaseArray $groups
     * @return CustomerGroup|mixed|null
     * @throws InvalidArgumentException
     */
    protected function getClosest(Customer $c1, BaseArray $groups)
    {

        $minDistance = PHP_INT_MAX;
        $closestGroup = $groups->getObj(0);

        /**
         * @var CustomerGroup $group
         */
        foreach ($groups as $group) {
            $distance = $this->getDistance($c1->getParameters(), $group->getParameters());
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestGroup = $group;
            }
        }

        return $closestGroup;
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function iterate()
    {
        $continue = false;

        // migration storages
        /**
         * @var BaseArray[] $attach
         * @var BaseArray[] $detach
         */
        $detach = $attach = array();

        /**
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {
            $detach[$group->getId()] = new BaseArray(null, Customer::class);
            $attach[$group->getId()] = new BaseArray(null, Customer::class);
        }

        // calculate proximity amongst points and clusters

        /**
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {

            /**
             * @var Customer $customer
             */
            foreach ($group->getCustomers() as $customer) {

                // find the closest cluster
                $closest = $this->getClosest($customer, $this->groups);

                // move the point from its old cluster to its closest
                if ($closest->getId() !== $group->getId()) {
                    $attach[$closest->getId()]->add($customer);
                    $detach[$group->getId()]->add($customer);
                    $continue = true;
                }
            }
        }
        /**
         * Two foreach cycles are required for right replacing customer group with new one
         *
         * @var CustomerGroup $group
         */
        foreach ($this->groups as $group) {
            $group->removeCustomers($detach[$group->getId()]);
        }

        foreach ($this->groups as $group) {
            $group->mergeCustomers($attach[$group->getId()]);
            $group->setParameters($this->updateCentroid($group));
        }

        return $continue;
    }

    /**
     * @param CustomerGroup $group
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    protected function updateCentroid(CustomerGroup $group)
    {
        $count = $group->getCustomers()->size();
        if (!$count) {
            return $group->getParameters();
        }

        $newCenter = new BaseArray(null, Parameter::class);
        $centroid = array_fill(0, $this->dimension, 0);

        /**
         * @var Customer $customer
         */
        foreach ($group->getCustomers() as $customer) {
            for ($i = 0; $i < $this->dimension; $i++) {
                $centroid[$i] += $customer->getParameters()->getObj($i)->getValue();
            }
        }

        for ($i = 0; $i < $this->dimension; $i++) {
            $newCenter->add(new Parameter($newCenter->nextKey(), $centroid[$i] / $count));
        }

        return $newCenter;
    }
}