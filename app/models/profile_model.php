<?php
class Profile_model extends MY_Model {
    public function find_by_id ($id)
    {
        $query = $this->db->get_where('profile', array('id'=> $id));
        if ($query->num_rows() == 0)
        {
            return FALSE;
        }
        return $query->row();
    }

    public function find_by_email ($email_address)
    {
        list($local, $domain) = explode('@', $email_address, 2);
        $query = $this->db->get_where('profile', array(
            'local'     => $local,
            'domain'    => $domain,
        ));
        if ($query->num_rows() == 0)
        {
            return FALSE;
        }
        return $query->row();
    }
}

