<?php

namespace ECGM\tests;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\BaseArray;
use ECGM\Model\CurrentProduct;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;

/**
 * Class TestMainInterface
 * @package ECGM\tests
 */
class TestMainInterface implements MainInterface
{

    /**
     * Return all customers, that should be included in strategy.
     * Has to return BaseArray with ECGM\Model\Customer requiredClass
     *
     * @return BaseArray
     */
    public function getCustomers()
    {
        return null;
    }

    /**
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function getProducts()
    {

        $currentProducts = new AssociativeBaseArray(null, CurrentProduct::class);

        $currentProducts->add(new CurrentProduct(1, 600, 1000, 420));
        $currentProducts->add(new CurrentProduct(2, 250, 1000, 126));
        $currentProducts->add(new CurrentProduct(3, 900, 1000, 150));
        $currentProducts->add(new CurrentProduct(4, 1100, 1000, 400));

        return $currentProducts;
    }

    /**
     * Set desired Product Payoff Coefficient to product
     *
     * @param CurrentProduct $product
     * @return CurrentProduct
     */
    public function setProductPPC(CurrentProduct $product)
    {

        $productRevenue = 0;

        switch ($product->getId()) {
            case 1:
                $productRevenue = 420;
                break;
            case 2:
                $productRevenue = 180;
                break;
            case 3:
                $productRevenue = 150;
                break;
            case 4:
                $productRevenue = 400;
                break;
        }

        $ppc = ($product->getPrice() - ($product->getPrice() * ($product->getDiscount() / 100))) - ($product->getPrice() - $productRevenue);

        if ($product->getId() == 2) {
            $ppc = $ppc - ($ppc * 0.30);
        }

        $product->setPpc($ppc);

        return $product;
    }

    /**
     * Return all customers, that have no group and should be included in strategy.
     * Has to return BaseArray with ECGM\Model\Customer requiredClass
     *
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    public function getUngroupedCustomers()
    {
        return new BaseArray(null, Customer::class);
    }

    /**
     * Return all customer groups, that should be included in strategy.
     * Has to return BaseArray with ECGM\Model\CustomerGroup requiredClass
     *
     * @return BaseArray
     * @throws InvalidArgumentException
     */
    public function getCustomerGroups()
    {
        return new BaseArray(null, CustomerGroup::class);
    }
}