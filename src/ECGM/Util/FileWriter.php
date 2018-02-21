<?php

namespace ECGM\Util;


use ECGM\Exceptions\InvalidArgumentException;

class FileWriter
{
    /**
     * @var string
     */
    protected $fileName;
    /**
     * @var string
     */
    protected $dir;

    /**
     * FileWriter constructor.
     * @param string $fileName
     */
    public function __construct($fileName, $dir = null)
    {
        $this->fileName = $fileName;
        if (is_null($dir)) {
            $dir = __DIR__ . "/../log";
        }
        $this->dir = $dir;
        $this->makeDir($dir);
    }

    /**
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function putLineToCSV($data)
    {
        if(!is_array($data)){
            throw new InvalidArgumentException("Parameter is not an array.");
        }

        $fp = fopen($this->fileName, 'a');
        fputcsv($fp, $data);
        fclose($fp);
    }

    /**
     * @param string $dir
     */
    protected function makeDir($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}