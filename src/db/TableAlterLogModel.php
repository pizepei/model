<?php
/**
 * Created by PhpStorm.
 * User: pizepei
 * Date: 2019/1/5
 * Time: 15:51
 * @title 表结构变更日志
 */

namespace pizepei\model\db;


class TableAlterLogModel extends Model
{
    /**
     * 表结构
     * @var array
     */
    protected $structure = [
        'id'=>[
            'TYPE'=>'uuid','COMMENT'=>'主键uuid','DEFAULT'=>false,
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
            'TYPE'=>'text', 'DEFAULT'=>false,'COMMENT'=>'操作说明','NULL'=>'',
        ],
        'details'=>[
            'TYPE'=>'json', 'DEFAULT'=>false,'COMMENT'=>'操作细节json','NULL'=>'',
        ],
        'details'=>[
            'TYPE'=>'json', 'DEFAULT'=>false,'COMMENT'=>'操作细节json','NULL'=>'',
        ],
        'sql'=>[
            'TYPE'=>'text', 'DEFAULT'=>false,'COMMENT'=>'操作sql','NULL'=>'',
        ],
        'write_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP','COMMENT'=>'操作编写时间',
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