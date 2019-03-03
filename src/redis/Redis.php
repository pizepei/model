<?php
/**
 * @Author: anchen
 * @Date:   2018-02-10 22:57:52
 * @Last Modified by:   pizepei
 * @Last Modified time: 2018-04-21 23:38:33
 */
namespace pizepei\model\redis;

class Redis
{
    /** @var \Redis */
    protected $redis = null;

    protected $config  = [
        'host'         => '127.0.0.1', // redis主机
        'port'         => 6379, // redis端口
        'password'     => '', // 密码
        'select'       => 0, // 操作库
        'expire'       => 3600, // 有效期(秒)
        'timeout'      => 0, // 超时时间(秒)
        'persistent'   => true, // 是否长连接
        'session_name' => '', // sessionkey前缀
        'type' => 'user', // 链接类型
    ];
    /**
     * @var null|\Redis
     */
    protected static $object = null;

    public function __construct($config = [])
    {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new Exception('not support:redis');
        }
        /**
         * 设置配置
         */
        if($config != []){
            //prefix
            $config = \Config::REDIS;
            $this->config = array_merge($this->config, $config);
        }

        try{
            $redis = new \Redis();
            $redis->connect($this->config['host'], $this->config['port'],1);
            if(!empty($this->config['password'])){
                $redis->auth($this->config['password']);//登录验证密码，返回【true | false】
            }
            $redis->select($this->config['select']);
            $redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']??'');
            $this->redis = $redis;
            return $this->redis;

        }catch(\Exception $e){
            exit(json_encode(['code'=>1001,'Message'=>$e->getMessage()]));
        }
    }

    /**
     * @Author pizepei
     * @Created 2019/3/3 18:07
     *
     * @param $name
     * @return mixed
     *
     * @title 魔术方法
     *
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public static function init($config=[])
    {
        /**
         * 判断是否已经有这个对象
         */
        if(self::$object != null){
            return self::$object;
        }else{
            return self::$object = new static($config);
        }

    }

}
