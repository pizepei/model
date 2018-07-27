<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2018/7/27
 * Time: 14:28
 * @title    文件缓存驱动
 */
namespace pizepei\model\cache\drive;
use pizepei\config\Config;
class File
{
    /**
     * 文件缓存时的文件名
     * 格式[组名（默认public）]_[MD5（key）]_[有效期date（Y-m-d H-i-s）（缓存时间+缓存有效期）].txt
     * @var string
     */
    private static $fileName = '';
    /**
     * @var string
     */
    private static $key = '';

    /**
     * 保存路径
     * @var string
     */
    private static $path = '';
    /**
     * md5的key
     * @var string
     */
    private static $md5key = '';

    /**
     * 配置的缓存目录
     * @var string
     */
    private static $targetDir = '';

    /**
     * 分组
     * @var string
     */
    private static $group = 'public';
    /**
     * 有效期
     * @var int
     */
    private static $period = 0;
    /**
     * 缓存数据
     * @var string
     */
    private static $data = '';

    private static $extension = '.txt';

    /**
     * File constructor.
     */
    public function __construct()
    {


    }
    /**
     * 初始化配置
     */
    public static function initSet($key,$data,$period,$config)
    {
        /**
         * 缓存路径
         */
        self::$targetDir = $config['targetDir'].'cache'.DIRECTORY_SEPARATOR;

        self::createDir(self::$targetDir);

        self::$key = $key;
        /**
         * 判断是否分组
         */
        if(is_array($key) && count($key) == 2){
            self::$group = $key[0];
            self::$key = $key[0];
        }
        /**
         * md5
         */
        self::$md5key =  md5(self::$key );
        /**
         * 有效期
         */
        self::$period = time()+$period;
        /**
         * 文件名称
         */
        self::$fileName = self::$group.'_'.self::$md5key.self::$extension;
        /**
         * 完整路径+文件名称
         */
        self::$path = self::$targetDir.self::$fileName;
        /**
         * 数据
         */
        self::$data = ['data'=>$data,'period'=>self::$period];
        return true;
    }
    /**
     * 设置缓存
     */
    public static function set($key,$data,$period,$config){
        self::initSet($key,$data,$period,$config);

        $result = file_put_contents(self::$path,self::$data);
        /**
         * 清除信息
         */
        self::$path = null;
        self::$fileName = null;
        self::$key = null;
        self::$data = null;

        return $result;
    }

    /**
     * 获取缓存
     * @param $key
     */
    public static function get($key)
    {



    }




    /**
     * 判断目录是否存在
     * 不存在创建
     * @param $Dir
     */
    private static function createDir($dir, $mode = 0777)
    {
            if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
            if (!self::createDir(dirname($dir), $mode)) return FALSE;
            return @mkdir($dir, $mode);

    }

}