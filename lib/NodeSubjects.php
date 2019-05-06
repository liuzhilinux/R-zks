<?php
/**
 * Created by PhpStorm.
 * User: ling
 * Date: 1/30/2019
 * Time: 20:48
 */

class NodeSubjects extends Table
{
    protected $table = 'r_node_subjects';

    /**
     * 获取子节点列表。
     *
     * @param $id
     *
     * @return array|bool
     */
    public function getSubNodes($id)
    {
        $id = intval($id);

        if ($id >= 0) {
            $conn = self::$handle->getConnHandle();

            $sql = 'SELECT `id`, `node_title`, `sequence`, `parent_node_id`, `is_cq`, `is_saq`, ' .
                '`status`, `update_time`, `create_time` FROM `r_node_subjects` ' .
                'WHERE `status` >= 0 AND `parent_node_id` = ' . $id . ' ORDER BY `sequence` ASC';

            $list = [];
            $mysqli_result = $conn->query($sql);

            if ($mysqli_result && $mysqli_result->num_rows > 0) {
                while ($row = $mysqli_result->fetch_assoc()) {
                    $list[] = method_exists($this, 'filterItem') ? $this->filterItem($row) : $row;
                }
            } else return false;

            return $list;
        }

        return false;
    }

    /**
     * 查看特定节点下的子节点数量。
     *
     * @param $parent_node_id
     *
     * @return bool
     */
    public function getCountByParentId($parent_node_id, $except_ids = [])
    {
        $parent_node_id = intval($parent_node_id);

        if ($parent_node_id >= 0) {
            $conn = self::$handle->getConnHandle();

            $sql = 'SELECT COUNT(`id`) AS `r_cnt` FROM `r_node_subjects` WHERE `parent_node_id` = ' . $parent_node_id;

            if (count($except_ids) > 0)
                $sql .= ' AND `id` NOT IN (' . implode(',', $except_ids) . ')';

            $mysqli_result = $conn->query($sql);

            if ($mysqli_result && $mysqli_result->num_rows > 0) {
                while ($row = $mysqli_result->fetch_assoc()) {
                    return $row['r_cnt'];
                }
            } else return false;
        }

        return false;
    }
}