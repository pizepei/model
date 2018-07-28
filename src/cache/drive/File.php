<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2018/7/27
 * Time: 14:28
 * @title    文件缓存驱动
 */
namespace pizepei\model\cache\drive;
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

        static::createDir(static::$targetDir);

        static::$key = $key;
        /**
         * 判断是否分组
         */
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[0];
        }
        /**
         * md5
         */
        static::$md5key =  md5(static::$key );
        /**
         * 有效期
         */
        static::$period = time()+$period;
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
     * @param $wildcard  通配符 用来匹配对应的key
     * @param $config 配置
     * @return bool
     */
    public static function initGet($key,$wildcard,$config)
    {
        /**
         * 缓存路径
         */
        static::$targetDir = $config['targetDir'].'cache'.DIRECTORY_SEPARATOR.static::$typeCache.DIRECTORY_SEPARATOR;

        static::createDir(static::$targetDir);

        static::$key = $key;
        /**
         * 判断是否分组
         */
        if(is_array($key) && count($key) == 2){
            static::$group = $key[0];
            static::$key = $key[0];
        }
        /**
         * md5
         */
        static::$md5key =  md5(static::$key );
        /**
         * 通配符
         */
        static::$wildcard = $wildcard;
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
            $result = unlink(static::$path);
        }else{
            /**
             * 设置缓存
             */
            $result = file_put_contents(static::$path,static::$data);

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
     * @param $key
     */
    public static function get($key)
    {






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
    }

    /**
     * 判断目录是否存在
     * 不存在创建
     * @param $Dir
     */
    private static function createDir($dir, $mode = 0777)
    {
            if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
            if (!static::createDir(dirname($dir), $mode)) return FALSE;
            return @mkdir($dir, $mode);

    }


    protected $arr = array();

    function findFile($flodername, $filename)
    {
        if (!is_dir($flodername)) {
            return "不是有效目录";
        }
        if ($fd = opendir($flodername)) {
            while($file = readdir($fd)) {
                if ($file != "." && $file != "..") {
                    $newPath = $flodername.'/'.$file;
                    if (is_dir($newPath)) {
                        $this->findFile($newPath, $filename);
                    }
                    if ($file == $filename) {
                        $this->arr[] = $newPath;
                    }
                }
            }
        }
        return $this->arr;

    }





}