<?php

/**
 * 类的对象对应数据库中表。
 * Class Table
 */
class Table
{
    protected static $columns = [];
    protected static $rows = [];
    protected static $handle;
    protected static $count;

    protected $table;
    protected $row = [];

    protected $wheres = [];
    protected $orders = ['update_time' => 'DESC'];

    protected $listFields = ['*'];

    /**
     * 初始化。
     */
    protected function __initialize()
    {
        if (is_null(self::$handle)) {
            self::$handle = DB::getHandle(
                MYSQLI_HOST,
                MYSQLI_USERNAME,
                MYSQLI_PASSWD,
                MYSQLI_DBNAME
            );
        }

        $handle = self::$handle;

        static::$columns = $handle->loadColumns($this->table);
    }

    /**
     * 判断表中是否存在对应字段。
     *
     * @param $name
     *
     * @return bool
     */
    protected function columnExist($name)
    {
        $columns = static::$columns;

        if (
            !is_string($name)
            && key_exists($name, $columns)
            && $this->row
            && !isset($this->row[$columns[$name]])
        ) {
            return false;
        }

        return true;
    }

    /**
     * 获取对应字段的值。
     *
     * @param $name
     *
     * @return null/string
     */
    public function __get($name)
    {
        if (!$this->columnExist($name) && isset($this->row[static::$columns[$name]])) {
            return null;
        }

        if ($name == 'columns') {
            return static::$columns;
        }

        if ($name == 'table') {
            return $this->table;
        }

        if ($name == 'row') {
            $row = $this->row;

            /*
            foreach ($row as $k => $v) {
                if (is_numeric($v) && is_float($v - 0)) $row[$k] = floatval($v);
                if (is_numeric($v) && is_int($v - 0)) $row[$k] = intval($v);
            }
            */

            return $row;
        }

        return $this->row[static::$columns[$name]];
    }

    /**
     * 设置对应字段的值。
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if ($this->columnExist($name)) {
            $conn = self::$handle->getConnHandle();
            $this->row[static::$columns[$name]] = $conn->real_escape_string($value);
        }
    }

    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->__initialize();
    }

    /**
     * 清空记录集。
     */
    protected static function flush()
    {
        static::$rows = [];
    }

    /**
     * 查询记录入栈。
     *
     * @param $row
     */
    protected static function push($row)
    {
        static::$rows[$row['id']] = $row;
    }

    /**
     * 查询记录出栈。
     *
     * @param $row
     *
     * @return object
     */
    protected static function pull($row)
    {
        $id = $row['id'];
        if (isset(static::$rows[$id])) {
            $row = static::$rows[$id];
        }
        unset(static::$rows[$id]);
        return $row;
    }

    /**
     * 根据条件从查询记录集中获取记录。
     *
     * @param $column
     * @param $value
     *
     * @return bool|object
     */
    protected static function getInRowsByItem($column, $value)
    {
        if ($column == 'id') {
            return static::getInRowsById($column);
        }

        if (!key_exists($column, static::$columns)) {
            return false;
        }

        foreach (static::$rows as $id => $row) {
            if ($row[$column] == $value) {
                return $row;
            }
        }

        return false;
    }

    /**
     * 根据主键（id）从查询记录集中获取记录。
     *
     * @param $id
     *
     * @return bool|object
     */
    protected static function getInRowsById($id)
    {
        if (key_exists($id, static::$rows)) {
            return static::$rows[$id];
        }

        return false;
    }

    /**
     * 根据条件获取一条记录。
     * 其中，当传人的参数为integer类型时，表示根据ID获取一条记录。
     * 如果是其他条件，可以用数组表示。
     * 其中$item为多维数组，其中键为字段名。
     * 格式：
     * ['name' => 'xiaoming']
     * ['status' => ['>', 1]]
     * ['end_time' => ['between', ['2017-03-09', 2017-06-23]]]
     * 多维数组以此类推。
     * [
     *     'name'     =>  'xiaoming',
     *     'status'   =>  ['>', 1],
     *     'end_time' =>  ['between', ['2017-03-09', 2017-06-23]]
     * ]
     *
     * @param $item
     *
     * @return $this|bool
     */
    public function get($item)
    {
        if (empty($item) OR $item < 0) {
            return false;
        }

        if (is_array($item)) {
            $row = self::$handle->getByItem($item, $this->table);
        } elseif (is_int(intval($item))) {
            $row = self::$handle->getById($item, $this->table);
        } else {
            return false;
        }

        if (!$row) {
            return false;
        }

        self::push($row);
        $this->row = $row;

        return $this;
    }

    /**
     * 新增一条记录。
     *
     * @return bool/int
     */
    public function insert()
    {
        if ($insertID = self::$handle->insert($this)) {
            $this->row['id'] = $insertID;
            self::push($this->row);
            return $insertID;
        }
        return false;
    }

    /**
     * 更新一条记录。
     *
     * @return bool/int
     */
    public function update()
    {
        if ($affected = self::$handle->update($this)) {
            self::push($this->row);
            return $affected;
        }
        return false;
    }

    /**
     * 删除一条记录。
     *
     * @param int $type
     *
     * @return bool/int
     */
    public function delete($type = SOFT_DELETE)
    {
        if ($affected = self::$handle->delete($this, $type)) {
            self::pull($this->row);
            return $affected;
        }
        return false;
    }

    /**
     * 指定要获取的列表字段。
     *
     * @param array $fields
     *
     * @return object $this
     */
    public function field($fields)
    {
        $this->listFields = $fields;
        return $this;
    }

    /**
     * 自定义列表排序。
     *
     * @param array $orders \
     *
     * @return object $this
     */
    public function order($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * 反转义字符。
     *
     * @param $col
     *
     * @return bool|mixed|string
     */
    public function ipslashes($col)
    {
        $row = $this->row;

        if (!isset($row[$col])) return false;

        $val = $row[$col];

        if (!is_string($col)) return $val;
        else return stripslashes(str_replace('\n', "\n", $val));
    }

    /**
     * 设置筛选内容。
     *
     * @param $searchs array 要筛选的内容。
     *
     * @return $this
     */
    public function setSearchs($searchs)
    {
        $this->wheres = $searchs;
        return $this;
    }

    /*
     * 或额外实现设置筛选内容。
     */
    // public function setSearch($args...)

    /**
     * 获取列表数据。
     *
     * @param int $page  当前页码。
     * @param int $limit 每页记录条数。
     *
     * @return array|bool
     */
    public function getDataList($page = 1, $limit = 20)
    {
        $field_str = implode(',', $this->listFields);

        $conn = self::$handle->getConnHandle();

        $where_str = $this->spliceWheres();
        $order_str = $this->spliceOrders();
        $limit_str = $this->spliceLimit($page, $limit);

        $count = $this->getDataCount();

        $list = [];
        if ($count > 0) {
            $sql = 'SELECT ' . $field_str . ' FROM ' . $this->table . ' '
                . $where_str . ' ' . $order_str . ' ' . $limit_str;

            $mysqli_result = $conn->query($sql);

            if ($mysqli_result && $mysqli_result->num_rows > 0) {
                while ($row = $mysqli_result->fetch_assoc()) {
                    $list[] = method_exists($this, 'filterItem') ? $this->filterItem($row) : $row;
                }
            } else return false;
        }

        return ['count' => $count, 'list' => $list];
    }

    /**
     * 当获取列表的时候，过滤列表项中的数据，也可以包含其他对数据的操作。
     *
     * @param $row
     *
     * @return mixed
     */
    protected function filterItem($row)
    {
        /*
        foreach ($row as $k => $v) {
            if (is_numeric($v) && is_float($v - 0)) $row[$k] = floatval($v);
            if (is_numeric($v) && is_int($v - 0)) $row[$k] = intval($v);
        }
        */

        return $row;
    }

    /**
     * 返回最后一次错误信息。
     *
     * @return string
     */
    public function getDBError()
    {
        $error = self::$handle->getError();
        $errno = self::$handle->getErrorNo();

        return '[' . $errno . ']: ' . $error;
    }

    /**
     * 拼接 where 语句。
     *
     * @return string
     */
    protected function spliceWheres()
    {
        $str = '';
        if ($this->wheres) {
            $wheres = [];

            foreach ($this->wheres as $col => $val) {
                if (is_array($val)) {
                    $ws = [];

                    foreach ($val as $actor => $vs) {
                        $s = $col;

                        if (is_numeric($actor)) continue;

                        $actor = strtolower($actor);

                        switch ($actor) {
                            case 'like':
                                $vs_arr = explode(' ', $vs);
                                $s_arr = [];

                                foreach ($vs_arr as $vs_str) {
                                    $vs_str = trim($vs_str);
                                    if ($vs_str)
                                        $s_arr[] = $col . " LIKE '%" . $vs_str . "%' ";
                                }

                                $s = ' ' . implode(' OR ', $s_arr) . ' ';
                                break;
                            case 'between':
                                if (!is_array($vs) || count($vs) !== 2) continue;
                                if (!isset($vs[0]) || !is_numeric($vs[0])) continue;
                                if (!isset($vs[1]) || !is_numeric($vs[1])) continue;

                                $s .= ' BETWEEN ' . $vs[0] . ' AND ' . $vs[1] . ' ';
                                break;
                            case 'in':
                                if (!is_array($vs) || count($vs) < 1) continue;

                                $s .= ' IN ("' . implode('","', $vs) . '") ';
                                break;
                            case '<':
                                if (!is_numeric($vs)) continue;

                                $s .= ' < ' . $vs . ' ';
                                break;
                            case '>':
                                if (!is_numeric($vs)) continue;

                                $s .= ' > ' . $vs . ' ';
                                break;
                            default:
                                continue;
                        }

                        $ws[] = $s;
                    }

                    $wheres[] = ' (' . implode(' AND ', $ws) . ') ';
                } else {
                    $s = $col;

                    if (is_numeric($val)) $s .= ' = ' . intval($val) . ' ';
                    elseif (is_string($val)) $s .= ' = "' . strval($val) . '" ';
                    else $s .= ' = "' . $val . '" ';

                    $wheres[] = $s;
                }
            }

            $str .= ' WHERE ' . implode(' OR ', $wheres) . ' ';
        }

        return $str;
    }

    /**
     * 拼接 order 语句。
     *
     * @return string
     */
    protected function spliceOrders()
    {
        $str = '';

        if ($this->orders) {
            $orders = [];

            foreach ($this->orders as $col => $sort) {
                if (is_numeric($col)) $orders[] = $sort;
                else $orders[] = $col . ' ' . $sort;
            }

            $str .= ' ORDER BY ' . implode(' , ', $orders) . ' ';
        }

        return $str;
    }

    /**
     * 拼接 limit 语句。
     *
     * @param int $page  当前页码。
     * @param int $limit 每页记录数
     *
     * @return string
     */
    protected function spliceLimit($page, $limit)
    {
        return ' LIMIT ' . ($page - 1) * $limit . ',' . $limit;
    }

    /**
     * 获取记录条数。
     *
     * @return bool|int
     */
    protected function getDataCount()
    {
        $conn = self::$handle->getConnHandle();

        $where_str = $this->spliceWheres();

        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table . ' ' . $where_str;

        $mysqli_result = $conn->query($sql);

        if ($mysqli_result && $mysqli_result->num_rows > 0) {
            $r = $mysqli_result->fetch_assoc();
            return intval($r['count']);
        }

        return false;
    }
}

