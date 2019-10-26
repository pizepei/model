<?php
/**
* Class Model
* 缓存模型
* Model::table()->spliceWhere();
*/
namespace pizepei\model\cache;
use pizepei\config\Config;
use pizepei\model\cache\drive\File;
use pizepei\model\cache\drive\Redis;
use pizepei\func\Func;

class Cache{
    /**
     * 文件缓存目录
     *
     * __DIR__ 当前代码所在目录
     * DIRECTORY_SEPARATOR  目录分割符
     * @var string
     */
    public static $targetDir = __DIR__.DIRECTORY_SEPARATOR;
    /**
     * 缓存key
     * @var string
     */
    public static $key = '';

    public static $data = '';

    public static $period = '';

    /**
     * @var array缓存配置
     */
    public static $config = [];


    public function __construct()
    {

    }
    /**
     * 初始化配置
     */
    protected static function init($key,$data,$period)
    {
        /**
         * 获取缓存配置
         */
        self::$config = \Config::UNIVERSAL['cache'];
        /**
         * 初始化数据
         */
        self::$key = $key;
        self::$data = $data;
        self::$period = $period;
        /**
         * 序列化数据
         */
         self::$data = $data;
//        if($data == null){
//            self::$data = $data;
//        }else{
//            self::$data = serialize ($data);
//        }
    }

    /**
     * 设置缓存
     * @param        $key [分组 ,key]
     * @param        $data
     * @param int    $period
     * @param string $type
     * @return mixed
     */
    public static function set($key,$data,$period=0,$type='sys'){
        /**
         * 初始化
         */
        self::init($key,$data,$period);
        /**
         * 判断缓存类型
         *  注意自动加载无法通过use 命名空间加载，只能拼接
         */
        $class = 'pizepei\model\cache\drive\\'.ucfirst(self::$config['driveType']);
        $class::$typeCache = $type;
        return $class::set(self::$key,self::$data,self::$period,self::$config);
    }

    /**
     * @Author 皮泽培
     * @Created 2019/10/26 16:58
     * @param        $key [分组 ,key]
     * @param string $type
     * @param bool $info 是否获取缓存详情
     * @return mixed
     * @throws \Exception
     */
    public static function get($key,$type='sys',$info=false)
    {
        /**
         * 获取缓存配置
         */
        self::$config = \Config::UNIVERSAL['cache'];

        /**
         * 初始化数据
         */
        self::$key = $key;

        $class = 'pizepei\model\cache\drive\\'.ucfirst(self::$config['driveType']);
        $class::$typeCache = $type;

        return $class::get(self::$key,self::$config,$info);
    }
    /**
     * 清空数据
     */
    public static function staticEmpty()
    {



    }

}