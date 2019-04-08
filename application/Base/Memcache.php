<?php
class Base_Memcache
{
    protected static $_instance = null;
    protected $mcObj = null;

    protected $staticCache = null;


    private function __construct()
    {
        $this->mcObj = new Memcache();
        $this->mcObj->addserver('localhost');
    }

    public static function i()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get($key, $flags = null)
    {
        if (!empty($this->staticCache[$key])) {
            return $this->staticCache[$key];
        }
        $res = $this->mcObj->get($key, $flags);
        $this->staticCache[$key] = $res;
        return $res;
    }

    public function set($key, $val, $expire = 90, $flags = null)
    {
        $this->staticCache[$key] = $val;
        return $this->mcObj->set($key, $val, $flags, $expire);
    }

    public function add($key, $val, $expire = 90, $flags = null)
    {

        $res = $this->mcObj->add($key, $val, $flags, $expire);
        if ($res) {
            $this->staticCache[$key] = $val;
        }

        return $res;
    }

    public function incr($key, $val = 1, $exist = true, $expire = 90)
    {
        if (!$exist && $res = $this->add($key, $val, $expire)) {
            $this->staticCache[$key] = $res;
            return $res;
        }
        
        $res = $this->mcObj->increment($key, $val);
        $this->staticCache[$key] = $res;
        return $res;
    }

    public function delete($key)
    {
        unset($this->staticCache[$key]);
        return $this->mcObj->delete($key);
    }
}