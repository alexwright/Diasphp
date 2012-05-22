<?php
class Comment_model extends MY_Model {
/*
+-------------+-----------------------+------+-----+---------+----------------+
| Field       | Type                  | Null | Key | Default | Extra          |
+-------------+-----------------------+------+-----+---------+----------------+
| id          | mediumint(9) unsigned | NO   | PRI | NULL    | auto_increment |
| profile_id  | mediumint(8) unsigned | NO   |     | NULL    |                |
| guid        | char(32)              | NO   | UNI | NULL    |                |
| parent_guid | char(32)              | NO   |     | NULL    |                |
| text        | text                  | YES  |     | NULL    |                |
+-------------+-----------------------+------+-----+---------+----------------+
*/
    public function create ($author_id, $comment_guid, $post_guid, $comment_text)
    {
        $row = array(
            'profile_id'    => $author_id,
            'guid'          => $comment_guid,
            'parent_guid'   => $post_guid,
            'text'          => $comment_text,
        );
        return $this->insert($row);
    }

    public function get_by_post_guid ($post_guid)
    {
        $q = array('parent_guid' => $post_guid);
        return $this->get_where($q)->result();
    }
}
