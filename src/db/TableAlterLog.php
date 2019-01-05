<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/1/5
 * Time: 15:51
 * @title 表结构变更日志
 */

namespace pizepei\model\db;


class TableAlterLog extends Db
{
    /**
     * 表结构
     * @var array
     */
    protected $structure = [
        'id'=>[
            'TYPE'=>'int','DEFAULT'=>false,'COMMENT'=>'主键id','AUTO_INCREMENT'=>true,
        ],
        'database'=>[
            'TYPE'=>'varchar(150)', 'DEFAULT'=>'','COMMENT'=>'数据库名称',
        ],
        'table'=>[
            'TYPE'=>'varchar(150)', 'DEFAULT'=>'','COMMENT'=>'表名称',
        ],
        'field'=>[
            'TYPE'=>'varchar(150)', 'DEFAULT'=>'','COMMENT'=>'字段创建表时为空',
        ],
        'type'=>[
            'TYPE'=>'varchar(12)', 'DEFAULT'=>'','COMMENT'=>'操作类型ALTER_TABLE_STRUCTURE常量，创建表为ADD-TABLE',
        ],
        'operator'=>[
            'TYPE'=>'varchar(255)', 'DEFAULT'=>'','COMMENT'=>'操作人',
        ],
        'explain'=>[
            'TYPE'=>'text', 'DEFAULT'=>'','COMMENT'=>'操作说明',
        ],
        'details'=>[
            'TYPE'=>'json', 'DEFAULT'=>'','COMMENT'=>'操作细节json',
        ],
        'details'=>[
            'TYPE'=>'json', 'DEFAULT'=>'','COMMENT'=>'操作细节json',
        ],
        'sql'=>[
            'TYPE'=>'text', 'DEFAULT'=>'','COMMENT'=>'操作sql',
        ],
        'write_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DEFAULT'=>'','COMMENT'=>'操作编写时间',
        ],
        'dsn'=>[
            'TYPE'=>'varchar(255)', 'DEFAULT'=>'','COMMENT'=>'数据库连接dsn',
        ],
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