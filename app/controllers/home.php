<?php
class Home extends MY_Controller {
    public function index ()
    {
        $this->view('home/main');
    }
}
