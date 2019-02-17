<?php
/**
 * Class Model
 * PDO模型
 * Model::table()->spliceWhere();
 */
namespace pizepei\model\db;
use pizepei\func\Func;
use pizepei\model\cache\Cache;

class Db
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
     * 查询表达式
     * @var array
     */
    protected  $expression= [
        '='=>'EQ',//等于
        '<>'=>'NEQ',//不等于（<>）
        '>'=>'GT',//大于（>）
        '>='=>'EGT',//大于等于（>=）
        '<'=>'LT',//小于（<）
        '<='=>'ELT',//小于等于（<=）
    ];
    private static $pdo = null;
    /**
     * @var string 表名称
     */
    private  $table = '';
    /**
     * @var string 表名称
     */
    private static $altertabl = '';
    /**
     * 数据库名称
     * @var null
     */
    private static $dbName = null;
    /**
     * @var array 数据库连接配置
     */
    private  $config = [];

    public static $alterConfig = [
    ];
    /**
     * 连接参数
     * @var array
     */
    public static $alterParams = [];
    /**
     * 连接参数
     * @var array
     */
    private $options = [];
    /**
     * @var array instance 数据库连接实例
     */
    private  $instance = [];

    private static $alterInstance = [];
    /**
     * @var string 连接信息
     */
    private static $dsn = '';

    /**
     * @var array 当前对象
     */
    private static $staticObject = [];
    /**
     * @var bool 实例化模式 true 重复使用对象  false 创建新对象
     */
    private static $setObjectPattern = true;
    /**
     * 获取完整的表结构
     */
    protected $table_describe = null;

    /**
     * 获取完整的表  索引
     */
    protected $table_index = null;
    /**
     * 缓存完整的表结构
     */
    protected $table_create = null;

    /**
     * 缓存完整的表结index
     */
    protected $table_describe_index = null;

    /**
     * 固定查询数据
     * @var string
     */
    protected $field = '*';

    /**
     * 所有field
     * @var string
     */
    protected $fieldSrr = '*';
    /**
     * 当前模型历史sql
     * @var array
     */
    public $sqlLog = [];
    /**
     * 当前sql
     * @var array
     */
    protected $sql = '';

    /**
     * 历史变量
     * @var array
     */
    protected $variableLog = [];
    /**
     * 绑定value
     * @var array
     */
    protected $execute_bindValue = [];
    /**
     * 主键
     * @var string
     */
    protected $INDEX_PRI = '';
    /**
     * where sql
     * @var string
     */
    protected $wheresql = '';

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
            'TYPE'=>'int(1)',
            'DEFAULT'=>1,//默认值
            'COMMENT'=>'软删除1正常2删除',//字段说明
        ],
        'creation_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'创建时间',//字段说明
        ],
        'update_time'=>[
            'TYPE'=>'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP  ON UPDATE CURRENT_TIMESTAMP',
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
     * 表结构
     * @var array
     */
    protected $structure = [
        'id'=>[
            'TYPE'=>'int',//数据类型（默认不为空）NOT NULL
            'DEFAULT'=>false,//默认值
            'COMMENT'=>'主键id',//字段说明
            'AUTO_INCREMENT'=>true,//自增  默认不
        ],
        'version'=>[
            'TYPE'=>'int',
            'DEFAULT'=>0,//默认值
            'COMMENT'=>'列数据版本号从0开始',//字段说明
        ],
        'del'=>[
            'TYPE'=>'int(1)',
            'DEFAULT'=>1,//默认值
            'COMMENT'=>'软删除1正常2删除',//字段说明
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
        'PRIMARY'=>'id',//主键
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
     * 从表中获取的表版本
     * @var int
     */
    protected $noe_table_version = 0;
    /**
     * @var array 表结构变更日志 版本号=>['表结构修改内容sql','表结构修改内容sql']
     */
    protected $table_structure_log = [
        0=>[
            /**
             * 注意：
             *      格式为 ['表操作的字段','操作类型ADD、DROP、MODIFY、CHANGE','操作内容（为安全起见不包括alter table user）','修改说明','修改人']
             */
            ['new1','ADD','alter table user add COLUMN new1 VARCHAR(20) DEFAULT NULL','修改说明：增加user表的new1字段','pizepei'],
            ['new1','DROP','alter table user DROP COLUMN new2','修改说明：删除一个字段','pizepei'],
            ['new1','MODIFY','alter table user MODIFY new1 VARCHAR(10)','修改说明：修改一个字段的类型','pizepei'],
            ['new1','CHANGE','alter table user CHANGE new1 new4 int;','修改说明：修改一个字段的名称，此时一定要重新指定该字段的类型','pizepei'],
        ],
        /**
         * 修改的内容必须是完整的否则好缺失部分原来的结构
         * ALTER TABLE `oauth_module`.`user_app` MODIFY COLUMN `nickname` timestamp(0) NULL DEFAULT NULL COMMENT '昵称' AFTER `mobile`;
         * ALTER TABLE `数据库`.`表` MODIFY COLUMN `需要修改的字段` 修改后的内容 AFTER `字段在哪个字段后面`;
         */
    ];
    /**
     * 初始化数据：表不存在时自动创建表然后自动插入$initData数据
     *      支持多条
     * @var array
     */
    protected $initData = [

    ];

    /**
     * 当前类名
     * @var string
     */
    protected $ClassName = '';

    public function __construct($instance,$table)
    {
        $this->table = $table;
        $this->instance = $instance;

        /**
         * 判断表是否存在
         */
        if(static::$alterConfig['initTablePattern']){
            $this->setStructure();
        }

        /**
         * 初始化表数据（缓存）
         */
        $this->showCreateTableCache();

    }

    /**
     * 错误处理
     * @param $e
     */
    protected function Exception($e,$type=false)
    {
        //var_dump($e->getCode());
        /**
         * 判断是否存在事务
         */
        if($this->inTransaction()){
            $this->rollBack();
        }
        /**
         * 表不存在42S02   1051 数据表不存在   1146 数据表不存在
         *字段不存在  1054
         * 1062：字段值重复，入库失败  1169：字段值重复，更新记录失败
         */
        if($e->getCode() == '42S02'){
            $this->CreateATableThatDoesNotExist();
        }

        /**
         * 字段不对
         */

        /**
         * 清除sql
         */
        $this->eliminateSql();
        die ("Error!: " . $e->getMessage() . "query<br/>");
    }
    /**
     * 如果表不存在
     *      进行的操作
     */
    protected function CreateATableThatDoesNotExist()
    {
        $this->setStructure();
        $this->showCreateTableCache();
    }
    /**
     * @Author: pizepei
     * @Created: 2018/12/31 22:55
     * @title  设置表结构
     * @explain 判断表是否存在、创建表、判断表结构修改
     *
     */
    protected function setStructure()
    {
        /**
         * 判断表是否存在
         * show databases like 'db_name';表
         * show tables like 'table_name';数据库
         */

        $result_table = $this->query("SELECT table_name FROM information_schema.TABLES WHERE table_name ='{$this->table}'"); //返回一个PDOStatement对象
        //$result_table = $this->instance->query("show tables like '".$this->table."'"); //返回一个PDOStatement对象
        //$result_table = $result_table->fetchAll(\PDO::FETCH_ASSOC); //获取所有
        /**
         * 获取实例化后的类名称
         */
        $ClassName =explode('\\', get_called_class() );
        $this->ClassName = lcfirst(end($ClassName));  //end()返回数组的最后一个元素
        /**
         * 判断是否是db类
         */
        if($this->ClassName != 'db'){
            /**
             * 合并表结构
             * $structureInit
             */
            if(isset($this->structure['INDEX'][0]['TYPE'])){
                foreach($this->structure['INDEX'] as $index){
                    array_unshift($this->structureInit['INDEX'],$index);
                }
            }
            $this->structure  = array_merge($this->structure,$this->structureInit);

            if(empty($result_table)){
                /**
                 * 表不存在
                 * 拼接创建sql
                 */
                /**
                 * 创建表
                 */
                $createTablrSql = "CREATE TABLE `".$this->table."`(";
                foreach($this->structure as $key=>$value){
                    if($key != 'PRIMARY' && $key != 'INDEX'){
                        $value['AUTO_INCREMENT'] =$value['AUTO_INCREMENT']??false;
                        if($value['AUTO_INCREMENT']){
                            $value['AUTO_INCREMENT'] = 'AUTO_INCREMENT';
                        }

                        $value['NULL'] = $value['NULL']??' NOT NULL ';

                        if($value['TYPE'] == 'json'){
                            if(static::$alterConfig['versions'] < 5.7){
                                $value['TYPE'] = 'text';
                            }
                        }else if($value['TYPE'] == 'uuid'){
                            $value['TYPE'] = 'char(36)';
                            if($key != $this->structure['PRIMARY']){
                                $value['DEFAULT'] = self::UUID_ZERO;
                            }
                        }
                        /**
                         * 处理默认值
                         */
                        $value['DEFAULT'] = isset($value['DEFAULT'])?$value['DEFAULT']:false;

                        if(!isset($value['DEFAULT']) || $value['DEFAULT'] === false){

                            $value['DEFAULT'] = '';
                        }else if($value['DEFAULT'] == '' || !empty($value['DEFAULT']) || $value['DEFAULT'] === 0){


                            $value['DEFAULT'] = " DEFAULT '".$value['DEFAULT']."' ";
                        }

                        $createTablrSql .='`'.$key.'` '.$value['TYPE'].$value['NULL'].$value['AUTO_INCREMENT'].' '.$value['DEFAULT']." COMMENT '".$value['COMMENT']."',".PHP_EOL;
                    }
                }
                if(is_array($this->structure['PRIMARY'])){
                    $createTablrSql .="PRIMARY KEY (".$this->structure['PRIMARY'][0].") COMMENT '".$this->structure['PRIMARY'][1]."',".PHP_EOL;
                }else{
                    $createTablrSql .="PRIMARY KEY (".$this->structure['PRIMARY']."),".PHP_EOL;
                }
                /**
                 * 循环处理 index
                 */
                if(isset($this->structure['INDEX'][0]['TYPE'])){

                    //  NORMAL KEY `create_time` (`create_time`) USING BTREE COMMENT '参数'PHP_EOL
                    foreach($this->structure['INDEX'] as $k=>$v){
                        $v['NAME'] = $v['NAME']??'';
                        $NAME = empty($v['NAME'])?'':"`".$v['NAME']."` ";

                        $v['USING'] = $v['USING']??'';
                        $USING = empty($v['USING'])?'':"USING ".$v['USING'];

                        $v['COMMENT'] = $v['COMMENT']??'';
                        $COMMENT = empty($v['COMMENT'])?'':"COMMENT '".$v['COMMENT']."'";

                        $createTablrSql .=$v['TYPE']." ".$NAME." (".$v['FIELD'].") ".$USING.' '.$COMMENT.','.PHP_EOL;
                    }
                }
                $createTablrSql = rtrim($createTablrSql,','.PHP_EOL);
                $createTablrSql .=')'.PHP_EOL."ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='".$this->table_comment.'@'.$this->table_version."'";
                //TableAlterLog
                $this->query($createTablrSql); //创建表
                $TableAlterLog = TableAlterLogModel::table();
                $AlterLog = [
                    'table'=>$this->table,//操作表
                    'field'=>'',//操作field
                    'database'=>static::$alterConfig['database'],//数据库
                    'dsn'=>self::$dsn,//连接
                    'type'=>'ADD-TABLE',//操作类型
                    'operator'=>'system',//操作人
                    'explain'=>'系统自动创建表',//操作说明
                    'details'=>$this->structure,//操作细节
                    'sql'=>$createTablrSql,
                ];
                $TableAlterLog->insert($AlterLog);
                /**
                 * 清空缓存完整的表结构并创建新的缓存
                 */
                $this->emptyStructureCache(true);
                /**
                 * 插入初始化数据
                 */
                if(!empty($this->initData)){
                    $this->insert($this->initData);
                }
            }else{
                /**
                 * show  create  table  tablename;
                 * 表存在 获取版本号
                 * 版本号等于当前版本号$table_version 不做继续操作
                 * 版本号小于当前版本号$table_version
                 *      从当前$table_version的下一个版本开始执行修改sql$table_structure_log
                 *      修改到对应版本号
                 * 会不会出现同时创建或者修改
                 * alter table t_user comment  = '修改后的表注释信息(用户信息表)';
                 */
                $result_table = $this->query("show create table ".$this->table); //返回一个PDOStatement对象
                /**
                 * 获取版本号
                 */
                if(!isset($result_table[0]['Create Table'])){
                    throw new \Exception('Create Table inexistence  '."[$this->table]");
                }
                $explode = explode('@',$result_table[0]['Create Table']);
                $this->noe_table_version = (int)end($explode);

                if($this->table_version != $this->noe_table_version){
                    $this->versionUpdate();
                    /**
                     * 清空缓存完整的表结构
                     */
                    $this->emptyStructureCache(true);
                }
                //echo '版本号一致';
            }
        }

    }

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
     * 版本更新
     */
    protected function versionUpdate()
    {

        /**
         * 获取版本中间值
         * 执行对应版本的修改sql
         *      对应修改表版本号
         * 删除表结构缓存
         *
         * ALTER TABLE table_name
         */
        $strSql = 'ALTER TABLE '.$this->table.' ';
        //echo '当前数据库表版本';
        if($this->noe_table_version>=$this->table_version){
            throw new \Exception("[$this->table] ".'table_version >= noe_table_version');
        }

        $TableAlterLog = TableAlterLogModel::table();

        $i = $this->noe_table_version+1;
        for($i;$i<=$this->table_version;$i++){

            if(isset($this->table_structure_log[$i]) && is_array($this->table_structure_log[$i])){

                $noe_sql_log = '';
                foreach($this->table_structure_log[$i] as $key=>$value){
                    /**
                     * 判断是否是合法操作
                     */
                    if(!isset(self::ALTER_TABLE_STRUCTURE[$value[1]])){
                        throw new \Exception("[$this->table] "."table_version-{$i}-[操作方法] illegality");
                    }
                    if(!isset($value[2])){
                        throw new \Exception("[$this->table] "."table_version-{$i}-操作内容 illegality");
                    }
                    /**
                     * 拼接sql
                     */
                    $noe_sql = $strSql;
                    $noe_sql .=self::ALTER_TABLE_STRUCTURE[$value[1]].$value[2];
                    /**
                     * 执行操作
                     */
                    $result = $this->query($noe_sql);
                    /**
                     * 写入日志
                     */
                    $AlterLog = [
                        'table'=>$this->table,//操作表
                        'field'=>$value[0],//操作field
                        'database'=>static::$alterConfig['database'],//数据库
                        'dsn'=>self::$dsn,//连接
                        'type'=>$value[1],//操作类型
                        'operator'=>$value[4],//操作人
                        'explain'=>$value[3],//操作说明
                        'details'=>$value,//操作细节
                        'sql'=>$noe_sql,
                        'write_time'=>$value[5]??date('Y-m-d H:i:s')
                    ];
                    $TableAlterLog->insert($AlterLog);
                }
                /**
                 * 修改版本号
                 * alter table t_user comment  = '修改后的表注释信息(用户信息表)';
                 */
                $comment_sql = 'ALTER TABLE '.$this->table." COMMENT = '{$this->table_comment}@{$i}'";
                $this->query($comment_sql);

            }else{
                //echo '版本不存在'.PHP_EOL;
            }
        }
    }

    /**
     * 清空缓存完整的表结构
     * @param $type true 清除后超级
     */
    protected function emptyStructureCache($type =false)
    {
        Cache::set(['table_create',$this->table],null,0,'db');
        Cache::set(['table_index',$this->table],null,0,'db');
        Cache::set(['table_describe_index',$this->table],null,0,'db');
        Cache::set(['table_describe',$this->table],null,0,'db');
        Cache::set(['table_describe_FieldStr',$this->table],null,0,'db');
        if($type){
            $this->showCreateTableCache();
        }
    }
    /**
     * 原生sql
     * @param $sql
     * @return mixed
     */
    public function query($sql)
    {
        try {
            /**
             * 建议支持参数绑定
             */
            if(__PATTERN__ == 'CLI'){
                if(__CLI__SQL_LOG__ == 'true' ){
                    $GLOBALS['DBTABASE']['sqlLog'][$this->table.'[query]'][] = $sql;//记录sqlLog
                }
            }else{
                $GLOBALS['DBTABASE']['sqlLog'][$this->table.'[query]'][] = $sql;//记录sqlLog
            }

            $this->safetySql($sql);

            $result = $this->instance->query($sql); //返回一个PDOStatement对象
            return $result = $result->fetchAll(\PDO::FETCH_ASSOC); //获取所有
        } catch (\PDOException $e) {
            $this->Exception($e);
        }
    }

    /**
     * @Author pizepei
     * @Created 2019/2/17 16:54
     *
     * @param $dql
     *
     * @title  方法标题（一般是方法的简称）
     * @explain 一般是方法功能说明、逻辑说明、注意事项等。
     *
     */
    protected function safetySql($dql)
    {

        foreach(static::$alterConfig['safety']['del'] as $key=>$value)
        {
            preg_match($value[0],$dql,$result);
            if(!empty($result)){
                throw new \Exception($value[1]);
            }
        }


    }
    /**
     * @Author: pizepei
     * @Created: 2019/1/1 12:07
     * @param string $table
     * @param bool   $prefix
     * @return bool|mixed|\pizepei\model\db\Db
     * @title  方法标题（一般是方法的简称）
     * @explain 一般是方法功能说明、逻辑说明、注意事项等。
     */
    public static function table($table ='',$prefix=true)
    {

        /**
         * 合并配置
         * 连接数据库
         */
        static::$alterConfig = array_merge( \Dbtabase::DBTABASE,static::$alterConfig);
        /**
         * 获取表名称
         */
        self::getTable($table,$prefix);
        /**
         * type 数据库类型
         * host 数据库主机名
         * dbname 使用的数据库
         * charset 数据库编码
         * hostport 数据库连接端口
         *
         * 注意   ：  host前面是 : [冒号] 其他参数前面是 ; [分号]  所以参数之间 = 之间不能有空格
         */
        self::$dsn = static::$alterConfig['type'].':host='.static::$alterConfig['hostname'].';port='.static::$alterConfig['hostport'].';dbname='.static::$alterConfig['database'].';charset='.static::$alterConfig['charset'];
        /************************资源重复利用*******************************************/
        /**
         * 判断是否重复使用对象
         */
        if(static::$alterConfig['setObjectPattern']){
            /**
             * 重复使用
             *      判断对象是否存在 存在返回
             */
            if(isset(self::$staticObject[self::$dsn . self::$altertabl])){

                return self::$staticObject[self::$dsn . self::$altertabl];
            }
        }else{
            //不使用
            /**
             * 判断是否已经存在pdo连接  存在返回对象
             */
            if(isset(self::$alterInstance[self::$dsn])){
                /**
                 * 实例化模型类
                 *      传如连接标识
                 */
                return new static(self::$alterInstance[self::$dsn],self::$altertabl);
            }
        }
        /**************************资源重复利用*****************************************/

        try {
            /**
             * $dsn 连接信息
             * username 数据库连接用户名
             * password  对应的密码
             * self::$alterParams 连接参数
             **/
            self::$alterInstance[self::$dsn] = new \PDO(self::$dsn, static::$alterConfig['username'], static::$alterConfig['password'],static::$alterConfig['params']); //初始化一个PDO对象
            /**
             * 实例化模型类
             *      传如连接标识
             */
            return self::setObjectPattern();

        } catch (\PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }

    }

    /**
     * @Author: pizepei
     * @Created: 2019/1/1 12:08
     *
     * @param $table
     * @param $prefix 是否使用表前缀
     *
     * @title  获取表名称
     * @explain 一般是方法功能说明、逻辑说明、注意事项等。
     */
    protected static function getTable($table,$prefix)
    {
            /**
             * 获取实例化后的类名称
             */
            $ClassName =explode('\\', get_called_class() );
            $ClassName = lcfirst(end($ClassName));  //end()返回数组的最后一个元素
            $ClassName = str_replace("Model","",$ClassName);

            $strlen = strlen($ClassName);
            $tablestr = '';
            /**
             * 处理大小写和下划线
             */
            for ($x=0; $x<=$strlen-1; $x++)
            {
                $str =ord($ClassName{$x});
                if($str>64 && $str <91 ){
                    $tablestr  .= '_'.strtolower($ClassName{$x});
                }else{
                    $tablestr .=$ClassName{$x};
                }
            }
            /**
             * 拼接
             */
            if($ClassName =='db'){
                /**
                 * 判断是否强制加表前缀
                 */
                self::$altertabl = $table;
                if($prefix){
                    self::$altertabl = static::$alterConfig['prefix'].$table;
                }
            }else{
                self::$altertabl = static::$alterConfig['prefix'].$tablestr;
            }

    }

    /**
     *
     * @param bool $strtoupper 是否大写
     * @param int  $separator 分隔符  45 -       0 空字符串
     * @param bool $parameter true 获取带{ } 的uuid
     * @return string
     */
    public static function getUuid($strtoupper=false,$separator=45,$parameter=false)
    {
        $charid = md5((static::$alterConfig['uuid_identifier']??(Dbtabase::DBTABASE['uuid_identifier']??mt_rand(10000,99999))).uniqid(mt_rand(), true));
        if($strtoupper){$charid = strtoupper($charid);}
        $hyphen = chr($separator);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);

        if($parameter){$uuid = chr(123).$uuid.chr(123);}

        return $uuid;
    }




    /**
     * 创建返回对象
     * @return bool|static
     */
    private  static function setObjectPattern()
    {
        /**
         * 判断返回类型
         */
        if(static::$alterConfig['setObjectPattern']){
            /**
             * 创建保存对象
             */
            return self::$staticObject[self::$dsn.self::$altertabl] = new static(self::$alterInstance[self::$dsn],self::$altertabl);
        }else{
            /**
             * 不保存
             */
            return new static(self::$alterInstance[self::$dsn],self::$altertabl);
        }
    }
    /**
     * 获取表结构
     * 缓存表结构
     */
    protected function showCreateTableCache()
    {
        /**
         * 缓存完整的表结构
         */
        $this->table_create = Cache::get(['table_create',$this->table],'db');
        if(!$this->table_create){
            /**
             * 获取完整的表结构
             */
            //$create = $this->instance->query('show create table '.$this->table); //返回一个PDOStatement对象
            //$this->table_create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

            $this->table_create = $this->query('show create table '.$this->table); //返回一个PDOStatement对象
            /**
             * 缓存cachePeriod
             */
            Cache::set(['table_create',$this->table],$this->table_create,static::$alterConfig['cachePeriod']??0,'db');
        }
        /**
         * 查看索引         show index from table_name
         * MySQL SHOW INDEX会返回以下字段：
            Table
            表的名称。
         *
            Non_unique
            如果索引不能包括重复词，则为0。如果可以，则为1。
         *
            Key_name
            索引的名称。
         *
            Seq_in_index
            索引中的列序列号，从1开始。
         *
            Column_name
            列名称。
         *
            Collation
            列以什么方式存储在索引中。在MySQLSHOW INDEX语法中，有值’A’（升序）或NULL（无分类）。
         *
            Cardinality
            索引中唯一值的数目的估计值。通过运行ANALYZE TABLE或myisamchk -a可以更新。基数根据被存储为整数的统计数据来计数，所以即使对于小型表，该值也没有必要是精确的。基数越大，当进行联合时，MySQL使用该索引的机会就越大。
         *
            Sub_part
            如果列只是被部分地编入索引，则为被编入索引的字符的数目。如果整列被编入索引，则为NULL。
         *
            Packed
            指示关键字如何被压缩。如果没有被压缩，则为NULL。
         *
            Null
            如果列含有NULL，则含有YES。如果没有，则该列含有NO。
         *
            Index_type
            用过的索引方法（BTREE, FULLTEXT, HASH, RTREE）。
         *
            Comment
         */

        /**
         * 缓存完整的 索引
         */
        $this->table_index = Cache::get(['table_index',$this->table],'db');

        if(!$this->table_index){
            /**
             * 获取完整的表索引
             */
            $create = $this->query('show index from '.$this->table); //返回一个PDOStatement对象
            //$create = $this->instance->query('show index from '.$this->table); //返回一个PDOStatement对象
            //$create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

            $this->table_index = $create;
            /**
             * 缓存
             */
            Cache::set(['table_index',$this->table],$this->table_index,0,'db');
        }
        /**
         * 缓存完整的 表结构array
         */
        $this->table_describe_index = Cache::get(['table_describe_index',$this->table],'db');
        $this->table_describe = Cache::get(['table_describe',$this->table],'db');
        $this->field = Cache::get(['table_describe_FieldStr',$this->table],'db');
        if(!$this->table_describe || !$this->table_describe_index || !$this->field){
            /**
             * 获取完整的表结构
             */
            $create = $this->query('describe '.$this->table); //返回一个PDOStatement对象
            
            //$create = $this->instance->query('describe '.$this->table); //返回一个PDOStatement对象
            //$create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

            $describeArrar = [];
            $indexArrar = [];
            $FieldStr = '';

            foreach ($create as $key =>$value){
                $FieldStr .= $value['Field'].',';
                $describeArrar[$value['Field']] = $value;
//                [
//                    'Type'=>$value['Type'],//数据结构
//                    'Null'=>$value['Null'],//是否为null   NO  YES
//                    'Key'=>$value['Key'],//index 类型
//                    'Default'=>$value['Default'],//默认值
//                ];
                /**
                 * 获取index
                 */
                if(!empty($value['Key'])){
                    $indexArrar[$value['Field']] = $value['Key'];
                }
            }
            $this->field = rtrim($FieldStr,',');
            $this->fieldSrr = rtrim($FieldStr,',');
            $this->table_describe_index = $indexArrar;
            $this->table_describe = $describeArrar;
            /**
             * 缓存
             */
            Cache::set(['table_describe_FieldStr',$this->table], $this->field,7200,'db');
            Cache::set(['table_describe_index',$this->table],$this->table_describe_index,7200,'db');
            Cache::set(['table_describe',$this->table],$this->table_describe,7200,'db');
        }
        /**
         * 获取主键
         */
        $this->INDEX_PRI = array_search('PRI',$this->table_describe_index);
        $this->fieldSrr = $this->field;
    }
    /**
     * 通过id查询
     * @param $id
     */
    public function get($id)
    {
        /**
         * 通过id主键查询
         */
        /**
         * 获取主键信息
         */
        /**
         * 主键 PRI   唯一 UNI   普通 MUL
         */

        if(empty($this->INDEX_PRI)){
            throw new \Exception('主键不存在（表结构中没有）');
        }
        /**
         * 准备slq
         */
        $this->sql = 'SELECT '.$this->field.' FROM  `'.$this->table.'` WHERE ( `'.$this->INDEX_PRI.'` = :'.$this->INDEX_PRI.' )';
        /**
         * 准备变量
         */
        $this->execute_bindValue = [':id'=>$id];
        return $this->constructorSend(false);
    }
    /*
     * 获取一条数据
     */
    public function fetch()
    {
        $this->sql = 'SELECT '.$this->field.' FROM `'.$this->table.'` WHERE '.$this->wheresql;
        return $this->constructorSend(false);
    }
    /**
     *获取所有数据
     */
    public function fetchAll()
    {
        $this->sql = 'SELECT '.$this->field.' FROM `'.$this->table.(empty($this->wheresql)?'`':'` WHERE '.$this->wheresql);
        return $this->constructorSend();
    }
    /**
     * 强制使用index
     */
    public function forceIndex($data){
        /**
         * 判断是否是array
         */
        if(!is_array($data)){
            /**
             * 不是  变成array
             */
            $data = [$data];
        }
        $str = '';
        /**
         * 检查体段
         */
        foreach ($data as $k => $v){
            /**
             * 判断是否是index
             */
            if($this->table_describe_index[$v]){
                /**
                 * 判断是否是主键
                 */
                if($this->INDEX_PRI == $v){
                    $str .= 'PRI ,';
                }else{
                    $str .= $v.' ,';
                }
            }
        }
        $str = rtrim($str,',');
        $this->forceIndex_sql = ' force index('.$str.')';
        return $this;
    }
    /**
     *查询构造器
     *查询构造器
     *查询构造器
     */
    public function constructorSend($all = true)
    {
        //可以看到，两者的最后返回结果是不一样的，query 返回的是一个结果集，而execute 返回的是影响行数 或者 是插入数据的id ！~由此可以看出，query 拿来执行 select 更好一些，execute 哪里执行 update | insert | delete 更好些！~~
        try {
            /**
             * 保存历史sql数据
             * 获取完整的sql$this->replace();
             */
            $GLOBALS['DBTABASE']['sqlLog'][$this->table] = $this->replace();

            /**
             * 查询缓存
             * 如果有缓存就使用
             */
            if($this->cacheStatus){
                $cacheData = $this->getCache();
                if($cacheData){ return $cacheData;}
            }
            /**
             * 准备sql
             */

            $sql = $this->instance->prepare($this->sql);
            /**
             * 历史sql
             */
            //$this->sqlLog[] = $this->sql;
            /**
             * 绑定变量
             */
            $create = $sql->execute($this->execute_bindValue);
            /**
             * 历史变量
             * @var array
             */
            $this->variableLog[] = $this->execute_bindValue;

            if($create){

                if($all){
                    $data = $sql->fetchAll(); //获取所有
                }else{
                    $data = $sql->fetch(); //获取一条数据
                }
                /**
                 * 缓存
                 */
                if($this->cacheStatus){
                    $this->setCache($data);
                }
            }else{
                //清除sql影响
                $this->eliminateSql();
                throw new \Exception('查询错误sql错误');
            }
            //清除sql影响
            $this->eliminateSql();
            /**
             * 统计提交
             */
            return $data;
        } catch (\PDOException $e) {
            $this->Exception($e);
        }
    }
    protected $lastInsertId = [];
    /**
     *删除、更新插入 构造器
     */
    public function constructorSendUpdate($type = true)
    {
        try {
            $GLOBALS['DBTABASE']['sqlLog'][$this->table] = $this->replace();
            /**
             * 准备sql
             */
//            var_dump($GLOBALS['DBTABASE']['sqlLog'][$this->table]);
            $sql = $this->instance->prepare($this->sql);
            /**
             * 绑定变量
             */
            $create = $sql->execute($this->execute_bindValue);

            /**
             * 历史变量
             * @var array
             */
            $this->variableLog[] = $this->execute_bindValue;

            $lastInsertId = $this->lastInsertId;
            //清除sql影响
            $this->eliminateSql();
            if($create){
                /**
                 * 判断是更新还是插入
                 */
                if($type){
                    /**
                     * 更新返回受影响行
                     */
                    return $sql->rowCount();
                }else{


                    if($this->ClassName !='db'){
                        if($this->structure[$this->INDEX_PRI]['TYPE'] == 'uuid'){
                            /**
                             * 执行获取uuid
                             */
                            $lastInsertId = "'".implode("','",$lastInsertId)."'";
                            /**
                             *获取已经插入的 InsertId
                             */
                            if(isset($this->structure['update_time'])){
                                $lastInsertIdSql = "SELECT `{$this->INDEX_PRI}`,`update_time` FROM `oauth_module`.`{$this->table}` WHERE {$this->INDEX_PRI}  IN( {$lastInsertId} )";
                            }else{
                                $lastInsertIdSql = "SELECT `{$this->INDEX_PRI}` FROM `oauth_module`.`{$this->table}` WHERE {$this->INDEX_PRI}  IN( {$lastInsertId} )";
                            }

                            return $this->inversion($this->INDEX_PRI,$this->query($lastInsertIdSql),true);
                        }
                    }

                    //var_dump($sql->fetch());
                    //获取最后一个插入数据的ID值
                    return $this->instance->lastInsertId($this->INDEX_PRI);
                }
            }else{
                throw new \Exception('查询错误sql错误');
            }

        } catch (\PDOException $e) {
            $this->Exception($e,true);

        }
    }

    /**
     * 清除sql影响
     */
    protected function eliminateSql()
    {
        $this->sql = '';
        $this->wheresql = '';
        $this->field = $this->fieldSrr;
        $this->cacheStatus = false;
        //清空value
        $this->execute_bindValue =[];
        /**
         * 设置安全模式
         */
        $this->insertSafety = true;
        $this->whereSafety = true;
        /**
         * 插入主键
         */
        $this->lastInsertId = [];
    }

    /**
     * 根据条件查询查询
     * @param array $where
     * @param $safety 设置安全模式
     * @return $this
     */
    public function where(array $where,$safety = true)
    {
        $this->whereSafety = $safety;
        $this->spliceWhere($where);
        return $this;
    }
    /**
     * 拼接sql Where
     * @param array $where
     */
    public function spliceWhere($where = array())
    {
        $i = 0;
        foreach ($where as $k=>$v){
            $kk = explode('|',$k);
            if(count($kk) >1) {
                /**
                 * OR
                 *
                 */
                $orstr = '';
                foreach ($kk as $ork=>$orv){

                    /**
                     * 准备数据
                     */
                    /**
                     * 如果是数组
                     * $where['ip|test'] = ['LIKE','%3'];
                     */
                    if(is_array($v)){

                        $expression = array_search(strtoupper($v[0]),$this->expression);//在数组中搜索键值 ""，并返回它的键名：

                        if($expression == false){
                            $orstr .='  ' .$orv.' '.strtoupper($v[0]).' :'.$orv.$ork.' OR';
                        }else{
                            $orstr .='  ' .$orv.' '.$expression.' :'.$orv.$ork.' OR';
                        }
                        $v = $v[1];


                    }else{
                        /**
                         * 拼接
                         */
                        $orstr .='  ' .$orv.' = :'.$orv.$ork.' OR';
                    }
                    $this->execute_bindValue[':'.$orv.$ork] = $v;
                }
                /**
                 * 删除or
                 */
                $judgeorstr[] = rtrim($orstr,'OR');

            }else{
                /**
                 * 其他 and
                 */
                /**
                 * 判断是否是array
                 *
                 */
                if(is_array($v)){
                    $judgeUnknownStr = '';
                    $judgeUnknown = strtoupper($v[0]);
                    $judgeUnknownValue = $v[1];
                    /**
                     * 拼接
                     */
                    if($judgeUnknown == 'IN'){

                        foreach ($judgeUnknownValue as $ink =>$inv){

                            $judgeUnknownStr .=" :{$k}in{$ink} ,";
                            /**
                             * 数据
                             */
                            $this->execute_bindValue[':'.$k.'in'.$ink] = $inv;
                        }
                        $judgeUnknownARR [] = "{$k} {$judgeUnknown} (".rtrim($judgeUnknownStr,',').') ';

                    }else {
                        /**
                         * 非in  表达式查询
                         */
                        $expression = array_search(strtoupper($judgeUnknown),$this->expression);//在数组中搜索键值 ""，并返回它的键名：

                        if($expression == false){
                            /**
                             * 如果不在列表中
                             *
                             */
                            $judgeUnknownARR []="  {$k} {$judgeUnknown}  :{$k}{$judgeUnknown} ";

                        }else{
                            $judgeUnknownARR []="  {$k} {$expression}  :{$k}{$judgeUnknown} ";
                        }

                        /**
                         * 准备数据
                         */
                        $this->execute_bindValue[':'.$k.$judgeUnknown] = $judgeUnknownValue;
                    }

                }else{
                    /**
                     * 不是默认 =
                     */
                    $judgeAndARR[] =" {$k} = :{$k} ";


                    $this->execute_bindValue[':'.$k] = $v;

                }
            }
        }

        $SQL = ' ';

        /**
         * 拼接
         */
        /**
         * or
         */
        if(isset($judgeorstr)){

            foreach ($judgeorstr as $sqlor){

                $SQL .= ' ( '.$sqlor.') AND';

            }
        }

        /**
         * f  = 条件
         */

        if(isset($judgeUnknownARR)){

            foreach ($judgeUnknownARR as $sqjudge){

                $SQL .= ' ( '.$sqjudge.') AND';
            }

        }

        /**
         * =
         */

        if(isset($judgeAndARR)){

            foreach ($judgeAndARR as $sqjAnd){
                $SQL .= '( '.$sqjAnd.') AND';
            }
        }
        $SQL = rtrim($SQL,'AND');
        $this->wheresql = $SQL;
    }

    /**
     * 设置需要查询的field
     */
    public function field($data)
    {
        $field = '';
        foreach ($data as $k=>$v){

            if(is_int($k)){
                $field .= $v.', ';
            }else{
                $field .= $k.' as '.$v.', ';
            }
        }

        $this->field = rtrim($field,', ');
        return $this;
    }


    /**
     *  默认开启安全模式
     * @var bool
     */
    protected $insertSafety = true;

    /**
     * 批量插入或者插入
     * @param $data
     * @param bool $safety 默认开启安全模式
     * @return mixed
     */
    public function insert($data,$safety = true){

        $this->insertSafety = $safety;
//        /**
//         * 判断是批量还是一条
//         */
        if(!isset($data[1])){
            /**
             * 一条
             */
            $data = [$data];
        }
        /**
         * 过滤字段
         */
        $this->filtrationField($data);
        /**
         * 判断是否是更新
         * 或者是插入
         */
        return $this->ifPudate($data);
    }

    /**
     * @Author: pizepei
     * @Created: 2019/1/4 22:32
     * @param $data
     * @title  过滤字段
     * @explain 一般是方法功能说明、逻辑说明、注意事项等。
     */
    protected function filtrationField(&$data)
    {
        /**
         * 获取表结构
         */
        $this->table_describe;

        foreach ($data as $k=>$v){

            foreach ($v as $kk=>$vv){
                /**
                 * 如果字段不存在
                 */
                if(!isset($this->table_describe[$kk])){
                    /**
                     * 删除
                     */
                    unset($data[$k][$kk]);
                }
            }

        }

    }
    /**
     * 根据主键 判断是否是更新 或者是插入
     * @param $data
     */
    protected function ifPudate($data)
    {
        /**
         * 判断是否存在主键
         * $this->INDEX_PRI
         */
        if(isset($data[0][$this->INDEX_PRI])){
            /**
             * 为更新
             */
            return $this->updateAll($data);
        }else{
            /**
             * 插入
             */
            return $this->insertAll($data);
        }

    }
    /**
     * 批量添加插入
     * @param $data
     */
    protected function insertAll($data)
    {
        /**
         * 插入
         * INSERT INTO `ip_white` ( `ip` ) VALUES ('45466464546'),('45466464546');
         */
        /**
         * 获取field
         *
         * 拼接对应数据
         *      判断是否有主键uuid
         *      是否有配套uuid 字段
         */
        $field = '';
        /**
         * uuid
         */
        if($this->ClassName !='db'){
            if($this->structure[$this->INDEX_PRI]['TYPE'] == 'uuid'){
                $field .= '`'.$this->INDEX_PRI.'`,';
            }
        //DEFAULT
        }

        foreach ($data[0] as $kk=>$vv){
            $field .= '`'.$kk.'`,';
        }
        $field = rtrim($field,',');
        /**
         * VALUES
         */
        $VALUES = '';
        $ii = 1;
        foreach ($data as $k=>$v){
            $VALUES .= '( ';
            /**
             * 自动写入uuid
             */
            if($this->ClassName !='db'){
                if($this->structure[$this->INDEX_PRI]['TYPE'] == 'uuid'){
                    $uuid = self::getUuid(true);
                    $this->lastInsertId[] = $uuid;
                    $arr = [$this->INDEX_PRI=>$uuid];
                    $v = $arr+$v;
                }
            }

            foreach ($v as $kk=>$vv){
                /**
                 * 如果是数组 先判断这个字段是否支持json 是就json_encode
                 */
                if(is_array($vv)){
                    /**
                     * 判断是否是db类
                     */
                    if($this->table_describe[$kk]['Type'] == 'json'){
                        $vv = json_encode($vv,JSON_UNESCAPED_UNICODE);
                    }
                }

                if($this->ClassName !='db'){
                    if($this->INDEX_PRI != $kk && $this->structure[$kk]['TYPE'] =='uuid'){
                        //检测是否是uuid
                            if(strlen($vv) == 36){
                                preg_match('/[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}/',$vv,$preg_match);
                                if($preg_match[0] != $vv){
                                    throw new \Exception('不规范的UUID');
                                }
                            }else{
                                throw new \Exception('不规范的UUID:uuid必须是36位');
                            }
                        }
                }

                if(is_array($vv)){
                    if($this->insertSafety){
                        throw new \Exception('非法的数据参数'.$vv[0]);
                    }
                    /**
                     * 支持插入时使用函数
                     */
                    $this->execute_bindValue[':'.$kk.$ii] = $vv[1];
                    $VALUES .= $vv[0].'( :'.$kk.$ii.' ),';
                }else{
                    $this->execute_bindValue[':'.$kk.$ii] = $vv;
                    $VALUES .= ' :'.$kk.$ii.',';
                }

                $ii++;
            }
            $VALUES = rtrim($VALUES,',');

            $VALUES .= '),';
        }
        $VALUES = rtrim($VALUES,',');

        $this->sql = 'INSERT '.$this->table.' ('.$field.') VALUES '.$VALUES;
        return $this->constructorSendUpdate(false);

    }

    /**
     * 批量更新
     * @param $data
     */
    protected function updateAll($data)
    {

        /**
         *
         * 意思  修改 ip_white表 id WHEN（=）  3357,3358,3359的数据对应的THEN值
         * UPDATE ip_white
        SET ip = CASE id
        WHEN 3357 THEN 54545
        WHEN 3358 THEN 5454545
        WHEN 3359 THEN 5544545
        END
        WHERE id IN (3357,3358,3359)
         *
         */
        /**
         * 拼接sql
         */
        $sql = 'UPDATE '.$this->table.' ';
        /**
         * 拼接详细
         */
        $index = '';
        foreach ($data as $k=>$v){
            /**
             * kk 是 Field
             * $vv  value
             */
            foreach ($v as $kk=>$vv){
                /**
                 * 不是主键
                 * 进行拼接
                 */
                if($kk != $this->INDEX_PRI){

                    /**
                     * 判断uuid格式
                     */
                    if($this->ClassName !='db'){
                        if($this->INDEX_PRI != $kk && $this->structure[$kk]['TYPE'] =='uuid'){
                            //检测是否是uuid
                            if(strlen($vv) == 36){
                                preg_match('/[A-Za-z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}/',$vv,$preg_match);
                                if($preg_match[0] != $vv){
                                    throw new \Exception('不规范的UUID');
                                }
                            }else{
                                throw new \Exception('不规范的UUID:uuid必须是36位');
                            }
                        }
                    }
                    /**
                     * 获取全部Field
                     */

                    /**
                     * 获取对应id 的对应Field 需要修改成的什么
                     * id=>value
                     */

                    $Field[$kk][$v[$this->INDEX_PRI]] = $vv;
                }else{
                    /**
                     * 拼接id
                     */
                    $index .= $vv.',';

                }
            }
        }

        /**
         * 拼接where
         * WHERE id IN (3357,3358,3359)
         */
        $indexsql = ' WHERE '.$this->INDEX_PRI.' IN  ( '.rtrim($index,',').' )';
        /**
         * 拼接主体
         *
        UPDATE ip_white
        SET ip = CASE id

        WHEN 3358 THEN 10008
        WHEN 3357 THEN 10007
        WHEN 3359 THEN 10009
        END,
        create_time = CASE id
        WHEN 3358 THEN 'tetst'
        WHEN 3357 THEN 'test'
        END
        WHERE id IN (3358,3357,3359)
         *
         *
        array (size=2)
        'ip' =>
        array (size=3)
        3358 => int 10008
        3357 => int 10007
        3359 => int 10009
        'create_time' =>
        array (size=2)
        3358 => string '2018-08-12 11:44:41' (length=19)
        3357 => string '2018-08-12 11:44:41' (length=19)
         *
         */
        $Fieldsql = '';
        $i = 1;
        foreach ($Field as $k=>$v){
            if($i ==1) {
                $Fieldsql .= 'SET '.$k.' = CASE ' . $this->INDEX_PRI . ' ';

            }else{
                $Fieldsql .= ' '.$k. ' = CASE ' . $this->INDEX_PRI . ' ';
            }
            $ii =1;
            foreach ($v as $kk=>$vv){
                $ii++;

                if(is_array($vv)){

                    if($this->insertSafety){
                        throw new \Exception('非法的数据参数'.$vv[0]);
                    }
                    /**
                     * 使用函数
                     */
                    $this->execute_bindValue[':when'.$k.$ii] = $kk;
                    $this->execute_bindValue[':when'.$k.'v'.$ii] = $vv[1];
                    $Fieldsql .=  'WHEN  :when'.$k.$ii.' THEN '.$vv[0].'( :when'.$k.'v'.$ii.') ';
                }else{
                    /**
                     * 没有使用函数
                     */
                    $this->execute_bindValue[':when'.$k.$ii] = $kk;
                    $this->execute_bindValue[':when'.$k.'v'.$ii] = $vv;
                    $Fieldsql .=  'WHEN  :when'.$k.$ii.' THEN  :when'.$k.'v'.$ii.' ';
                }


            }
            $Fieldsql .= ' END, ';
            ++$i;
        }
        /**
         * 拼接sql
         */
        $this->sql = $sql.rtrim($Fieldsql,', ').$indexsql;
        return $this->constructorSendUpdate();

    }

    /**
     * 添加(不判断是否是主键)
     * @param $data
     */
    public function add($data)
    {
        /**
         * 判断数组类型
         */
        if(!isset($data[0])){
            $data = [$data];
        }
        /**
         * 过滤字段
         */
        $this->filtrationField($data);

        return $this->insertAll($data);
    }
    /**
     * 删除
     * 支持批量删除
     * 支持软删除
     * @param array $data  ['id']
     * @param bool $type 默认直接删除 false 为软删除
     */
    public function del($data = array(),$type = true)
    {
//        DELETE FROM t_leave_info WHERE leave_info_id IN (640,634,633);

        $sql = " DELETE FROM ".$this->table." WHERE ";

        /**
         * 判断是否存入主键参数
         */
        $sqlindex = '';
        if($data !=[]){
            $this->INDEX_PRI;
            $sqlindex = $this->INDEX_PRI.' IN ( ';
            foreach ($data as $k=>$v){
                $this->execute_bindValue[':del'.$k] = $v;
                $sqlindex .= ':del'.$k.',';
            }
            $sqlindex = rtrim($sqlindex,',');
            $sqlindex .= ') ';
        }
        /**
         * 拼接where
         */

        if($this->wheresql != ''){
            /**
             * 有 where条件
             */
            $this->sql = $sql.' '.$this->wheresql.' AND '.$sqlindex;

        }else{
            /**
             * 没有where sql
             */
            $this->sql = $sql.' ( '.$sqlindex.' ) ';
        }
        return $this->constructorSendUpdate();

    }
    /**
     * 设置缓存关闭状态
     * @var bool
     */
    protected $cacheStatus = false;
    /**
     * 缓存有效期
     * @var int
     */
    protected $period = 0;
    /**
     * 缓存key
     * @var null
     */
    protected $cacheKey = null;
    /**
     * 缓存操作
     * 注意  ： 只有查询使用缓存，其他存在不使用缓存
     * @param $key
     * @param $period
     * @return mixed
     */
    public function cache($key,$period = 0)
    {
        /**
         * 设置缓存开启状态
         */
        $this->cacheStatus = true;
        $this->period = $period;
        $this->cacheKey = $key;
        return $this;
    }
    /**
     * 获取缓存
     */
    protected function getCache()
    {
        /**
         * 判断是否分组
         */
        if(is_array($this->cacheKey) && count($this->cacheKey) == 2){
            $group = $this->cacheKey[0];
            $key = $this->cacheKey[1];
            $this->cacheKey = [$group , $key.md5( $this->sql )];
            $data = Cache::get($this->cacheKey,'db');
        }else{
            $this->cacheKey = $this->cacheKey.md5( $this->sql );
            $data = Cache::get( $this->cacheKey,'db');
        }
        return  $data;
    }
    /**
     * 设置缓存
     */
    protected function setCache($data)
    {
        return Cache::set($this->cacheKey,$data,$this->period,'db');
    }

    /**
     * 完整sql
     */
    protected function replace()
    {
        $sql = $this->sql;
        foreach ($this->execute_bindValue as $k=>$v){
            $sql = str_replace($k,'`'.$v.'`',$sql);
        }
        $this->sqlLog[] = ['Sql'=>$sql,'Cache'=>$this->cacheStatus];
        //var_dump($this->sqlLog);
        return $this->sqlLog;
    }

    /**
     * 事服
     */
    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        if($this->cacheStatus){
            throw new \Exception('开启事务不能使用缓存');
        }
        $this->instance->beginTransaction();
    }
    /**
     * 检查驱动内的一个事务当前是否处于激活。此方法仅对支持事务的数据库驱动起作用。
     */
    public function inTransaction()
    {
        return $this->instance->inTransaction();
    }
    /**
     * 提交事务
     */
    public function commit()
    {
        if($this->cacheStatus){
            $this->instance->rollBack();
            throw new \Exception('开启事务不能使用缓存[已经回滚事务]');
        }
        return $this->instance->commit();
    }
    /**
     * 回滚事务
     */
    public function rollBack()
    {
        return $this->instance->rollBack();
    }

    /**
     * 反转数据
     * @param      $field
     * @param      $data
     * @param bool $type
     * @return array
     */
    public function inversion($field,$data,$type=false)
    {
        $result =[];
        foreach($data as $key=>$value){
            if($type){
                $result[$value[$field]] = $value;
            }else{
                $result[] = $value[$field];
            }
        }
        return $result;
    }


    /**
     * 思考
     *
     * 表不存在创建表  创建表可创建默认数据
     *
     * 简单的where   加是否过滤软删除
     *
     * 通过id 查询 表结构
     *
     * 通过id查询数据
     *
     * 批量添加   修改
     *
     * 软删除
     *
     *过滤器
     *
     */

}