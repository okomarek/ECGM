<?php

namespace ECGM\Tests;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Model\BaseArray;
use ECGM\Model\Customer;
use ECGM\Model\CustomerGroup;
use ECGM\Model\Parameter;
use PHPUnit\Framework\TestCase;

class MiscTests extends TestCase
{

    public static $splitLine = "\n\n---------------------------------------------------------\n\n";

    public function testInvalidValueExceptions()
    {

        echo "Testing invalid value exceptions\n\n";

        //Validate bad required class
        $this->expectException(InvalidArgumentException::class);
        new BaseArray(null, "SomeNonexistentClassName");

        //Validate bad insert value
        $this->expectException(InvalidArgumentException::class);
        $arrayTest = new BaseArray(null, Customer::class);
        $arrayTest->add(new CustomerGroup(1));

        //Validate non numeric CustomerParameter value
        $this->expectException(InvalidArgumentException::class);
        new Parameter(1, "fasfaf", new Customer(12, new CustomerGroup(1)));

        echo "Invalid value exceptions OK";
        echo self::$splitLine;
    }
}