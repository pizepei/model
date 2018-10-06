<?php
/**
 * Auth: ax
 * Created: 2018/10/5 22:14
 */

namespace pizepei\model\cache\drive;
use pizepei\model\cache\drive\File;

class RouteFile extends File
{
    /**
     * 设置缓存目录
     * @var string
     */
    protected static $typeCache = 'route';
}