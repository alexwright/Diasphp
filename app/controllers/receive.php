<?php

class Receive extends CI_Controller {
    public function users ($guid)
    {
        $this->load->model('profile_model');
        $to = $this->profile_model->find_by_guid($guid);
        if ($to === FALSE)
        {
            $this->output->set_status_header(404);
            return;
        }

        $xml = $this->input->post('xml', FALSE);
        if ($xml === FALSE)
        {
            $this->output->set_status_header(400);
            return;
        }

        $xml = trim($xml);
        $dom = DOMDocument::loadXML($xml);
        if ($dom === FALSE)
        {
            $this->output->set_status_header(400);
            return;
        }

        $env = $this->get_env($dom);
        $decrypted_header = $this->decrypt_header($dom, $to);
        $decrypted_data = $this->decrypt_data($env, $decrypted_header);

        $author_id = $decrypted_header['author_id'];
        $author_profile = $this->finger($author_id);
        
        $legit = $this->verify($env['data'], $env['sig'], $author_profile->public_key);
        if ( ! $legit )
        {
            $this->output->set_status_header(404);
            return;
        }

        $dom = $this->fix_xml($decrypted_data);
        if ($this->handle($dom, $author_profile, $to))
        {
            $this->output->set_status_header(200);
            echo "ok";
        }
        else
        {
            $this->output->set_status_header(400);
            echo "problem with message payload.";
            return;
        }
    }

    private function handle ($dom_payload, $signed_by, $sent_to)
    {
        if (
            $dom_payload->firstChild->nodeName != 'XML'
            ||
            $dom_payload->firstChild->firstChild->nodeName != 'post'
        )
        {
            return FALSE;
        }

        $post = $dom_payload->firstChild->firstChild;
        foreach ($post->childNodes AS $c)
        {
            switch ($c->nodeName)
            {
                case 'status_message':
                    $this->handle_status_message($c, $signed_by, $sent_to);
                    break;

                case 'signed_retraction':
                    $this->handle_signed_retraction($c, $signed_by, $sent_to);
                    break;

                case 'comment':
                    $this->handle_comment($c, $signed_by, $sent_to);
                    break;

                case 'photo':
                    $this->handle_photo($c, $signed_by, $sent_to);
                    break;

                case 'request':
                    $this->handle_request($c, $signed_by, $sent_to);
                    break;

                default:
                    echo "message: ", $c->nodeName, "\n";
                    echo $dom_payload->saveXML($c), "\n\n";
                    // unknow message type
                    break;
            }
        }
        return TRUE;
    }

    private function handle_status_message ($c, $signed_by, $sent_to)
    {
        $message = $this->dom_to_assoc($c);

        if ($message['diaspora_handle'] == $signed_by->local . '@' . $signed_by->domain)
        {
            $m = (object)$message;
            $this->load->model('status_message_model');
            $i = $this->status_message_model->create(
                    $m->guid, $signed_by->id, $sent_to->id,
                    $m->public == 'true', $m->created_at, $m->raw_message);
        }
        else
        {
            // Don't know how to handle this.
        }
    }

    private function handle_signed_retraction ($c, $signed_by, $sent_to)
    {
        $message = $this->dom_to_assoc($c);

        $base_str = $message['target_guid'] . ';' . $message['target_type'];
        $my_hash = base64_encode(hash('sha256', $base_str, TRUE));
        $raw_sig = base64_decode($message['target_author_signature']);

        $public_key = openssl_get_publickey($signed_by->public_key);
        $decrypted_sig = '';
        $r = openssl_public_decrypt($raw_sig, $decrypted_sig, $public_key);
        if ($r !== TRUE)
        {
            throw new Exception('public_decrypt() failed');
            return FALSE;
        }

        $their_hash = base64_encode(substr($decrypted_sig, - 32));
        
        if ($my_hash != $their_hash)
        {
            return FALSE;
        }

        switch ($message['target_type'])
        {
            case 'StatusMessage':
                $this->load->model('status_message_model');
                $this->status_message_model->delete_by_guid($message['target_guid']);
                break;
            default:
                echo "Unknown retraction type\n";
        }
    }

    private function handle_comment ($c, $signed_by, $sent_to)
    {
        $message = $this->dom_to_assoc($c);

        $base_str = $message['guid'] . ';' . $message['parent_guid'] . ';' . 
                    $message['text'] . ';' . $message['diaspora_handle'];
        $my_hash = base64_encode(hash('sha256', $base_str, TRUE));

        // Check the author
        $raw_sig = base64_decode($message['author_signature']);
        $author_profile = $this->finger($message['diaspora_handle']);
        $public_key = openssl_get_publickey($author_profile->public_key);
        $decrypted_sig = '';
        $r = openssl_public_decrypt($raw_sig, $decrypted_sig, $public_key);

        if ($r !== TRUE)
        {
            throw new Exception('public_decrypt() failed');
            return FALSE;
        }

        $their_hash = base64_encode(substr($decrypted_sig, - 32));
        if ($their_hash != $my_hash)
        {
            return FALSE;
        }

        // Check post owner
        $raw_sig = base64_decode($message['parent_author_signature']);
        $public_key = openssl_get_publickey($signed_by->public_key);
        $decrypted_sig = '';
        $r = openssl_public_decrypt($raw_sig, $decrypted_sig, $public_key);

        if ($r !== TRUE)
        {
            throw new Exception('public_decrypt() failed');
            return FALSE;
        }

        $their_hash = base64_encode(substr($decrypted_sig, - 32));
        if ($their_hash != $my_hash)
        {
            return FALSE;
        }

        // Store the comment at this point.
        $this->load->model('comment_model');
        $this->comment_model->create($author_profile->id, $message['guid'], $message['parent_guid'], $message['text']);
    }

    private function handle_photo ($c, $signed_by, $sent_to)
    {
        $message = $this->dom_to_assoc($c);

        $this->load->model('status_message_model');
        $post = $this->status_message_model->get_by_guid($message['status_message_guid']);
        if ( ! $post )
        {
            // Not a post we know about
            return FALSE;
        }

        if ($post->from_id != $signed_by->id)
        {
            // Not the author of the original post
            return FALSE;
        }

        // All seems legit.
        $this->load->model('photo_model');
        $this->photo_model->create(
            $message['guid'],
            $signed_by->id,
            $message['created_at'],
            $message['remote_photo_path'] . $message['remote_photo_name'],
            $post->guid);
    }

    private function handle_request ($c, $signed_by, $sent_to)
    {
        $message = $this->dom_to_assoc($c);

        $this->load->model('contact_model');
        $this->contact_model->add($sent_to->id, $signed_by->id, 'Y');
    }

    private function fix_xml ($xml)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = FALSE;

        $xml = str_replace(array(chr(12), chr(3), chr(1), chr(5), chr(8), chr(0x10)), '', $xml);
        $xml = trim($xml);
        $dom->loadXML($xml);
        return $dom;
    }

    private function verify ($data, $sig, $public_pem = NULL, $encoding = 'base64url', $alg = 'RSA-SHA256', $type = 'application/xml')
    {
        $this->load->helper('base64_urlsafe');
        $data = str_replace(array(" ","\t","\r","\n"), array("","","",""), $data);

        $base_str = $data  . '.' . 
                    base64_url_encode($type) . '.' .
                    base64_url_encode($encoding) . '.' . 
                    base64_url_encode($alg);
        $my_hash = base64_encode(hash('sha256', $base_str, TRUE));

        $public_key = openssl_get_publickey($public_pem);

        $raw_sig = $this->base64_url_decode($sig);
        
        $decrypted_sig = '';
        $r = openssl_public_decrypt($raw_sig, $decrypted_sig, $public_key);
        if ($r !== TRUE)
        {
            throw new Exception('public_decrypt() failed');
            return FALSE;
        }
        
        $their_hash = base64_encode(substr($decrypted_sig, - 32));
        return $their_hash === $my_hash;
    }

    private function get_env ($dom)
    {
        $env_node = $dom->getElementsByTagName('env')->item(0);
        $child_nodes = $env_node->childNodes;
        return $this->dom_to_assoc($child_nodes);
    }

    private function decrypt_data ($env, $decrypted_header)
    {
        $encrypted_data = base64_decode($this->base64_url_decode($env['data']));
        $decrypted_data = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            base64_decode($decrypted_header['aes_key']),
            $encrypted_data,
            MCRYPT_MODE_CBC,
            base64_decode($decrypted_header['iv'])
        );
        return $decrypted_data;
    }

    private function decrypt_header ($dom, $profile)
    {
        // Un-wrap the XML->Base64->JSON into a PHP stdClass
        $nodes = $dom->getElementsByTagName('encrypted_header');
        $encrypted_header = 
          trim($nodes->item(0)->firstChild->nodeValue);
        $encrypted_header = json_decode(base64_decode($encrypted_header));

        // Use PKI to decrypt the AES key and IV
        $private_key = openssl_get_privatekey($profile->private_key);
        if ($private_key === FALSE)
            throw new Exception('Unable to load private key');
        $decrypted_aes_key = NULL;
        openssl_private_decrypt(
            base64_decode($encrypted_header->aes_key),
            $decrypted_aes_key,
            $private_key);
        if ($decrypted_aes_key === NULL)
            throw new Exception('PKI decryption failed');
        $aes_info = json_decode($decrypted_aes_key);

        // Decrypt the AES-encrypted payload
        $decrypted_xml_payload = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            base64_decode($aes_info->key),
            base64_decode($encrypted_header->ciphertext),
            MCRYPT_MODE_CBC,
            base64_decode($aes_info->iv)
        );
        $decrypted_xml_payload = trim($decrypted_xml_payload);
        $decrypted_header = DOMDocument::loadXML($decrypted_xml_payload);
        if ($decrypted_header === FALSE)
            throw new Exception('AES Decryption, or XML parsing failed.');

        return $this->dom_to_assoc($decrypted_header);
    }

    private function finger ($author_id)
    {
        $this->load->model('profile_model');
        $profile = $this->profile_model->find_by_email($author_id);
        if ($profile)
        {
            return $profile;
        }

        $this->load->library('web_finger');
        $profile = $this->web_finger->finger($author_id);

        $this->profile_model->save_remote_profile($profile);
        return $profile;
    }

    private function dom_to_assoc ($dom)
    {
        $a = array();
        if ($dom instanceof DOMDocument)
        {
            $node_list = $dom->firstChild->childNodes;
        }
        else if ($dom instanceof DOMNodeList)
        {
            $node_list = $dom;
        }
        else if ($dom instanceof DOMElement)
        {
            $node_list = $dom->childNodes;
        }
        else
        {
            echo "unknown type\n";
            var_dump($dom);
            exit;
        }

        foreach ($node_list AS $node)
        {
            if ($node->nodeType !== XML_ELEMENT_NODE)
                continue;

            $a[$node->localName] = $node->nodeValue;
        }
        return $a;
    }

    private function base64_url_decode ($str)
    {
        $str = str_replace('-', '+', $str);
        $str = str_replace('_', '/', $str);
        return base64_decode($str);
    }
}

