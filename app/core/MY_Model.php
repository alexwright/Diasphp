<?php
class MY_Model extends CI_Model {
    protected $table_name;

    public function __construct ()
    {
        $this->guess_settings();
    }

    private function guess_settings ()
    {
        $model_name = strtolower(get_class($this));
        $table_name = preg_replace('/_model$/', '', $model_name);
        $this->table_name = $table_name;
    }

    protected function update ($changes, $where)
    {
        $this->db->update($this->table_name, $changes, $where);
    }

    protected function insert ($row)
    {
        $this->db->insert($this->table_name, $row);
        return $this->db->insert_id();
    }

    protected function get_where ($where)
    {
        return $this->db->get_where(
            $this->table_name,
            $where);
    }

    protected function row_or_false ($query_ref)
    {
        if ($query_ref->num_rows() == 0)
        {
            return FALSE;
        }
        return $query_ref->row();
    }
}
