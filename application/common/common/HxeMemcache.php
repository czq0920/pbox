<?php
namespace app\common\common;
/**
 * Memcache管理类
 */
class HxeMemcache
{
    private static $cache;

    private static $isEnable, $isPersistent, $prefix;

    public static function changeConfig($prefix = '' ,$configServers = '', $isPersistent=false){
        self::makeConnection($prefix,$configServers, $isPersistent);
    }

    private static function makeConnection($prefix='', $configServers = array(), $isPersistent=false){
        $isEnable = config('CACHE_MEMCACHE_ENABLE');
        $isPersistent = $isPersistent ? $isPersistent : config('CACHE_MEMCACHE_PRESISTENT');
        $configServers = $configServers ? $configServers : config('CACHE_MEMCACHE_SERVER');
        $prefix = $prefix ? $prefix : SITE_PREFIX;

        self::$prefix = $prefix;
        self::$isEnable = $isEnable;
        self::$isPersistent = $isPersistent;
        if ($isEnable)
        {
            self::$cache = new \Memcache();
            $persistent = $isPersistent;
            $servers = $configServers;
            foreach ($servers as $server)
            {
                $host = $server["host"];
                $port = empty($server["port"]) ? 11211 : (int) $server["port"];
                self::$cache->addServer($host, $port, $persistent);
            }
            return true;
        }
        return false;
    }

    /**
     * Add the key-value map in the cache
     *
     * @access public
     * @param string $key  the key of the memcache
     * @param mix $value  the value, it could be any structure
     * @param int $expire  the lifetime of cache, if you set the value as zero, the cache will be never expired.(Unit: second)
     * @return boolean
     */
    public static function add($key, $value, $expire = 0) {
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->add(self::$prefix . $key, $value, 0, $expire);
    }

    /**
     * Increment numeric item's value
     *
     * @param string $key
     * @param int $offset
     */
    public static function increment($key, $offset=1) {
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->increment(self::$prefix . $key, $offset);
    }

    /**
     * set the key-value map in cache
     *
     * @access public
     * @param string $key the key of the memcache
     * @param mixed $value the value, it could be any structure
     * @param int $expire the lifetime of cache, if you set the value as zero, the cache will be never expired.(Unit: second)
     * @return void
     */
    public static function set($key, $value, $expire = 0) {
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->set(self::$prefix . $key, $value, 0, $expire);
    }

    /**
     * get the cache value by key
     *
     * @access public
     * @param string $key the key indicates the value
     * @return mixed the value indicated by the key
     */
    public static function get($key){
        if (!self::$cache && !self::makeConnection()) return false;
        //MultiGet
        if (is_array($key)){
            foreach ($key as $k=>$v) $keyNew[$v] = self::$prefix . $v;
            $key = $keyNew;
        }
        else $key = self::$prefix . $key;

        $value = self::$cache->get($key);

        //对MultiGet的键名去除前缀
        if(is_array($key)) {
            foreach ($value as $k=>$v)
                $valueNew[str_replace(self::$prefix, '', $k)] = $v;
            $value = $valueNew;
        }
        return $value;
    }

    /**
     * clear key-value map and alive flag in cache
     *
     * @access public
     * @param string $key    memcache key
     * @return void
     */
    public static function clear($key){
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->delete(self::$prefix . $key);
    }

    /**
     * get the current status of the memcache server
     *
     * @access public
     */
    public static function stat()
    {
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->getExtendedStats();
    }

    /**
     * decrement numeric item's value
     *
     * @param string $key
     * @param int $offset
     */
    public static function decrement($key, $offset=1) {
        if (!self::$cache && !self::makeConnection()) return false;
        return self::$cache->decrement(self::$prefix . $key, $offset);
    }

}
