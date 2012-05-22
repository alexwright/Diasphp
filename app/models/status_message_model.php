<?php
class Status_message_model extends MY_Model {
    public function create ($guid, $from_id, $to_id, $public, $created, $message)
    {
        $row = array(
            'guid'          => $guid,
            'from_id'       => $from_id,
            'to_id'         => $to_id,
            'public'        => $public,
            'created'       => $created,
            'message'       => $message,
        );
        return $this->insert($row);
    }

    public function delete_by_guid ($guid)
    {
        return $this->db->delete($this->table_name, array('guid'=> $guid));
    }

    public function get_stream ($profile_id)
    {
        return $this->get_where(array('to_id'=> $profile_id))->result();
    }
}
