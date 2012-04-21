<?php
class Profile_model extends MY_Model {
    private $table_name = 'profile';

    public function find_by_id ($id)
    {
        return $this->row_or_false($this->get_where(array(
            'id'        => $id,
        )));
    }

    public function find_by_email ($email_address)
    {
        list($local, $domain) = explode('@', $email_address, 2);
        return $this->row_or_false($this->get_where(array(
            'local'     => $local,
            'domain'    => $domain,
        )));
    }

    private function get_where ($where)
    {
        return $this->db->get_where(
            $this->table_name,
            $where);
    }

    private function row_or_false ($query_ref)
    {
        if ($query_ref->num_rows() == 0)
        {
            return FALSE;
        }
        return $query_ref->row();
    }
}

