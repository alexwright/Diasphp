<?php

class Receive extends CI_Controller {
    // !curl -id @request_xml.dump http://dev.diasphp.com/receive/users/0123456789ABCDEF
    public function users ($guid)
    {
        $this->load->model('profile_model');
        $profile = $this->profile_model->find_by_guid($guid);
        if ($profile === FALSE)
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
        $decrypted_header = $this->decrypt_header($dom, $profile);
        $decrypted_data = $this->decrypt_data($env, $decrypted_header);

        var_dump($env, $decrypted_header, $decrypted_data);
        $author_id = $decrypted_header['author_id'];
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

