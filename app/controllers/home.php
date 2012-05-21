<?php
class Home extends MY_Controller {
    public function index ()
    {
        if ( ! $this->user )
        {
            return redirect('login');
        }
        $this->view('home/main');
    }
}
