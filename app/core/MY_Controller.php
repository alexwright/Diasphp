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

            $this->load->model('profile_model');
            $this->profile = $this->profile_model->find_by_user_id($user_id);
        }
    }

    public function _remap ($method, $params)
    {
        if (isset($this->requires))
        {
            if ($this->requires == 'profile')
            {
                if ( ! $this->user OR ! $this->profile )
                {
                    return redirect('login');
                }
            }
        }

        $request_method = strtolower($this->input->server('REQUEST_METHOD'));

        if (method_exists($this, $request_method . '_' . $method))
        {
            $method = $request_method . '_' . $method;
        }

        if ( ! method_exists($this, $method) )
        {
            show_404();
            return FALSE;
        }

        $r = new ReflectionMethod($this, $method);
        if ($r->isPublic() && !$r->isStatic())
        {
            return call_user_func_array(array($this, $method), $params);
        }
    }

    protected function view ($template_name, $view_data = array())
    {
        $this->load->library('smarty');
        $this->smarty->view($template_name, $view_data);
    }
}
