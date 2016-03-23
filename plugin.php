<?php

class RankGold {

    public function init_page()
    {
        if ($GLOBALS['qa_request'])
            return false;

        require_once QA_INCLUDE_DIR.'db/users.php';
        require_once QA_INCLUDE_DIR.'db/points.php';
        require_once QA_INCLUDE_DIR.'app/users-edit.php';
        require_once QA_INCLUDE_DIR.'app/posts.php';
        require_once QA_INCLUDE_DIR.'db/selects.php';

        $this->do_post_job();
    }

    function get_json_data($string)
    {
        if (!$string || !is_string($string)) return false;

        $array = json_decode($string, true);

        $is_json = is_array($array) && !empty($array);

        if ( $is_json && function_exists('json_last_error'))
            $is_json = (json_last_error() == 0);

        if ($is_json)
            return $array;

        return false;
    }

    function do_post_job()
    {
        $string = file_get_contents('php://input');

        if (!$data = $this->get_json_data($string))
            return false;

        $username = isset($data['username'])?$data['username']:false;
        $password = isset($data['password'])?$data['password']:false;

        if (!$username || !$password)
            $this->output_json('Credentials not provided!', 403);

        if (!$userid = $this->auth_user($username, $password))
            $this->output_json('Credentials are not matched!', 403);

        if (isset($data['new_post'])) {

            if (!isset($data['post_title']) || !$data['post_title'])
                $this->output_json('Title not provided!', 403);

            if (!isset($data['post_content']) || !$data['post_content'])
                $this->output_json('Content not provided!', 403);

            $post_tags = isset($data['post_tags']) ? $data['post_tags'] : null;

            if ($id = $this->new_post($data['post_title'], $data['post_content'], $userid, $post_tags))
                $this->output_json($id);

            $this->output_json('Fail to create a post!', 500);

        } elseif (isset($data['get_post'])) {

            if (!isset($data['id']) || !$data['id'])
                $this->output_json('id not provided!', 403);

            if ($info = $this->get_post($data['id']))
                $this->output_json($info);

            $this->output_json('Fail to retrieve the id '.$data['id'].'!', 403);
        }

        $this->output_json('Fail to retrieve the id '.$data['id'].'!', 404);

    }

    function output_json($message, $code = 0) {

        if (!$code)
            exit(json_encode($message));

        $array = compact('code', 'message');

        exit(json_encode($array));

    }

    function get_post($id)
    {
        $post = qa_db_single_select(qa_db_full_post_selectspec(null, $id));

        if (!is_array($post)) return false;

        $array = array();
        $array['post_id'] = $post['postid'];
        $array['post_date'] = date('Y-m-d H:i:s', $post['created']);
        $array['post_title'] = $post['title'];
        $array['post_link'] = $this->get_site_url(qa_q_request($post['postid'], $post['title']));
        return $array;
    }

    function get_site_url($path='')
    {
        $site = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://" . $_SERVER["HTTP_HOST"];
        return $site . '/'.ltrim($path, '/');
    }

    function new_post($post_title, $post_content, $userid, $post_tags = null)
    {
        return qa_post_create('Q', null, $post_title, $post_content, 'html', null, $post_tags, $userid, false);
    }

    function auth_user($username, $password)
    {
        if (!$userid = $this->get_userid($username))
            return false;

        $userinfo = qa_db_select_with_pending(qa_db_user_account_selectspec($userid, true));

        if (strtolower(qa_db_calc_passcheck($password, $userinfo['passsalt'])) !== strtolower($userinfo['passcheck']))
            return false;

        qa_set_logged_in_user($userid, $userinfo['handle'], false);

        return $userid;

    }


    function get_userid($username)
    {
        $user = qa_db_user_find_by_handle($username);

        if (!$user || !is_array($user)) return false;

        return $user[0];
    }

}
