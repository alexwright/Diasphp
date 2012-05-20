<?php
class Login extends MY_Controller {
    public function index ()
    {
        $this->view('login/form');
    }

    public function post_index ()
    {
        $form_data = $this->form_data($this->login_form());
        if (empty($form_data['user']) OR empty($form_data['pass']))
        {
            return $this->index();
        }

        $this->load->model('user_model');
        $user = $this->user_model->get_by_username($form_data['user']);

        $this->load->library('bcrypt');
        $ok = $this->bcrypt->verify($form_data['pass'], $user->password_hash);

        if ($ok)
        {
            $this->session->set_userdata(array(
                'user_id'   => $user->id,
            ));
            redirect(site_url('profile'));
        }
        else
        {
            $this->view('login/form', array(
                'error'     => 'Bad username or password',
            ));
            return;
        }
    }

    private function login_form ()
    {
        return array(
            array(
                'field'     => 'user',
                'type'      => 'text',
                'rules'     => 'required',
                'label'     => 'Username',
            ),
            array(
                'field'     => 'pass',
                'type'      => 'password',
                'rules'     => 'required',
                'label'     => 'Password',
                'raw'       => TRUE,
            ),
        );
    }

    private function form_data ($rules)
    {
        $form_data = array();
        foreach ($rules AS $rule)
        {
            $xss_filter = !(isset($rule['raw']) && $rule['raw']);
            $form_data[$rule['field']] = $this->input->post($rule['field'], $xss_filter);
        }
        return $form_data;
    }

    public function logout ()
    {
        $this->session->unset_userdata(array(
            'user_id'       => '',
            'profile_id'    => '',
        ));
        redirect(site_url());
    }
}
