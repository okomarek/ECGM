<?php
/**
 * Created by PhpStorm.
 * User: qvee
 * Date: 14.3.18
 * Time: 8:08
 */

namespace ECGM\Int;


use ECGM\Enum\StrategyType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Exceptions\LogicalException;
use ECGM\MainInterface;
use ECGM\Model\AssociativeBaseArray;
use ECGM\Model\Customer;
use ECGM\Model\Order;

interface StrategyInterface
{


    /**
     * StrategyController constructor.
     * @param float $coefficient
     * @param MainInterface $mainInterface
     * @param int $maxProductsInStrategy
     */
    public function __construct($coefficient, MainInterface $mainInterface, $maxProductsInStrategy = 40);

    /**
     * @param Customer $customer
     * @param AssociativeBaseArray $currentProducts
     * @param Order|null $currentOrder
     * @param int $strategyType
     * @return AssociativeBaseArray
     * @throws InvalidArgumentException
     * @throws LogicalException
     * @throws \ReflectionException
     */
    public function getStrategy(Customer $customer, AssociativeBaseArray $currentProducts, Order $currentOrder = null, $strategyType = StrategyType::CONSERVATIVE);
}