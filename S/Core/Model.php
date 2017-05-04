<?php
/**
 * Created by PhpStorm.
 * User: silsuer
 * Date: 2017/1/21
 * Time: 18:38
 */
namespace S;
class Model{
    protected static $db;
    private static $model;
    private  static $tableName;
    private $table_prefix;
    private  $sql;
    private $sql_where= '';
    private $sql_limit = '';
    private $sql_insert = '';
    private $sql_create_table;
    private $sql_save = '';
    private function init(){
        $this->sql_where='';
        $this->sql = '';
        $this->sql_limit = '';
        $this->sql_insert='';
        $this->sql_create_table='';
        $this->sql_save='';
    }
    /**
     * @intro 单例模式 ,获取唯一的对象
     * @param $table_name
     * @return Model
     */
    public static function getInstance($table_name){
        self::$tableName = $table_name;
        if (!self::$model instanceof self){
            self::$model = new self();
        }
        return self::$model;
    }

    /**
     * Model constructor. 实例化本对象，读取配置文件中数据库的配置，并实例化pdo对象，返回本对象
     * @throws S_Exception
     */
    private function __construct(){
        $dsn = C('database');
        $this->table_prefix = $dsn['db_prefix'];
        //  new PDO('mysql:host=localhost;dbname=bocishangai', 'root', '815581420shenC');
        try{
            self::$db = new \PDO('mysql:host='.$dsn['db_host'] . ';dbname=' . $dsn['db_name'] . ';charset='. $dsn['db_charset'],$dsn['db_user'],$dsn['db_password']);
        }catch (S_Exception $e){
            throw new S_Exception('数据库连接出现错误');
        }
        return $this;
    }

    private function __clone(){
    }


    /**
     * @intor 查询函数，要么不传参，要么只能传入一个数组，函数拼接sql语句，进行查询
     * @param null $parm 传入的参数，传入要查询的列
     * @return array 返回查询结果（数组形式）
     * @throws S_Exception
     */
    public function select($parm = null){

        //$parm 要么不传，要么只能传入一个数组
        if (is_array($parm)){
            $sqli = rtrim($this->mutliArr($parm),','); //把传入的数组拼接成字符串
            $this->sql = 'select ' . $sqli . ' from ' .$this->table_prefix . self::$tableName . $this->sql_where ;//拼接sql语句
        }else{
            if (!is_null($parm)){
                throw new S_Exception( __METHOD__ .  '传入的参数错误！');
            }
            $this->sql = "select * from " . $this->table_prefix . self::$tableName . $this->sql_where . $this->sql_limit; //不是数组的话，就查询所有列
        }
//      echo $this->sql;

        $res = self::$db->query($this->sql);
        $res->setFetchMode(\PDO::FETCH_ASSOC);


        $arr = [];
        foreach ($res as $row){
            $arr[]  = $row;
        }
        $this->init();  //由于是单例模式，每次执行完sql语句，要将原本的所有的变量都清空，防止多次执行时出错
        if (empty($arr)){
            return false;
        }
        return $arr;
    }


    /**
     * 把数组连接成字符串
     * @param $array  传入的数组
     * @return string 返回生成的字符串
     */
    public function mutliArr($array){
        $sqli = '';
        foreach ($array as $v){
            $sqli .= $v . ',';
        }
        return $sqli;
    }

    /**
     * @intro where函数，把传进来的参数拼接成where字符串，并赋值给私有变量$sql_where ，然后返回本对象，实现联动执行方法
     * @param null $parm  传入的条件查询参数数组
     * @return $this  返回本对象
     * @throws S_Exception
     */
    public function where($parm = null){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数错误!');
        }else{
            $this->sql_where =  ' where ' . rtrim(trim($this->multiWhere($parm)),'and');
        }
        return $this;
    }

    /**
     * @intro 把传入的数组拼接成where字符串并返回
     * @param $parm  传入的数组
     * @return string  返回拼接的字符串
     * @throws S_Exception
     */
    public function multiWhere($parm){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数错误!');
        }
        $where_prepare = '';
        foreach ($parm as $k => $value) {
            if (is_array($value)){
                $where_prepare .=' '. $k . ' ' . $value[0] . $value[1] . ' and';
            }else{
                $where_prepare .= ' ' .$k . ' = '.'\'' . $value.'\'' . ' and';
            }

        }
        return $where_prepare;
    }

    /**
     * @intro 拼接limit语句，并返回本对象
     * @param $first
     * @param null $second
     * @return $this
     */
    public function limit($first, $second = null){
        if (is_null($second)){
            $this->sql_limit = ' limit ' . $first ;
        }else{
            $this->sql_limit = ' limit ' . $first . ',' . $second;
        }
        return $this;
    }

    public function add($parm = null){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数不正确!');
        }
        $sql_in = rtrim(trim($this->multiInsert($parm)),',');
        $arr_in = $this->arrayInsert($parm);
        $this->sql_insert = 'insert into ' . $this->table_prefix . self::$tableName . ' set ' . $sql_in;
//        echo $this->sql_insert;
//        dump($arr_in);
        $a = self::$db->prepare($this->sql_insert)->execute($arr_in);
//        self::$db->execute($arr_in);
        $this->init();
        return $a;

    }
//    create table ceshi_user(
//        id int(11) not null primary key auto_increment,
//        name varchar(50) not null default '',
//        sex varchar(50) not null default '',
//       idnum varchar(50) not null default '',
//    school varchar(100) not null default ''
//);

    public function multiInsert($parm){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数不正确');
        }
        $sql_in = '';
        foreach ($parm as $k => $v){
            $sql_in .= $k . '=:'. $k . ',';
        }
        return $sql_in;
    }

    public  function arrayInsert($parm){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数不正确');
        }
        $arr = [];
        foreach ($parm as $k => $v){
            $arr[':'.$k] = $v;
        }
        return $arr;
    }

    public function createDatabase(){

    }

    public function createTable($tableName,$str){
//         $dsn = C('database');
        self::$db->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
        $this->sql_create_table = "create table " . $this->table_prefix . $tableName ."( " . $str . " )";
//         echo $this->sql_create_table;
        self::$db->exec($this->sql_create_table);
        $this->init();
        return true;
    }

    public function setField($column,$value){
        //修改
        if (is_int($value)){
            $this->sql_save='update ' . $this->table_prefix . self::$tableName . ' set ' . $column . '=' . $value . $this->sql_where;
        }elseif (is_string($value)){
            $this->sql_save='update ' . $this->table_prefix . self::$tableName . ' set ' . $column . '=\'' . $value .'\''. $this->sql_where;
        }
//         $sql="update buyer set username='ff123' where id>3";


        $res=self::$db->exec($this->sql_save);
        $this->init();
        return $res;

    }

    public  function save($parm){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ . '参数错误');
        }
        $multiSql = trim(rtrim($this->multiSave($parm)),',');
        $this->sql_save = 'update ' . $this->table_prefix . self::$tableName . ' set ' . $multiSql . $this->sql_where;

        $res = self::$db->exec($this->sql_save);
        return $res;
    }
    public function multiSave($parm){
        if (!is_array($parm)){
            throw new S_Exception(__METHOD__ ."参数不正确");
        }
        $str='';
        foreach ($parm as $k =>$v){
            if (is_int($v)){
                $str .= $k . '=' . $v . ',';
            }elseif (is_string($v)){
                $str .=$k . '=\'' . $v .'\',';
            }
        }
        return $str;
    }
}