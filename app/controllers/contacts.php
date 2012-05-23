<?php
class Contacts extends MY_Controller {
    protected $requires = 'profile';
    public function index ()
    {
        $this->load->model('contact_model');
        $contacts = $this->contact_model->get_by_profile_id($this->profile->id);

        $this->view('contacts/main', array(
            'contacts'      => $contacts,
        ));
    }
}
