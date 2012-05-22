<?php
class Home extends MY_Controller {
    public function index ()
    {
        if ( ! $this->user )
        {
            return redirect('login');
        }
        
        $this->stream();
    }

    private function stream ()
    {
        $this->load->model('status_message_model');
        $stream = $this->status_message_model->get_stream($this->profile->id);

        $this->load->model('profile_model');
        $this->load->model('comment_model');
        foreach ($stream AS $i => $post)
        {
            $stream[$i]->profile = $this->profile_model->find_by_id($post->from_id);
            $stream[$i]->comments = $this->comment_model->get_by_post_guid($post->guid);
        }

        $this->view('home/stream', array(
            'stream'        => $stream,
        ));
    }
}
