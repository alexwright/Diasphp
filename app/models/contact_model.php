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

    public function add ($profile_id, $contact_profile_id, $from = 'N', $to = 'N')
    {
        $current = $this->get_where(array(
            'profile_id'            => $profile_id,
            'contact_profile_id'    => $contact_profile_id,
        ))->row();

        if (empty($current))
        {
            $row = array(
                'profile_id'        => $profile_id,
                'contact_profile_id'=> $contact_profile_id,
                'to'    => $to,
                'from'  => $from,
            );
            $this->insert($row);
        }
        else
        {
            $this->update(
                array(
                    'to'    => $to == 'Y' ? 'Y' : $current->to,
                    'from'  => $from == 'Y' ? 'Y' : $current->from,
                ),
                array(
                    'id'    => $current->id,
                )
            );
        }
    }
}
