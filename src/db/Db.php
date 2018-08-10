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


    private static $dbName = null;
    /**
     * @var 数据库连接配置
     */
    private  $config = [];

    public static $alterConfig = [
 //        // 数据库类型
//        'type'        => 'mysql',
//        // 数据库连接DSN配置
//        'dsn'         => '',
//        // 服务器地址
//        'hostname'    => '',
//        // 数据库名
//        'database'    => 'oauth',
//        // 数据库用户名
//        'username'    => 'oauth',
//        // 数据库密码
//        'password'    => '',
//        // 数据库连接端口
//        'hostport'    => '3306',
//        // 数据库连接参数  参考资料http://php.net/manual/zh/pdo.setattribute.php
//        'params'      => [
//            /**
//             * 是否保持长连接   是
//             */
//            \PDO::ATTR_PERSISTENT => true,
//            /**
//             *即由MySQL进行变量处理
//             */
//            \PDO::ATTR_EMULATE_PREPARES =>false,
//            /**
//             * 指定超时的秒数。并非所有驱动都支持此选项，这意味着驱动和驱动之间可能会有差异。比如，SQLite等待的时间达到此值后就放弃获取可写锁，但其他驱动可能会将此值解释为一个连接或读取超时的间隔。 需要 int 类型。
//             */
//            \PDO::ATTR_TIMEOUT => 3,
//            /**
//             * 数据库编码  同 $_pdo->query("SET NAMES utf8")
//             */
//            \PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8',
//            /**
//             * PDO::ATTR_ERRMODE：错误报告。他的$value可为：
//             *      PDO::ERRMODE_SILENT： 仅设置错误代码。
//             *      PDO::ERRMODE_WARNING: 引发 E_WARNING 错误
//             *      PDO::ERRMODE_EXCEPTION: 抛出 exceptions 异常。
//             */
//            \PDO::ATTR_ERRMODE =>\PDO::ERRMODE_EXCEPTION ,
//        ],
//        // 数据库连接编码默认
//        'charset'     => 'utf8',
//        // 数据库表前缀
//        'prefix'      => '',
//        // 数据库调试模式
//        'debug'       => false,
//        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
//        'deploy'      => 0,
//        // 数据库读写是否分离 主从式有效
//        'rw_separate' => false,
//        // 读写分离后 主服务器数量
//        'master_num'  => 1,
//        // 指定从服务器序号
//        'slave_no'    => '',
//        // 是否严格检查字段是否存在
//        'fields_strict'  => true,
//        //是否保持长连接
//        'persistent' => true,
//        //实例化模式 true 重复使用对象  false 创建新对象
//        'setObjectPattern'=>true,
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
     * @var Connection[] 数据库连接实例
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
     * 历史slq
     * @var array
     */
    protected $slqLog = [];

    /**
     * 历史变量
     * @var array
     */
    protected $variableLog = [];


    public function __construct($instance,$table)
    {
        /**
         * 获取实例化后的类名称
         */
        $ClassName =explode('\\', get_called_class() );
        $ClassName = lcfirst(end($ClassName));  //end()返回数组的最后一个元素
        $strlen = strlen($ClassName);
        $tablestr = '';
        for ($x=0; $x<=$strlen-1; $x++)
        {
            $str =ord($ClassName{$x});
            if($str>64 && $str <91 ){
                $tablestr  .= '_'.strtolower($ClassName{$x});
            }else{
                $tablestr .=$ClassName{$x};
            }
        }
        if($ClassName =='Db'){
            $this->table = $table;
        }else{
            $this->table = $tablestr;
        }
        $this->instance = $instance;
        $this->showCreateTableCache();
    }
    /**
     * @param null $table
     */
    public static function table($tabl)
    {

        /**
         * 合并配置
         * 连接数据库
         */
        self::$alterConfig = array_merge( Dbtabase::DBTABASE,self::$alterConfig);

        /**
         * 初始化表名称
         */
        self::$altertabl = self::$alterConfig['prefix'].$tabl;

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
     * @param $array
     */
    public  function getAttribute($array)
    {
        $this->instance->getAttribute($array);
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
    public function showCreateTableCache()
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
//        var_dump($this->table_describe_index);
        if(!$this->table_describe || !$this->table_describe_index){
            /**
             * 获取完整的表结构
             */
            $create = $this->instance->query('describe '.$this->table); //返回一个PDOStatement对象
            $create = $create->fetchAll(\PDO::FETCH_ASSOC); //获取所有

            $describeArrar = [];
            $indexArrar = [];

            foreach ($create as $key =>$value){
                $describeArrar[$value['Field']] = [
                    'Type',$value['Type'],//数据结构
                    'Null',$value['Null'],//是否为null   NO  YES
                    'Key',$value['Key'],//index 类型
                    'Default',$value['Default'],//默认值
                ];
                /**
                 * 获取index
                 */
                if(!empty($value['Key'])){
                    $indexArrar[$value['Field']] = $value['Key'];
                }
            }

            $this->table_describe_index = $indexArrar;
            $this->table_describe = $describeArrar;
            /**
             * 缓存
             */
            Cache::set(['table_describe_index',$this->table],$this->table_describe_index,0,'db');
            Cache::set(['table_describe',$this->table],$this->table_describe,0,'db');
        }

        /**
         * 获取主键
         */
        $this->INDEX_PRI = array_search('PRI',$this->table_describe_index);

    }

    /**
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
            throw new Exception('重键不存在（表结构中）');

        }
        /**
         * 准备slq
         */
        $this->sql = 'SELECT '.$this->field.' FROM  `'.$this->table.'` WHERE ( `'.$this->INDEX_PRI.'` = :'.$this->INDEX_PRI.' )';

        /**
         * 准备变量
         */
        $this->execute_bindValue = [':id'=>$id];

        return $this->constructorSend();
    }

    /*
     * 获取一条数据
     */
    public function fetch()
    {
        $this->sql = 'SELECT '.$this->field.' FROM `'.$this->table.'` WHERE '.$this->sql;
        return $this->constructorSend(false);

    }

    /**
     *获取所有数据
     */
    public function fetchAll()
    {
        $this->sql = 'SELECT '.$this->field.' FROM `'.$this->table.'` WHERE '.$this->sql;
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
     *构造器
     */
    public function constructorSend($all = true)
    {
        //可以看到，两者的最后返回结果是不一样的，query 返回的是一个结果集，而execute 返回的是影响行数 或者 是插入数据的id ！~由此可以看出，query 拿来执行 select 更好一些，execute 哪里执行 update | insert | delete 更好些！~~
        try {
            /**
             * 准备sql
             */
             $sql = $this->instance->prepare($this->sql);
            /**
             * 绑定变量
             */
            $create = $sql->execute($this->execute_bindValue);


            /**
             * 历史slq
             */
             $this->slqLog[] = &$this->sql;
             $GLOBALS['DBTABASE']['slqLog'] = &$this->sql;
            /**
             * 历史变量
             * @var array
             */

            $this->variableLog = $this->execute_bindValue;
            $GLOBALS['DBTABASE']['variableLog'] = &$this->execute_bindValue;

            if($create){

                if($all){
                    $data = $sql->fetchAll(); //获取所有
                }else{
                    $data = $sql->fetch(); //获取一条数据
                }

            }else{
//                $data = $sql->fetchAll(\PDO::FETCH_ASSOC); //获取所有
//                var_dump($this->execute_bindValue);
                throw new \Exception('查询错误sql错误');

            }
            /**
             * 统计提交
             */
            return $data;

        } catch (\PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
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
     *
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
                     * 拼接
                     */
                    $orstr .='  ' .$orv.' = :'.$orv.$ork.' OR';
                    /**
                     * 准备数据
                     */
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
        $this->sql = $SQL;
    }

    /**
     * 甚至需要查询的field
     */
    public function field($data)
    {
        $field = '';
        foreach ($data as $k=>$v){

            if(is_int($k)){
                $field .= $v.', ';
            }else{
                $field .= $k.' as '.$v;
            }
        }

        $this->field = rtrim($field,', ');


        return $this;
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
     *
     * 软删除
     *
     *过滤器
     *
     *
     * 规定查询字段
     *
     *
     */

}