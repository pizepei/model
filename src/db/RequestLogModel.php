<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/2/1
 * Time: 11:30
 * @title 请求日志
 */

namespace pizepei\model\db;


class RequestLogModel
{
    /**
     * 表结构
     * @var array
     */
    protected $structure = [
        'id'=>[
            'TYPE'=>'uuid','DEFAULT'=>false,'COMMENT'=>'主键id','AUTO_INCREMENT'=>true,
        ],
        'province'=>[
            'TYPE'=>'varchar(50)', 'DEFAULT'=>'','COMMENT'=>'省',
        ],
        'city'=>[
            'TYPE'=>'varchar(50)', 'DEFAULT'=>'','COMMENT'=>'市',
        ],
        'isp'=>[
            'TYPE'=>'varchar(35)', 'DEFAULT'=>'','COMMENT'=>'网络供应商',
        ],
        'NetworkType'=>[
            'TYPE'=>'varchar(20)','DEFAULT'=>'','COMMENT'=>'网络类型（从请求头中获取）',
        ],
        'Ipanel'=>[
            'TYPE'=>'varchar(35)', 'DEFAULT'=>'', 'COMMENT'=>'浏览器内核',
        ],
        'language'=>[
            'TYPE'=>'varchar(35)', 'DEFAULT'=>'', 'COMMENT'=>'从浏览器获取语言',
        ],
        'Os'=>[
            'TYPE'=>'varchar(35)', 'DEFAULT'=>'', 'COMMENT'=>'从浏览器获取操作系统',
        ],
        'IpInfo'=>[
            'TYPE'=>'json', 'DEFAULT'=>false, 'COMMENT'=>'ip信息','NULL'=>'',
        ],
        'Build'=>[
            'TYPE'=>'json', 'DEFAULT'=>false, 'COMMENT'=>'移动设备信息','NULL'=>'',
        ],
        'NetType'=>['TYPE'=>'varchar(35)', 'DEFAULT'=>'', 'COMMENT'=>'=从ip获取的移动设备网络',],

        'ip'=>[
            'TYPE'=>'varchar(15)', 'DEFAULT'=>'', 'COMMENT'=>'ip地址',
        ],
        'point'=>[
            'TYPE'=>'geometry', 'DEFAULT'=>false, 'COMMENT'=>'经纬度','NULL'=>'',
        ],
        'user_agent'=>[
            'TYPE'=>'varchar(255)', 'DEFAULT'=>'', 'COMMENT'=>'user_agent全部信息',
        ],

        'PRIMARY'=>'id',//主键

        'INDEX'=>[
            //  NORMAL KEY `create_time` (`create_time`) USING BTREE COMMENT '参数'
            ['TYPE'=>'key','FIELD'=>'ip','NAME'=>'ip','USING'=>'BTREE','COMMENT'=>'ip地址'],
        ],//索引 KEY `ip` (`ip`) COMMENT 'sss '
    ];
    /**
     * @var string 表备注（不可包含@版本号关键字）
     */
    protected $table_comment = '表结构变更日志';
    /**
     * @var int 表版本（用来记录表结构版本）在表备注后面@$table_version
     */
    protected $table_version = 0;
}