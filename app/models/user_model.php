<?php
class User_model extends MY_Model {
/*
+---------------+-----------------------+------+-----+---------+----------------+
| Field         | Type                  | Null | Key | Default | Extra          |
+---------------+-----------------------+------+-----+---------+----------------+
| id            | mediumint(9) unsigned | NO   | PRI | NULL    | auto_increment |
| login_name    | varchar(16)           | NO   | UNI | NULL    |                |
| password_hash | varchar(255)          | YES  |     | NULL    |                |
| email         | varchar(64)           | YES  |     | NULL    |                |
+---------------+-----------------------+------+-----+---------+----------------+
*/
    public function create ($login, $email, $pass_hash = NULL)
    {
        return $this->insert(array(
            'login_name'        => $login,
            'email'             => $email,
            'password_hash'     => $pass_hash,
        ));
    }

    public function username_taken ($username)
    {
        $user = $this->get_by_username($username);
        return $user !== FALSE;
    }

    public function get_by_username ($username)
    {
        $query = $this->get_where(array(
            'login_name'        => $username,
        ));
        return $this->row_or_false($query);
    }

    public function get_by_id ($user_id)
    {
        $query = $this->get_where(array(
            'id'                => $user_id,
        ));
        return $this->row_or_false($query);
    }
}
