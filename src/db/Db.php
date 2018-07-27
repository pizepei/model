<?php
/**
 * Class Model
 * PDO模型
 * Model::table()->spliceWhere();
 */
namespace pizepei\model\db;
use pizepei\config\Dbtabase;

class Db
{

    private static $pdo = null;

    private static $table = null;

    private static $dbName = null;
    /**
     * @var 数据库连接配置
     */
    private  $config = [];

    public static $alterConfig = [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => '',
        // 数据库名
        'database'    => 'oauth',
        // 数据库用户名
        'username'    => 'oauth',
        // 数据库密码
        'password'    => '',
        // 数据库连接端口
        'hostport'    => '3306',
        // 数据库连接参数  参考资料http://php.net/manual/zh/pdo.setattribute.php
        'params'      => [
            /**
             * 是否保持长连接   是
             */
            \PDO::ATTR_PERSISTENT => true,
            /**
             *即由MySQL进行变量处理
             */
            \PDO::ATTR_EMULATE_PREPARES =>false,
            /**
             * 指定超时的秒数。并非所有驱动都支持此选项，这意味着驱动和驱动之间可能会有差异。比如，SQLite等待的时间达到此值后就放弃获取可写锁，但其他驱动可能会将此值解释为一个连接或读取超时的间隔。 需要 int 类型。
             */
            \PDO::ATTR_TIMEOUT => 3,
            /**
             * 数据库编码  同 $_pdo->query("SET NAMES utf8")
             */
            \PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8',
            /**
             * PDO::ATTR_ERRMODE：错误报告。他的$value可为：
             *      PDO::ERRMODE_SILENT： 仅设置错误代码。
             *      PDO::ERRMODE_WARNING: 引发 E_WARNING 错误
             *      PDO::ERRMODE_EXCEPTION: 抛出 exceptions 异常。
             */
            \PDO::ATTR_ERRMODE =>\PDO::ERRMODE_EXCEPTION ,
        ],
        // 数据库连接编码默认
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
        // 数据库调试模式
        'debug'       => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'      => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num'  => 1,
        // 指定从服务器序号
        'slave_no'    => '',
        // 是否严格检查字段是否存在
        'fields_strict'  => true,
        //是否保持长连接
        'persistent' => true,
        //实例化模式 true 重复使用对象  false 创建新对象
        'setObjectPattern'=>true,
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

    public function __construct($instance)
    {
        $this->instance = $instance;

    }
    /**
     * @param null $table
     */
    public static function table($table=null)
    {
        /**
         * 合并配置
         * 连接数据库
         */
        self::$alterConfig = array_merge( Dbtabase::DBTABASE,self::$alterConfig);
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
        /************************资源重复利用*******************************************
        /**
         * 判断是否重复使用对象
         */
        if(self::$alterConfig['setObjectPattern']){
            /**
             * 重复使用
             *      判断对象是否存在 存在返回
             */
            if(isset(self::$staticObject[self::$dsn])){
                return self::$staticObject[self::$dsn];
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
                return new static(self::$alterInstance[self::$dsn]);
            }
        }
        /**************************资源重复利用*****************************************
        /**
         * 准备连接参数
         * options
         */
//        self::setAlterParams();

        try {
            /**
             * $dsn 连接信息
             * username 数据库连接用户名
             * password  对应的密码
             * self::$alterParams 连接参数
             **/
            self::$alterInstance[self::$dsn] = new \PDO(self::$dsn, self::$alterConfig['username'], self::$alterConfig['password'],self::$alterParams); //初始化一个PDO对象

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
            return self::$staticObject[self::$dsn] = new static(self::$alterInstance[self::$dsn]);
        }else{
            /**
             * 不保存
             */
            return new static(self::$alterInstance[self::$dsn]);
        }
    }

    /**
     * 测试方法
     */
    public function test()
    {
        /**
         * 1、准备表名称
         *
         * 2、准备sql
         *
         * 3、准备prepare
         *
         * 4、发送
         */
        $query  = 'sql';
        $result = $this->instance->prepare($query);

        //使用query
        $stmt = $this->instance->query('select * from config limit 2'); //返回一个PDOStatement对象

//        $row = $stmt->fetch(); //从结果集中获取下一行，用于while循环
        $rows = $stmt->fetchAll(); //获取所有

        $row_count = $stmt->rowCount(); //记录数，2
        echo '<hr>';
        print_r($row_count);
        echo '<hr>';

//        print_r($rows);

        print_r(\PDO::ATTR_SERVER_INFO);
//        return $result;
    }


    /**
     * 根据条件查询查询
     */
    public function where(array $where)
    {


    }

    /**
     *
     */
    public function spliceWhere($where = array())
    {

        $where['id|user'] = 5;

        $where['sex'] = ['OR','性别'];

        $where['sex'] = '性别';

        foreach ($where as $k=>$v){
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
                $judgeUnknownStr .=" {$k} = :{$k} {$judgeUnknown}";
                /**
                 * 切割
                 */
                $judgeUnknownStr = rtrim($judgeUnknownStr,$judgeUnknown);

            }else{
            /**
             * 不是默认 AND
             */
                $judgeAndStr = '';
                $judgeAndStr .=" {$k} = :{$k} AND";
                /**
                 * 切割
                 */
                $judgeAndStr = rtrim($judgeAndStr,'AND');
            }

            if(empty($judgeAndStr) && empty($judgeUnknownStr)){
                echo '错误';
            }else{
                $WERE = $judgeAndStr.' AND '.$judgeUnknownStr;
            }

        }

        var_dump($WERE);

        return $WERE;

    }

}