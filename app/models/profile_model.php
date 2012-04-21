<?php
class Profile_model extends MY_Model {
    public function __construct ()
    {
        parent::__construct();
    }

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
}

