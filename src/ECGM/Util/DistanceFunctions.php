<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;
use ECGM\Int\DistanceFuncInterface;

/**
 * Class DistanceFunctions
 * @package ECGM\Util
 */
class DistanceFunctions implements DistanceFuncInterface
{
    /**
     * @param array $v1
     * @param array $v2
     * @return float|int
     * @throws InvalidArgumentException
     */
    public function distanceQuick($v1, $v2)
    {
        $dimension = count($v1);

        if ($dimension != count($v2)) {
            throw new InvalidArgumentException("Vector v1 size " . $dimension . " is not equal to vector v2 size " . count($v2));
        }

        $distance = 0;
        for ($n = 0; $n < $dimension; $n++) {
            $distance += abs($v1[$n] - $v2[$n]);
        }

        return $distance;
    }

    /**
     * @param array $v1
     * @param array $v2
     * @return float
     * @throws InvalidArgumentException
     */
    public function distancePrecise($v1, $v2)
    {
        $dimension = count($v1);

        if ($dimension != count($v2)) {
            throw new InvalidArgumentException("Vector v1 size " . $dimension . " is not equal to vector v2 size " . count($v2));
        }

        $distance = 0;
        for ($n = 0; $n < $dimension; $n++) {
            $distance += pow($v1[$n] - $v2[$n], 2);
        }

        return sqrt($distance);
    }

}