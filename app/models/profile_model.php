<?php
class Profile_model extends MY_Model {
    public function __construct ()
    {
        parent::__construct();
    }

    public function find_by_id ($id)
    {
        return $this->row_or_false($this->get_where(array(
            'id'        => $id,
        )));
    }

    public function find_by_email ($email_address)
    {
        list($local, $domain) = explode('@', $email_address, 2);
        return $this->row_or_false($this->get_where(array(
            'local'     => $local,
            'domain'    => $domain,
        )));
    }

    public function find_by_guid ($guid)
    {
        return $this->row_or_false($this->get_where(array(
            'guid'      => $guid,
        )));
    }

    public function set_keys ($profile_id, $private_key, $public_key)
    {
        $this->update(
            array(
                'private_key'   => $private_key,
                'public_key'    => $public_key,
            ),
            array(
                'id'            => $profile_id,
            )
        );
    }

    public function save_remote_profile ($profile)
    {
        $key = $this->normalize_key($profile->public_key);

        $subject = $profile->subject;
        if (substr($subject, 0, 5) == 'acct:')
        {
            $subject = substr($subject, 5);
        }
        $subject = explode('@', $subject, 2);

        $row = array(
            'type'              => 'remote',
            'guid'              => $profile->guid,
            'local'             => $subject[0],
            'domain'            => $subject[1],
            'public_key'        => $key,
            'forename'          => $profile->hcard->forename,
            'surname'           => $profile->hcard->surname,
            'searchable'        => $profile->hcard->searchable,
        );

        
        $id = $this->insert($row);

        $this->load->model('remote_profile_model');
        $this->remote_profile_model->create(
            $id,
            $profile->seed_location,
            NULL
        );

        return $id;
    }

    public function create_local_profile ($user_id, $local, $domain, $fname, $sname)
    {
        $guid = substr(md5(uniqid()), 0, 16);
        $row = array(
            'type'              => 'local',
            'user_id'           => $user_id,
            'local'             => $local,
            'domain'            => $domain,
            'guid'              => $guid,
            'forename'          => $fname,
            'surname'           => $sname,
        );

        return $this->insert($row);
    }

    private function normalize_key ($key)
    {
        // Strip armor
        $in = preg_replace('/-{5}[A-Z\s]+-{5}|\s|\n|\r/', '', $key);

        include_once 'ASN/ASN.php';
        $s = ASN::decode(base64_decode($in));
        if (ASN::is_pkcs1($s))
        {
            // Extract modulus and exponent, and format as x509
            $m = $s[0]->value[0];
            $e = $s[0]->value[1];
            $s = ASN::pkcs_to_x509($m, $e);
            
            // Re-armor
            $out = base64_encode(ASN::encode($s));
            $out = implode("\n", str_split($out, 64));
            $out = implode("\n", array(
                '-----BEGIN PUBLIC KEY-----',
                $out,
                '-----END PUBLIC KEY-----',
            ));
            return $out;
        }
        return $key;
    }
}

