<?php
class Signup extends MY_Controller {
    function post_index ()
    {
        $this->load->library('Smarty');
        $this->load->library('form_validation');

        $rules = $this->signup_validation();
        $this->form_validation->set_rules($rules);
        if ($this->form_validation->run() == FALSE)
        {
            $errors = $this->form_validation->get_error_array();
            $this->smarty->view('signup/form', array(
                'form_errors'       => $errors,
                'form_rules'        => $rules,
            ));
        }
        else
        {
            $errors = array();

            $username = $this->input->post('user', TRUE);
            $this->load->model('user_model');
            $taken = $this->user_model->username_taken($username);
            if ($taken)
            {
                $errors['user'] = "That username is already in use.";
                $this->smarty->view('signup/form', array(
                    'form_errors'   => $errors,
                    'form_rules'    => $rules,
                ));
                return;
            }

            $this->load->library('bcrypt');
            $hash = $this->bcrypt->hash($this->input->post('passa', FALSE));

            $user_id = $this->user_model->create(
                $username,
                $this->input->post('email'),
                $hash
            );

            $this->load->library('session');
            $this->session->set_userdata(array(
                'user_id'       => $user_id,
            ));

            redirect(site_url('profile/create'));
        }
    }

    public function get_index ()
    {
        $this->load->library('Smarty');
        $this->smarty->view('signup/form', array(
            'form_errors'       => array(),
            'form_rules'        => $this->signup_validation(),
        ));
    }

    private function signup_validation ()
    {
        return array(
            array(
                'field'     => 'user',
                'label'     => 'Username',
                'rules'     => 'required',
                'type'      => 'text',
            ),
            array(
                'field'     => 'email',
                'label'     => 'Email',
                'rules'     => 'required|valid_email',
                'type'      => 'text',
            ),
            array(
                'field'     => 'passa',
                'label'     => 'Password',
                'rules'     => 'required',
                'type'      => 'password',
            ),
            array(
                'field'     => 'passb',
                'label'     => 'Password (again)',
                'rules'     => 'required|matches[passa]',
                'type'      => 'password',
            ),
        );
    }
}
