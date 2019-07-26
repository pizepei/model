<?php
/**
 * Auth: pizepei
 * Created: 2018/10/5 21:29
 */

namespace pizepei\model\cache\drive;


interface  Cache
{
    /**
     * 设置缓存接口
     * @param $key [分组 ,key]
     * @param $data
     * @param $period
     * @param $config
     * @return mixed
     */
    public static function set($key,$data,$period,$config);
    /**
     * 获取缓存
     * @param $key
     * @param $config
     * @return mixed
     */
    public static function get($key,$config,bool $info);

}