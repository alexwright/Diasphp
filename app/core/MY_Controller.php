<?php
class MY_Controller extends CI_Controller {
    protected $user;
    protected $profile;

    public function __construct ()
    {
        parent::__construct();

        $user_id = $this->session->userdata('user_id');
        if ($user_id)
        {
            $this->load->model('user_model');
            $this->user = $this->user_model->get_by_id($user_id);
        }
    }
}
