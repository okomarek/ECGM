<?php

namespace ECGM\Model;


use ECGM\Enum\DateType;
use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\KeyeableValue;

class Product implements KeyeableValue
{

    /**
     * @var mixed
     */
    protected $id;
    /**
     * @var float
     */
    protected $price;
    /**
     * @var float
     */
    protected $discount;
    /**
     * @var integer
     */
    protected $expiration;
    /**
     * @var DateType
     */
    protected $expirationDateType;
    /**
     * @var BaseArray $complements
     */
    protected $complements;

    /**
     * Product constructor.
     * @param mixed $id
     * @param float $price
     * @param integer $expiration
     * @param int $expirationDateType
     * @param float $discount
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function __construct($id, $price, $expiration, $expirationDateType = DateType::DAYS, $discount = 0.0)
    {
        if (!is_numeric($price) || !is_numeric($discount) || !is_numeric($expiration)) {
            throw  new InvalidArgumentException("Price, expiration or discount are not numeric.");
        }

        if ($expiration < 0) {
            throw  new InvalidArgumentException("Expiration cannot be lower than 0, but is " . $expiration . ".");
        }

        if (!DateType::isValidValue($expirationDateType)) {
            throw new InvalidArgumentException("Expiration date type is $expirationDateType, but available values are " . json_encode(DateType::getConstants()) . ".");
        }

        $this->id = $id;
        $this->price = $price;
        $this->discount = $discount;
        $this->expiration = $expiration;
        $this->expirationDateType = $expirationDateType;
        $this->complements = new BaseArray(null, ProductComplement::class);
    }

    /**
     * @return BaseArray
     */
    public function getComplements()
    {
        return $this->complements;
    }

    /**
     * @param $complements
     * @throws InvalidArgumentException
     */
    public function setComplements($complements)
    {
        $this->complements->set($complements);
    }

    /**
     * @return float|int
     */
    public function getDiscountedPrice()
    {
        return $this->getPrice() - ($this->getPrice() * ($this->getDiscount() / 100));
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return DateType
     */
    public function getExpirationDateType()
    {
        return $this->expirationDateType;
    }

    /**
     * @param DateType $expirationDateType
     */
    public function setExpirationDateType(DateType $expirationDateType)
    {
        $this->expirationDateType = $expirationDateType;
    }

    public function __toString()
    {
        $str = "";
        $str .= "ID: " . $this->getId() . ", ";
        $str .= "Price: " . $this->getPrice() . ", ";
        $str .= "Expiration: " . $this->getExpiration() . ", ";
        $str .= "Discount: " . $this->getDiscount();

        $complements = array();

        /**
         * @var Product $complement
         */
        foreach ($this->complements as $complement) {
            $complements[] = $complement->getId();
        }


        $str .= "Complements: [" . implode(", ", $complements) . "]\n";

        return $str;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->getId();
    }
}