<?php
class Home extends MY_Controller {
    public function __construct ()
    {
        parent::__construct();

        if ( ! $this->user )
        {
            return redirect('login');
            exit;
        }
    }

    public function index ()
    {
        $this->stream();
    }

    public function stream ()
    {
        $this->load->model('status_message_model');
        $stream = $this->status_message_model->get_stream($this->profile->id);

        $this->load->model('profile_model');
        $this->load->model('comment_model');
        $this->load->model('photo_model');
        foreach ($stream AS $i => $post)
        {
            $stream[$i]->profile = $this->profile_model->find_by_id($post->from_id);
            $stream[$i]->comments = $this->comment_model->get_by_post_guid($post->guid);
            foreach ($stream[$i]->comments AS $o => $comment)
            {
                $stream[$i]->comments[$o]->profile = $this->profile_model->find_by_id($comment->profile_id);
            }
            $stream[$i]->photos = $this->photo_model->get_by_post_guid($post->guid);
        }

        $this->view('home/stream', array(
            'stream'        => $stream,
        ));
    }
}
