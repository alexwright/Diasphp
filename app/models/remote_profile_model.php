<?php

class Remote_profile_model extends MY_Model {
    public function create ($profile_id, $seed_location, $hcard_url, $activity_stream = NULL)
    {
        $row = array(
            'profile_id'        => $profile_id,
            'seed_location'     => $seed_location,
            'hcard_url'         => $hcard_url,
            'activity_stream'   => $activity_stream,
        );
        $this->insert($row);
    }
}
