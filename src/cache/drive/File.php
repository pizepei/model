<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2018/7/27
 * Time: 14:28
 * @title    文件缓存驱动
 */
namespace pizepei\model\cache\drive;
use pizepei\func\Func;

class File
{
    /**
     * 文件缓存时的文件名
     * 格式[组名（默认public）]_[MD5（key）]_[有效期date（Y-m-d H-i-s）（缓存时间+缓存有效期）].txt
     * @var string
     */
    protected static $fileName = '';
    /**
     * @var string
     */
    protected static $key = '';

    /**
     * 保存路径
     * @var string
     */
    protected static $path = '';
    /**
     * md5的key
     * @var string
     */
    protected static $md5key = '';

    /**
     * 配置的缓存目录
     * @var string
     */
    protected static $targetDir = '';

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
     * 缓存文件的类型
     * @var string
     */
    protected static $extension = '.txt';
    /**
     * 通配符 用来匹配对应的key
     * @var string
     */
    protected static $wildcard = '';

    /**
     * 缓存类型（比如db类 db类缓存与其他缓存区分 分组和key ）
     * @var string
     */
    protected static $typeCache = 'sys';

    public function __construct()
    {

    }
    /**
     * SET初始化配置
     */
    public static function initSet($key,$data,$period,$config)
    {

        /**
         * 缓存路径
         */
        static::$targetDir = $config['targetDir'].'cache'.DIRECTORY_SEPARATOR.static::$typeCache.DIRECTORY_SEPARATOR;
        /**
         * 文件处理类
         *  创建目录方法
         */
        Func:: M('file') ::createDir(static::$targetDir);

        static::$key = $key;
        /**
         * 判断是否分组
         */
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[1];
        }
        /**
         * md5
         */
        static::$md5key =  md5(static::$key );
        /**
         * 有效期   0永久
         */
        if($period != 0){
            static::$period = time()+$period;
        }
        /**
         * 文件名称
         */
        static::$fileName = static::$group.'_'.static::$md5key.static::$extension;
        /**
         * 完整路径+文件名称
         */
        static::$path = static::$targetDir.static::$fileName;

        static::$data = ['data'=>$data,'period'=>static::$period];
        return true;
    }

    /**
     * GET初始化配置
     * @param $key  需要查询的key
     * @param $config 配置
     * @return bool
     */
    public static function initGet($key,$config)
    {
        /**
         * 缓存路径
         */
        static::$targetDir = $config['targetDir'].'cache'.DIRECTORY_SEPARATOR.static::$typeCache.DIRECTORY_SEPARATOR;

        static::$key = $key;
        /**
         * 判断是否分组
         */
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[1];
        }
        /**
         * md5
         */
        static::$md5key =  md5(static::$key );
        /**
         * 文件名称
         */
        static::$fileName = static::$group.'_'.static::$md5key.static::$extension;
        /**
         * 完整路径+文件名称
         */
        static::$path = static::$targetDir.static::$fileName;
        return true;
    }



    /**
     * 设置缓存
     */
    public static function set($key,$data,$period,$config){
        static::initSet($key,$data,$period,$config);

        /**
         * 判断是删除缓存还是设置缓存
         */
        if(static::$data['data'] == null){
            /**
             * 删除缓存
             */
            $result = @unlink(static::$path);
        }else{
            /**
             * 设置缓存
             */
            $result = file_put_contents(static::$path,serialize(static::$data));
        }
        /**
         * 清除 static  信息
         * 防止数据错误
         */
        static::staticEmpty();
        return $result;
    }

    /**
     * 获取缓存
     * @param $key  需要查询的key
     * @param $config 配置
     * @return bool
     */
    public static function get($key,$config)
    {
        static::initGet($key,$config);

        if(file_exists(static::$path )){
            /**
             * 获取数据
             */
            static:: $data = unserialize(file_get_contents(static::$path));

            if(static:: $data['period'] >time() || static:: $data['period']==0 ){
                $data = static:: $data['data'];
                /**
                 * 清除 static  信息
                 * 防止数据错误
                 */
                static::staticEmpty();
                return $data;
            }
            /**
             * 删除
             */
            unlink(static::$path);
        }
        /**
         * 清除 static  信息
         * 防止数据错误
         */
        static::staticEmpty();
        return NULL;
    }
    /**
     * 清空数据
     */
    public static function staticEmpty()
    {
        static::$path = null;
        static::$fileName = null;
        static::$key = null;
        static::$data = null;
        static::$targetDir = null;
        static::$key = null;
        static::$group = null;
        static::$key = null;
        static::$md5key = null;
        static::$period = null;
        static::$fileName = null;
        static::$path = null;
    }

}