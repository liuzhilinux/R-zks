<?php
/**
 * Created by PhpStorm.
 * User: ling
 * Date: 1/22/2019
 * Time: 22:30
 */

/**
 * 用户登录。
 */
function login()
{
    $request = $GLOBALS['ev_request'];
    if (
        !isset($request['user']) || !isset($request['pass']) ||
        !$request['user'] || !$request['pass']
    ) super_dump(false, -1, 'User name or password not filled in.');

    $user = $request['user'];
    $pass = $request['pass'];
    $users = $GLOBALS['users'];

    if (!isset($users[$user]) || $users[$user] != $pass)
        super_dump(false, -1, 'User name or password fields.');
    else {
        $_SESSION['sess_mk'] = true;
        super_dump(['user_profile' => ['user_name' => $user]]);
    }
}

/**
 * 用户登出。
 */
function logout()
{
    session_unset();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    super_dump(true);
}

/**
 * 输出配置供前端页面使用。
 */
function base_config()
{
    ob_start();
    header('Content-Type: application/javascript');

    echo 'const BASE_FACT = "' . BASE_FACT . '";';

    ob_end_flush();
}

// **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** ****

/**
 * 添加节点。
 */
function create_node_subject()
{
    $request = $GLOBALS['ev_request'];

    validate_subject($request);

    $model = new NodeSubjects;

    $model->nodeTitle = $request['node_title'];
    $model->parentNodeId = $request['parent_node_id'];
    $model->sequence = isset($request['sequence']) ? intval($request['sequence']) : 0;
    $model->isCq = isset($request['is_cq']) ? intval($request['is_cq']) : 0;
    $model->isSaq = isset($request['is_saq']) ? intval($request['is_saq']) : 0;
    $model->subjects = isset($request['subjects']) ? $request['subjects'] : '';

    if ($insert_id = $model->insert()) super_dump($insert_id);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 更新节点。
 */
function update_node_subject()
{
    $request = $GLOBALS['ev_request'];

    validate_subject($request, true);

    $id = $request['id'];

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    $subject->nodeTitle = $request['node_title'];
    $subject->parentNodeId = $request['parent_node_id'];
    $subject->sequence = isset($request['sequence']) ? intval($request['sequence']) : 0;
    $subject->isCq = isset($request['is_cq']) ? intval($request['is_cq']) : 0;
    $subject->isSaq = isset($request['is_saq']) ? intval($request['is_saq']) : 0;
    $subject->subjects = $request['subjects'];

    if ($affected_row = $subject->update()) super_dump($affected_row);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 更新节点内容。
 */
function update_node_subject_content()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? $request['id'] : 0;
    $content = isset($request['subjects']) ? $request['subjects'] : false;

    if ($id <= 0) super_dump(false, -1, 'Illegal request![id]');
    if (false === $content) super_dump(false, -1, 'Illegal request![subjects]');

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    $subject->subjects = $content;

    if ($affected_row = $subject->update()) super_dump($affected_row);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 更新节点类型。
 */
function update_node_qtype()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? $request['id'] : 0;

    if ($id <= 0) super_dump(false, -1, 'Illegal request![id]');
    if (!isset($request['is_cq']) && !isset($request['is_saq']))
        super_dump(false, -1, 'Illegal request![cqsaq]');

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    unset($request['id']);
    $map = ['is_cq' => 'isCq', 'is_saq' => 'isSaq'];

    foreach ($request as $k => $v) {
        if (array_key_exists($k, $map)) {
            $pr = $map[$k];
            if (in_array($v, [0, 1])) $subject->$pr = $v;
        }
    }

    if ($affected_row = $subject->update()) super_dump($affected_row);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 修改节点从属。
 */
function update_node_parent_id()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? $request['id'] : 0;
    $parent_node_id = isset($request['parent_node_id']) ? $request['parent_node_id'] : -1;

    if ($id <= 0) super_dump(false, -1, 'Illegal request![id]');
    if ($parent_node_id < 0) super_dump(false, -1, 'Illegal request![parent_node_id]');

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    $subject->parentNodeId = $parent_node_id;
    $subject->sequence = $model->getCountByParentId($parent_node_id, [$id]);

    if ($affected_row = $subject->update()) super_dump($affected_row);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 获取节点和子节点列表。
 */
function get_node_subject()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? intval($request['id']) : 0;

    if ($id <= 0) super_dump(false, -1, 'Illegal request![id]');

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    $subject = $subject->row;

    $subject['subjects'] = $model->ipslashes('subjects');

    if (isset($request['with_sub_nodes']) && 1 == $request['with_sub_nodes']) {
        $model = new NodeSubjects;
        $sub_nodes = $model->getSubNodes($id);
        $subject['sub_nodes'] = $sub_nodes;
    }

    super_dump($subject);
}

/**
 * 获取当前节点标题和子节点列表。
 */
function get_nodes()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? intval($request['id']) : 0;

    if ($id < 0) super_dump(false, -1, 'Illegal request![id]');

    $model = new NodeSubjects;

    if (0 === $id) {
        $current_node = [
            'id' => 0,
            'node_title' => 'Main',
            'parent_node_id' => 0,
            'is_cq' => 0,
            'is_saq' => 0,
        ];

        $sub_nodes = $model->getSubNodes(0);
    } else {
        if (!$subject = $model->get($id))
            super_dump(false, -1, 'Records do not exist or have been deleted.');

        $current_node = [
            'id' => $subject->id,
            'node_title' => $subject->nodeTitle,
            'parent_node_id' => $subject->parentNodeId,
            'is_cq' => $subject->isCq,
            'is_saq' => $subject->isSaq,
        ];

        $sub_nodes = $model->getSubNodes($subject->id);
    }

    super_dump(['current_node' => $current_node, 'sub_nodes' => $sub_nodes]);
}

/**
 * 删除节点。
 */
function delete_node_subject()
{
    $request = $GLOBALS['ev_request'];

    $id = isset($request['id']) ? intval($request['id']) : 0;

    if ($id <= 0) super_dump(false, -1, 'Illegal request![id]');

    $model = new NodeSubjects;

    if (!$subject = $model->get($id))
        super_dump(false, -1, 'Records do not exist or have been deleted.');

    if ($affected_row = $subject->delete(SOFT_DELETE)) super_dump($affected_row);
    else super_dump(false, -1, 'DB_ERR' . $model->getDBError());
}

/**
 * 设置节点排序。
 */
function set_node_subjects_sequence()
{
    $request = $GLOBALS['ev_request'];

    validate_sequence($request);

    foreach ($request as $id) {
        $model = new NodeSubjects;

        if (!$subject = $model->get($id))
            super_dump(false, -1, 'Records do not exist or have been deleted.[id => ' . $id . ']');
    }

    // 保持原子性原则，重新来一次循环。
    foreach ($request as $k => $v) {
        $subject = (new NodeSubjects)->get($v);
        $subject->sequence = $k;

        if (!$subject->update())
            super_dump(false, -1, 'DB_ERR' . $model->getDBError());
    }

    super_dump($request);
}

// **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** **** ****

/**
 * 验证节点主体数据。
 *
 * @param $request
 * @param bool $is_update
 */
function validate_subject($request, $is_update = false)
{
    if ($is_update && (!isset($request['id']) || !is_numeric($request['id']) || intval($request['id']) <= 0))
        super_dump(false, -1, 'Illegal request![id]');
    if (!isset($request['node_title']) || empty($request['node_title']))
        super_dump(false, -1, '节点标题未填写。');
    if (mb_strlen($request['node_title']) > 480)
        super_dump(false, -1, '节点标题字数超过 480 个。');
    if (isset($request['sequence']) && (!is_numeric($request['sequence']) || intval($request['sequence']) < 0))
        super_dump(false, -1, 'Illegal request![sequence]');
    if (!is_numeric($request['parent_node_id']) || intval($request['parent_node_id']) < 0)
        super_dump(false, -1, 'Illegal request![parent_node_id]');
    if (isset($request['is_cq']) && (!is_numeric($request['is_cq']) || !in_array(intval($request['is_cq']), [0, 1])))
        super_dump(false, -1, 'Illegal request![is_cq]');
    if (isset($request['is_saq']) && (!is_numeric($request['is_saq']) || !in_array(intval($request['is_saq']), [0, 1])))
        super_dump(false, -1, 'Illegal request![is_saq]');
    if ($is_update && !isset($request['subjects']))
        super_dump(false, -1, '主体（内容）未填写。');
}

/**
 * 验证排序数据。
 *
 * @param $request
 */
function validate_sequence($request)
{
    if (!is_array($request) || empty($request)) super_dump(false, -1, '排序数据不能为空。');

    $idx = 0;
    $ids = [];

    foreach ($request as $k => $v) {
        if ($k !== $idx || !is_int($v)) super_dump(false, -1, 'Illegal request![sequence #1]');
        if (in_array($v, $ids)) super_dump(false, -1, 'Illegal request![sequence #2]');
        $idx++;
        $ids[] = $v;
    }
}

