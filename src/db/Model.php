<?php
/**
 * Created by PhpStorm.
 * User: 84873
 * Date: 2018/7/30
 * Time: 16:56
 */

namespace pizepei\model\db;
use pizepei\model\db\Db;

class Model extends Db
{
    /**
     * uuid 默认值
     */
    const UUID_ZERO = '00000000-0000-0000-0000-000000000000';

    const DEFAULT =[
        'uuid'=>'00000000-0000-0000-0000-000000000000',//uuid
        'json'=>'[]',//json
        'geometry'=>"POINT('0 0')",//空间
    ];
    /**
     * 可操作的字段操作
     */
    const ALTER_TABLE_STRUCTURE = [
        'ADD'=>' ADD COLUMN ',//增加
        'DROP'=>' DROP COLUMN ',//删除
        'MODIFY'=>' MODIFY ',//修改结构（不修改字段的名称）
        'CHANGE'=>' CHANGE ',//完整的修改字段（包括字段的名称和结构）
        /**
         * 索引操作
         * 增加索引 ALTER TABLE table_name ADD [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name (index_col_name,...) [USING index_type]
         */
        'ADD-INDEX'=>' ADD  INDEX',//
        'ADD-UNIQUE'=>' ADD UNIQUE INDEX',//增加索引 ALTER TABLE table_name ADD [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name (index_col_name,...) [USING index_type]
        'ADD-FULLTEXT'=>' ADD FULLTEXT INDEX',//增加索引 ALTER TABLE table_name ADD [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name (index_col_name,...) [USING index_type]
        'ADD-SPATIAL'=>' ADD SPATIAL INDEX',//增加索引 ALTER TABLE table_name ADD [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name (index_col_name,...) [USING index_type]
        'DROP-INDEX'=>' DROP INDEX  ',//删除ALTER TABLE table_name  DROP INDEX index_name;  (修改索引 先删除掉原索引，再根据需要创建一个同名的索引)
    ];

    /**
     * 表结构(初始化)
     * 默认
     *      version（列数据版本号从0开始）
     *      update_time （更新时间）
     *      creation_time （创建时间 默认NORMAL普通索引）
     * @var array
     */
    protected $structureInit = [
        'version'=>[
            'TYPE'=>'int',
            'DEFAULT'=>1,//默认值
            'COMMENT'=>'列数据版本号从0开始',//字段说明
        ],
        'del'=>[
            'TYPE'=>"ENUM('1','2','3')",
            'DEFAULT'=>1,//默认值 1正常 2 删除 3 异次元
            'COMMENT'=>'软删除默认值1， 1正常 2 删除 3 异次元',//字段说明
        ],
        'creation_time'=>[
            'TYPE'=>'timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)',
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'创建时间',//字段说明
        ],
        'update_time'=>[
            'TYPE'=>'timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)  ON UPDATE CURRENT_TIMESTAMP(6)',
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'更新时间',//字段说明
        ],
        /**
         * UNIQUE 唯一
         * SPATIAL 空间
         * NORMAL 普通 key
         * FULLTEXT 文本
         */
        'INDEX'=>[
            //  NORMAL KEY `create_time` (`create_time`) USING BTREE COMMENT '参数'
            ['TYPE'=>'key','FIELD'=>'creation_time','NAME'=>'creation_time','USING'=>'BTREE','COMMENT'=>'创建时间'],
        ],//索引 KEY `ip` (`ip`) COMMENT 'sss '
    ];

    /**
     * @var string 表备注（不可包含@版本号关键字）
     */
    protected $table_comment = '模拟表';
    /**
     * @var int 模型定义的 表版本（用来记录表结构版本）在表备注后面@$table_version
     */
    protected $table_version = 0;
    /**
     * 初始化数据：表不存在时自动创建表然后自动插入$initData数据
     *      支持多条
     * @var array
     */

    protected $initData = [

    ];



    /**
     * @var array 表结构变更日志 版本号=>['表结构修改内容sql','表结构修改内容sql']
     */
    protected $table_structure_log = [
//        0=>[
            /**
             * 注意：
             *      格式为 ['表操作的字段','操作类型ADD、DROP、MODIFY、CHANGE','操作内容（为安全起见不包括alter table user）','修改说明','修改人']
             */
//            ['new1','ADD','new1 VARCHAR(20) DEFAULT NULL','修改说明：增加user表的new1字段','pizepei'],//可以使用UUID 关键字
//            ['new1','DROP','new2','修改说明：删除一个字段','pizepei'],
//            ['new1','MODIFY','VARCHAR(10)','修改说明：修改一个字段的类型','pizepei'],
//            ['new1','CHANGE',' new1 new4 int;','修改说明：修改一个字段的名称，此时一定要重新指定该字段的类型','pizepei'],
//        ],
        /**
         * 修改的内容必须是完整的否则好缺失部分原来的结构
         * ALTER TABLE `oauth_module`.`user_app` MODIFY COLUMN `nickname` timestamp(0) NULL DEFAULT NULL COMMENT '昵称' AFTER `mobile`;
         * ALTER TABLE `数据库`.`表` MODIFY COLUMN `需要修改的字段` 修改后的内容 AFTER `字段在哪个字段后面`;
         */
    ];

}