<?php
class Contact_model extends MY_Model {
    public function get_by_profile_id ($profile_id, $filter = NULL)
    {
        $q = array(
            'profile_id'    => $profile_id,
        );
        if ($filter != NULL)
        {
            switch ($filter)
            {
                case 'to':
                    $q['to'] = 'Y';
                    break;
                case 'from':
                    $q['from'] = 'Y';
                    break;
                case 'both':
                    $q['from'] = $q['to'] = 'Y';
                    break;
            }
        }
        return $this->db
            ->from('profile')
            ->join('contact', 'profile.id=contact.contact_profile_id')
            ->where($q)
            ->get()
            ->result();
    }
}
