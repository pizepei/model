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
     * 可操作的字段操作
     */
    const ALTER_TABLE_STRUCTURE = [
        'ADD'=>' ADD COLUMN ',//增加
        'DROP'=>' DROP COLUMN ',//删除
        'MODIFY'=>' MODIFY ',//修改结构（不修改字段的名称）
        'CHANGE'=>' CHANGE ',//完整的修改字段（包括字段的名称和结构）
        'ADD-INDEX'=>' ADD ',//增加索引
    ];

    protected $structureInit = [
        'version'=>[
            'TYPE'=>'int',
            'DEFAULT'=>0,//默认值
            'COMMENT'=>'列数据版本号从0开始',//字段说明
        ],
        'update_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP  ON UPDATE CURRENT_TIMESTAMP',
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'更新时间',//字段说明
        ],
        'creation_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'创建时间',//字段说明
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



}