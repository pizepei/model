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
class SysFile extends File
{
    /**
     * 设置缓存目录
     * @var string
     */
    protected static $typeCache = 'sys';

}