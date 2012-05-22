<?php
/*
+------------+-----------------------+------+-----+---------+----------------+
| Field      | Type                  | Null | Key | Default | Extra          |
+------------+-----------------------+------+-----+---------+----------------+
| id         | mediumint(8) unsigned | NO   | PRI | NULL    | auto_increment |
| guid       | char(16)              | YES  | MUL | NULL    |                |
| profile_id | mediumint(8) unsigned | YES  |     | NULL    |                |
| created    | datetime              | YES  |     | NULL    |                |
| url        | varchar(128)          | YES  |     | NULL    |                |
| post_guid  | char(16)              | YES  | MUL | NULL    |                |
+------------+-----------------------+------+-----+---------+----------------+
*/
class Photo_model extends MY_Model {
    public function create ($guid, $profile_id, $created, $url, $post_guid)
    {
        $row = array(
            'guid'          => $guid,
            'profile_id'    => $profile_id,
            'created'       => $created,
            'url'           => $url,
            'post_guid'     => $post_guid,
        );
        $this->insert($row);
    }

    public function get_by_post_guid ($post_guid)
    {
        return $this->get_where(array(
            'post_guid'     => $post_guid,
        ))->result();
    }
}
