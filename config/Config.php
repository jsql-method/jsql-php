<?php
namespace JSQL;

use \ArrayAccess;

/**
 * Class Config
 * @package JSQL
 */
class Config implements ArrayAccess
{
    /** @var \ArrayObject $data */
    public $data = array();

    /** @var string $file */
    private $file;

    /**
     * Config constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->file = __DIR__ . "/{$name}.ini";

        if(!file_exists($this->file)) {
            /* Create sample file */
            $this->data = [];
            $this->data['KEYS']['Api-Key'] = "Place application key here!";
            $this->data['KEYS']['Dev-Key'] = "Place developer key here!";
            $this->Save();

            /* Throw error */
            die("Configuration file does not exist!<br />Sample has been created.<br />Please try again.");
        }

        /* Parse config file */
        $this->data = parse_ini_file($this->file, true);
    }

    /**
     * Getter magic method
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Setter magic method
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Isset magic method
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Unset magic method
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @return array
     */
    public function &__invoke()
    {
        return $this->data;
    }

    /**
     * ArrayAccess offsetGet overload
     * @param $offset
     * @return mixed|null
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * ArrayAccess offsetSet overload
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }

        $this->Save();
    }

    /**
     * ArrayAccess offsetExists overload
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * ArrayAccess offsetUnset overload
     * @param $offset
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
            $this->Save();
        }
    }

    /**
     * Method updates config file
     * @return bool
     */
    public function Save() {
        // process array
        $data = array();
        foreach ($this->data as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }

        // open file pointer, init flock options
        $fp = fopen($this->file, 'w');
        $retries = 0;
        $max_retries = 100;

        if (!$fp) {
            return false;
        }

        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }

        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}
