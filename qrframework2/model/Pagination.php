<?php

/**
 * 分页相关基础类。
 * Class Pagination
 */
abstract class Pagination
{
    protected $mysqli_handle;
    protected $table;
    protected $count;

    public $currentPageNo;
    public $lastPageNo;


    /**
     * 定义每页显示记录数量。
     * @var
     */
    public $listRows;

    /**
     * 定义排序。
     *
     * 格式：
     * ['col']
     * ['col_2' => 'DESC']
     * @var
     */
    protected $orders = [];

    /**
     * 定义关联表。
     *
     * 格式：
     * ['tbl_name' => ['key', 'foriegn_key']]
     * ['tbl_name_2' => ['left', 'key', 'foriegn_key']]
     * @var array
     */
    protected $joinTable = [];

    /**
     * 用于填充要查询的字段。
     *
     * 格式：
     * ['col' => 'as_col']
     * @var array
     */
    protected $columns = [];


    /**
     * 初始化。
     */
    protected function __initialize()
    {
        $db = DB::getHandle(
            MYSQLI_HOST,
            MYSQLI_USERNAME,
            MYSQLI_PASSWD,
            MYSQLI_DBNAME
        );
        $this->mysqli_handle = $db->getConnHandle();
    }

    /**
     * 计算表中记录的总数。
     * @return int
     */
    protected function getCount()
    {
        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table . ' ';

        if ($this->joinTable) {
            $sql .= $this->join();
        }

        $sql .= $this->where();

        $conn = $this->mysqli_handle;

        if ($mysqli_result = $conn->query($sql)) {
            if ($mysqli_result->num_rows > 0) {
                $r = $mysqli_result->fetch_assoc();
                return intval($r['count']);
            }
        }

        return 0;
    }

    /**
     * 拼接 JOIN 语句。
     * @return string
     */
    protected function join()
    {
        $join = '';
        $tableList = $this->joinTable;
        $tbl_current = $this->table;

        foreach ($tableList as $tbl_name => $item) {
            if (count($item) == 2) {
                $join .= ' JOIN ' . $tbl_name . ' ON ' . $tbl_current . '.' . $item[0] . ' = ' . $tbl_name . '.' . $item[1];
            } else {
                $join .= $item[0] . ' JOIN ' . $tbl_name . ' ON ' . $tbl_current . '.' . $item[1] . ' = ' . $tbl_name . '.' . $item[2];
            }
        }

        return $join;
    }


    /**
     * 拼接请求字段。
     * @return string
     */
    public function columns()
    {
        $columns = $this->columns;
        $col_arr = [];
        $col_str = '';

        foreach ($columns as $col => $as_col) {
            $col_arr[] = $col . ' AS ' . $as_col;
        }

        $col_str = implode(' , ', $col_arr);

        return $col_str;
    }

    /**
     * 拼接 ORDER BY 语句。
     * @return string
     */
    public function orderBy()
    {
        if (empty($this->orders)) {
            return '';
        }

        $or = ' ORDER BY ';
        $or_arr = [];

        foreach ($this->orders as $col => $by) {
            if (is_int($col)) {
                $or_arr[] = $by;
            } else {
                $or_arr[] = $col . ' ' . $by;
            }
        }

        $or .= implode(' , ', $or_arr);
        return $or;
    }

    /**
     * 拼接 LIMIT 语句。
     * @return string
     */
    protected function limit()
    {
        $m = ($this->currentPageNo - 1) * $this->listRows;
        $n = $this->listRows;

        $m = $m < 0 ? 0 : $m;

        return ' LIMIT ' . $m . ' , ' . $n;
    }

    /**
     * 拼接 WHERE 语句。
     * @return string
     */
    protected function where()
    {
        return ' WHERE status >= 0 ';
    }

    /**
     * 输出分页查询结果。
     * @return array|bool
     */
    protected function showList()
    {
        $sql = 'SELECT ';

        if ($this->columns) {
            $sql .= $this->columns();
        } else {
            $sql .= ' * ';
        }

        $sql .= ' FROM ' . $this->table . ' ';

        if ($this->joinTable) {
            $sql .= $this->join();
        }

        $sql .= $this->where() . $this->orderBy() . $this->limit();

        if ($mysqli_result = $this->mysqli_handle->query($sql)) {
            if ($mysqli_result->num_rows > 0) {
                $r = [];

                while ($row = $mysqli_result->fetch_assoc()) {
                    if (isset($row['id'])) {
                        $r[$row['id']] = $row;
                    } else {
                        $r[] = $row;
                    }
                }

                return $r;
            }
        }

        return false;
    }
}

