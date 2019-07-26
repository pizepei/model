<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/1/5
 * Time: 11:24
 * @title redis缓存
 */

namespace pizepei\model\cache\drive;
use \pizepei\model\redis\Redis as RedisModel;

class Redis implements  Cache
{
    /**
     * redis
     * @var null
     */
    protected static $Redis = null;

    /**
     * 缓存类型（比如db类 db类缓存与其他缓存区分 分组和key ）
     * @var string
     */
    public static $typeCache = 'sys';
    /**
     * 分组
     * @var string
     */
    protected static $group = 'public';
    /**
     * 有效期
     * @var int
     */
    protected static $period = 0;
    /**
     * 缓存数据
     * @var string
     */
    protected static $data = '';

    /**
     * @var string
     */
    protected static $key = '';
    /**
     * md5的key
     * @var string
     */
    protected static $md5key = '';


    /**
     * Redis constructor.
     */
    public function __construct()
    {

    }
    /**
     * 设置缓存接口
     * @param $key
     * @param $data
     * @param $period
     * @param $config
     * @return mixed
     */
    public static function set($key,$data,$period,$config)
    {
        static::initRedis();
        /**
         * 确定key
         */
        static::$key = $key;
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[1];
        }
        static::$md5key =  md5(static::$key );
        /**
         * 判断是删除缓存还是设置缓存
         */
        if($data == null){
            /**
             * 删除缓存
             */
            $returnData = static::$Redis->del('cache:'.static::$typeCache.':'.static::$group.':'.static::$md5key);
        }else{
            /**
             * 设置缓存  准备数据
             */
            static::$data = serialize($data);
            /**
             * $typeCache  缓存类型  拼接  分组   =  表名称
             */
            $returnData = static::$Redis->set('cache:'.static::$typeCache.':'.static::$group.':'.static::$md5key,static::$data);
            if($period !=0){
                $returnData = static::$Redis->expire('cache:'.static::$typeCache.':'.static::$group.':'.static::$md5key,$period);
            }
        }
        static ::staticEmpty();
        return $returnData;
    }
    /**
     * 获取缓存
     * @param $key
     * @param $config
     * @return mixed
     */
    public static function get($key,$config,$info=false)
    {
        static::initRedis();
        /**
         * 确定key
         */
        static::$key = $key;
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[1];
        }
        static::$md5key =  md5(static::$key );
        $returnData = unserialize(static::$Redis->get('cache:'.static::$typeCache.':'.static::$group.':'.static::$md5key));
        static::staticEmpty();
        return $returnData;

    }

    /**
     * @Author pizepei
     * @Created 2019/3/31 17:18
     * @title  初始化redis
     * @explain 一般是方法功能说明、逻辑说明、注意事项等。
     *
     */
    public static function initRedis()
    {
        self::$Redis = RedisModel::init(\Config::REDIS);
    }

    /**
     * @Author pizepei
     * @Created 2019/3/31 18:31
     * @param $key
     * @param $config
     * @return mixed
     * @title  获取有效期
     * @explain 暂时只有redis缓存有
     *
     */
    protected static function ttl($key,$config)
    {
        static::initRedis();
        /**
         * 确定key
         */
        static::$key = $key;
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[1];
        }
        static::$md5key =  md5(static::$key );
        $returnData = static::$Redis->ttl('cache:'.static::$typeCache.':'.static::$group.':'.static::$md5key);
        static::staticEmpty();
        return $returnData;
    }
    /**
     * 清空数据
     */
    public static function staticEmpty()
    {
        static::$key = null;
        static::$data = null;
        static::$key = null;
        static::$group = null;
        static::$key = null;
        static::$md5key = null;
        static::$period = null;
    }
}