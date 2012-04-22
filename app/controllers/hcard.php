<?php
class Hcard extends CI_Controller {
    public function users ($guid = NULL)
    {
        if ($guid === NULL)
        {
            $this->output->set_status_header(404);
            return;
        }

        $this->load->model('profile_model');
        $profile = $this->profile_model->find_by_guid($guid);
        if ($profile === FALSE)
        {
            $this->output->set_status_header(404);
            return;
        }
        
        $view_data = array(
            'profile'       => $profile,
        );
        $this->load->helper('url');
        $this->load->view('hcard/hcard', $view_data);
    }
}

