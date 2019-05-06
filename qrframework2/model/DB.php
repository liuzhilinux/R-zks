<?php

/**
 * 通用 MySQLi 扩展类库，实现对数据库的基本读写。
 * Class DB
 */

const SOFT_DELETE = 1;
const DIRECT_DELETE = 2;

class DB
{
    private static $instance;
    private $mysqli_handle;
    private $host;
    private $username;
    private $passwd;
    private $dbname;
    private $port;
    private $charset;

    /**
     * DB constructor.
     *
     * @param $host
     * @param $username
     * @param $passwd
     * @param $dbname
     * @param $port
     * @param $charset
     */
    private function __construct($host, $username, $passwd, $dbname, $port, $charset)
    {
        $this->host = $host;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->dbname = $dbname;
        $this->port = $port;
        $this->charset = $charset;

        $this->mysqli_handle = (new mysqli(
            $this->host,
            $this->username,
            $this->passwd,
            $this->dbname,
            $this->port
        ));

        // 设置将数据库中的数据按原本格式输出（避免整型被转换成字符类型），需要 mysqlnd 驱动支持。
        $this->mysqli_handle->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
        $this->mysqli_handle->set_charset($this->charset);
        $this->tables = $this->loadTables();
    }

    /**
     * 克隆对象。
     *
     * @return mixed
     */
    public function __clone()
    {
        return self::$instance;
    }

    /**
     * 获取链接句柄。
     *
     * @return mysqli
     */
    public function getConnHandle()
    {
        return $this->mysqli_handle;
    }

    /**
     * 以数组的形式返回记录。
     *
     * @param $record
     *
     * @return array|bool
     */
    private function getRowByRecord($record)
    {
        $row = $record->row;
        $columns = array_values($record->columns);

        if ((!$record instanceof Table) OR empty($row)) {
            return false;
        }

        $r = [];

        foreach ($columns as $column) {
            if (isset($row[$column])) {
                $r[$column] = $row[$column];
            }
        }

        return $r;
    }


    /**
     * 建立数据库连接并返回连接句柄。
     *
     * @param     $host
     * @param     $username
     * @param     $passwd
     * @param     $dbname
     * @param int $port
     *
     * @return DB
     */
    public static function getHandle($host, $username, $passwd, $dbname, $port = 3306, $charset = 'UTF8')
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($host, $username, $passwd, $dbname, $port, $charset);
        }

        return self::$instance;
    }

    /**
     * 连接错误说明。
     *
     * @return string
     */
    public function getConnectError()
    {
        return $this->mysqli_handle->connect_error;
    }

    /**
     * 连接错误代码。
     *
     * @return integer
     */
    public function getConnectErrorNo()
    {
        return $this->mysqli_handle->connect_errno;
    }

    /**
     * 获取最后一次错误说明。
     *
     * @return string
     */
    public function getError()
    {
        return $this->mysqli_handle->error;
    }

    /**
     * 获取最后一次错误代码。
     *
     * @return integer
     */
    public function getErrorNo()
    {
        return $this->mysqli_handle->errno;
    }

    /**
     * 初始化时导入数据库中的表。
     *
     * @return array|bool
     */
    private function loadTables()
    {
        $sql = 'SHOW TABLES;';

        if (!$mysqli_result = $this->mysqli_handle->query($sql)) {
            return false;
        }

        $res = [];

        while ($r = $mysqli_result->fetch_row()) {
            $res[] = $r[0];
        }

        return $res;
    }

    /**
     * 将字段名转换为驼峰命名的方式，以方便作为属性名使用。
     *
     * @param $name
     *
     * @return bool|string
     */
    private function nameToCamelCase($name)
    {
        if (!is_string($name) OR $name == '') {
            return false;
        }

        if (!strpos($name, '_')) {
            return $name;
        }

        $str = '';
        $tmp = explode('_', $name);

        $str .= $tmp[0];

        for ($i = 1; $i < count($tmp); $i++) {
            $str .= ucfirst($tmp[$i]);
        }

        return $str;
    }

    /**
     * 初始化表时导入字段列表。
     *
     * @param $table
     *
     * @return bool|array
     */
    public function loadColumns($table)
    {
        if (!is_string($table) && !in_array($table, $this->tables)) {
            return false;
        }
        $sql = 'SHOW COLUMNS FROM ' . $table;

        if (!$mysqli_result = $this->mysqli_handle->query($sql)) {
            return false;
        }

        $res = [];

        if ($mysqli_result->num_rows > 0) {
            while ($r = $mysqli_result->fetch_assoc()) {
                $res[$this->nameToCamelCase($r['Field'])] = $r['Field'];
            }
        }

        return $res;
    }

    /**
     * 通过其他条件获取一条记录。
     *
     * @param $item
     * @param $table
     *
     * @return bool/array
     */
    public function getByItem($item, $table)
    {
        if (empty($item) && is_string($table) && $table == '') {
            return false;
        }

        $sql = 'SELECT * FROM ' . $table . ' WHERE status >= 0 ';
        $where_str = 'AND (';
        $where_arr = [];

        foreach ($item as $col => $val) {
            if (!is_string($col)) {
                return false;
            }

            $t = '`' . $col . '`';

            if (is_array($val) && !empty($val)) {
                if (!is_string($val[0])) {
                    return false;
                }

                $t .= ' ' . $val[0];
                if (is_array($val[1])) {
                    $t .= ' (' . $val[1][0] . ' , ' . $val[1][1] . ') ';
                } else {
                    $t = ' ' . $val[1];
                }
            } elseif (is_string($val) || is_int($val)) {
                $t .= ' = ' . $val;
            } else {
                return false;
            }

            $where_arr[] = $t;
        }

        $where_str .= implode(' AND ', $where_arr) . ') ';

        $sql .= $where_str;

        if ($mysqli_result = $this->mysqli_handle->query($sql)) {
            if ($mysqli_result->num_rows > 0) {
                return $mysqli_result->fetch_assoc();
            }
        }

        return false;
    }

    /**
     * 通过ID获取一条记录。
     *
     * @param $id
     * @param $table
     *
     * @return bool/array
     */
    public function getById($id, $table)
    {
        if (!is_int(intval($id)) && $id <= 0 && is_string($table) && $table == '') {
            return false;
        }

        $sql = 'SELECT * FROM ' . $table . ' WHERE status >= 0 AND id = ' . $id;

        if ($mysqli_result = $this->mysqli_handle->query($sql)) {
            if ($mysqli_result->num_rows > 0) {
                return $mysqli_result->fetch_assoc();
            }
        }

        return false;
    }

    /**
     * 插入一条记录，其中$record为对应记录的对象。
     *
     * @param $record
     *
     * @return bool|int
     */
    public function insert($record)
    {
        if (!$row = $this->getRowByRecord($record)) {
            return false;
        }

        // 其中id、status、create_time、update_time可以为空。
        if (count($row) < count($record->columns) - 4) {
            return false;
        }

        $row['id'] = null;
        $row['status'] = 0;
        $row['update_time'] = time();
        $row['create_time'] = time();

        $sql = 'INSERT INTO ' . $record->table . '  ';

        $col_str = '(';
        $col_arr = [];
        $val_str = '(';
        $val_arr = [];

        foreach ($row as $col => $val) {
            $col_arr[] = '`' . $col . '`';

            if ($col == 'id') {
                $val_arr[] = 'NULL';
            } else {
                $val_arr[] = " '" . $this->mysqli_handle->escape_string($val) . "' ";
            }
        }

        $col_str .= implode(',', $col_arr) . ')';
        $val_str .= implode(',', $val_arr) . ')';

        $sql .= $col_str . ' VALUE ' . $val_str;

        if ($this->mysqli_handle->query($sql)) {
            return $this->mysqli_handle->insert_id;
        }

        return false;
    }

    /**
     * 更新一条记录。
     *
     * @param $record
     *
     * @return bool|int
     */
    public function update($record)
    {
        if (!$row = $this->getRowByRecord($record) OR !isset($row['id'])) {
            return false;
        }

        $row['update_time'] = time();

        $sql = 'UPDATE ' . $record->table . ' SET ';
        $set_arr = [];
        $where_str = ' WHERE id=' . $row['id'];

        foreach ($row as $col => $item) {
            if ($col == 'id' OR is_null($item)) {
                continue;
            }
            $set_arr[] = '`' . $col . '`' . " ='" . $this->mysqli_handle->escape_string($item) . "' ";
        }

        $set_str = implode(',', $set_arr);

        $sql .= $set_str . $where_str;

        if ($this->mysqli_handle->query($sql)) {
            return $this->mysqli_handle->affected_rows;
        }

        return false;
    }

    /**
     * 删除记录。
     *
     * @param     $record
     * @param int $type
     *
     * @return bool|int
     */
    public function delete($record, $type = SOFT_DELETE)
    {
        if (!$row = $this->getRowByRecord($record) OR !isset($row['id'])) {
            return false;
        }

        if ($type == SOFT_DELETE) {
            $sql = 'UPDATE ' . $record->table . ' SET status=-1, update_time=' . time() . ' WHERE id=' . $row['id'];
        } else {
            $sql = 'DELETE FROM ' . $record->table . ' WHERE id=' . $row['id'];
        }

        if ($this->mysqli_handle->query($sql)) {
            return $this->mysqli_handle->affected_rows;
        }

        return false;
    }

}
