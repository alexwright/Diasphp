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

    public function _remap ($method, $params)
    {
        $request_method = strtolower($this->input->server('REQUEST_METHOD'));

        if (method_exists($this, $request_method . '_' . $method))
        {
            $method = $request_method . '_' . $method;
        }

        $r = new ReflectionMethod($this, $method);
        if ($r->isPublic() && !$r->isStatic())
        {
            return call_user_func_array(array($this, $method), $params);
        }
        
        header('Content-Type: text/plain');
        var_dump(array(
            $method,
            $params,
        ));
    }
}
