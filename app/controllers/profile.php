<?php
class Profile extends MY_Controller {
    public function get_create ()
    {
        $this->load->library('session');
        /*
        var_dump(array(
            $this->session->all_userdata(),
            $this->user,
        ));
        */

        $this->view('profile/create');
        return;
        header('Content-Type: text/plain');
        var_dump($this->new_key_pair());
    }

    public function post_create ()
    {
        $this->load->model('profile_model');
        $profile_id = $this->profile_model->create_local_profile(
            $this->user->id,
            $this->input->post('local', TRUE),
            'dev.diasphp.com',
            $this->input->post('fname', TRUE),
            $this->input->post('sname', TRUE)
        );

        list($private, $public) = $this->new_key_pair();
        $this->profile_model->set_keys($profile_id, $private, $public);

        $this->session->set_userdata(array(
            'profile_id'    => $profile_id,
        ));
        
        echo "Done";
    }

    private function new_key_pair ($key_size = 4096)
    {
        $res = openssl_pkey_new(array(
            'digest_alg'        => 'sha1',
            'private_key_bits'  => $key_size,
            'encrypt_key'       => FALSE
        ));
        $pri_key = '';
        openssl_pkey_export($res, $pri_key);
        
        $key_info = openssl_pkey_get_details($res);
        $pub_key = $key_info["key"];

        return array($pri_key, $pub_key);
    }
}
