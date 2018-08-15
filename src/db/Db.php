<?php
/**
 * Class Model
 * PDO模型
 * Model::table()->spliceWhere();
 */
namespace pizepei\model\db;
use pizepei\config\Dbtabase;
use pizepei\func\Func;
use pizepei\model\cache\Cache;
class Db
{
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

    public function __construct($instance,$table)
    {
        $this->table = $table;
        $this->instance = $instance;
        $this->showCreateTableCache();
    }
    /**
     * @param null $table
     */
    public static function table($table ='')
    {

        /**
         * 合并配置
         * 连接数据库
         */
        self::$alterConfig = array_merge( Dbtabase::DBTABASE,self::$alterConfig);
        /**
         * 获取表名称
         */
        self::getTable($table);
        /**
         * type 数据库类型
         * host 数据库主机名
         * dbname 使用的数据库
         * charset 数据库编码
         * hostport 数据库连接端口
         *
         * 注意   ：  host前面是 : [冒号] 其他参数前面是 ; [分号]  所以参数之间 = 之间不能有空格
         */
        self::$dsn = self::$alterConfig['type'].':host='.self::$alterConfig['hostname'].';port='.self::$alterConfig['hostport'].';dbname='.self::$alterConfig['database'].';charset='.self::$alterConfig['charset'];
        /************************资源重复利用*******************************************/
        /**
         * 判断是否重复使用对象
         */
        if(self::$alterConfig['setObjectPattern']){
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
            self::$alterInstance[self::$dsn] = new \PDO(self::$dsn, self::$alterConfig['username'], self::$alterConfig['password'],self::$alterConfig['params']); //初始化一个PDO对象
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
     * 获取表名称
     * @param $tabl
     */
    protected static function getTable($table)
    {
            /**
             * 获取实例化后的类名称
             */
            $ClassName =explode('\\', get_called_class() );
            $ClassName = lcfirst(end($ClassName));  //end()返回数组的最后一个元素
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
                self::$altertabl = self::$alterConfig['prefix'].$table;
            }else{
                self::$altertabl = self::$alterConfig['prefix'].$tablestr;
            }
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
        if(self::$alterConfig['setObjectPattern']){
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
            $create = $this->instance->query('show create table '.$this->table); //返回一个PDOStatement对象
            $this->table_create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有
            /**
             * 缓存
             */
            Cache::set(['table_create',$this->table],$this->table_create,0,'db');
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
            $create = $this->instance->query('show index from '.$this->table); //返回一个PDOStatement对象
            $create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

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
            $create = $this->instance->query('describe '.$this->table); //返回一个PDOStatement对象
            $create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

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
        $this->sql = 'SELECT '.$this->field.' FROM `'.$this->table.'` WHERE '.$this->wheresql;
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
            $this->sqlLog[] = $this->sql;
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
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }

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
                    $this->instance->lastInsertId();
                    //获取最后一个插入数据的ID值
                    return $this->instance->lastInsertId();
                }
            }else{
                throw new \Exception('查询错误sql错误');
            }

        } catch (\PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
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
    }

    /**
     * 根据条件查询查询
     */
    public function where(array $where)
    {
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
     * 批量插入或者插入
     * @param $data
     */
    public function insert($data){
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
     * 过滤字段
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
         */
        $field = '';
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
            foreach ($v as $kk=>$vv){
                $this->execute_bindValue[':'.$kk.$ii] = $vv;
                $VALUES .= ' :'.$kk.$ii.',';
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
                $this->execute_bindValue[':when'.$k.$ii] = $kk;
                $this->execute_bindValue[':when'.$k.'v'.$ii] = $vv;
                $Fieldsql .=  'WHEN  :when'.$k.$ii.' THEN  :when'.$k.'v'.$ii.' ';

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
        $this->instance->commit();
    }
    /**
     * 回滚事务
     */
    public function rollBack()
    {
        $this->instance->rollBack();
    }
    /**
     * 思考
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
     *
     *
     *
     */

}