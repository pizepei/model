<?php
/**
 * @title db操作日志表
 */

namespace pizepei\model\db;


class DbOperationLogModel
{
    /**
     * 表结构
     * @var array
     */
    protected $structure = [
        'id'=>[
            'TYPE'=>'uuid','COMMENT'=>'主键uuid','DEFAULT'=>false,
        ],
        'request_id'=>[
            'TYPE'=>'uuid', 'DEFAULT'=>'','COMMENT'=>'请求id',
        ],
        'table'=>[
            'TYPE'=>'varchar(150)', 'DEFAULT'=>'','COMMENT'=>'操作表名称',
        ],
        'type'=>[
            'TYPE'=>"ENUM('1','2','3','4','5')", 'DEFAULT'=>'1', 'COMMENT'=>'1、添加2、删除3、修4、查',
        ],
        'operator'=>[
            'TYPE'=>'json', 'DEFAULT'=>'','COMMENT'=>'操作人信息',
        ],
        'explain'=>[
            'TYPE'=>'varchar(255)', 'DEFAULT'=>false,'COMMENT'=>'操作说明','NULL'=>'',
        ],
        'details'=>[
            'TYPE'=>'json', 'DEFAULT'=>false,'COMMENT'=>'操作细节修改的内容','NULL'=>'',
        ],
        'sql'=>[
            'TYPE'=>'text', 'DEFAULT'=>false,'COMMENT'=>'操作sql','NULL'=>'',
        ],
        'INDEX'=>[
            ['TYPE'=>'KEY','FIELD'=>'table','NAME'=>'table','USING'=>'BTREE','COMMENT'=>'表名称'],
            ['TYPE'=>'KEY','FIELD'=>'type','NAME'=>'type','USING'=>'BTREE','COMMENT'=>'操作类型'],

        ],//索引 KEY `ip` (`ip`) COMMENT 'sss 'user_name


        'PRIMARY'=>'id',//主键
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