<?php

class Web_finger {
    public function finger ($user, $domain = NULL)
    {
        $at_pos = strpos($user, '@');
        if ($at_pos !== FALSE)
        {
            $domain = substr($user, $at_pos + 1);
            $user = substr($user, 0, $at_pos);
        }

        $meta = $this->get_host_meta($domain);
        if ($meta === FALSE)
            return FALSE;

        $lrdd_template = $this->get_lrdd_template($meta);
        if ($lrdd_template === FALSE)
            return FALSE;

        $lrdd_url = str_replace('{uri}', $user . '%40' . $domain, $lrdd_template);
        $profile = $this->load_profile($lrdd_url);

        return $profile;
    }

    public function load_profile ($url)
    {
        $xrd_dom = $this->get_xml($url);

        $xpath = new DOMXPath($xrd_dom);
        $xpath->registerNamespace('xrd', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $query = '//xrd:Link[@rel="diaspora-public-key"]/@href';
        $public_key = $xpath->evaluate($query)->item(0)->value;

        $query = '//xrd:Subject';
        $subject = $xpath->evaluate($query)->item(0)->nodeValue;

        $query = '//xrd:Link[@rel="http://joindiaspora.com/guid"]/@href';
        $guid = $xpath->evaluate($query)->item(0)->value;

        $profile = array(
            'subject'   => $subject,
            'guid'      => $guid,
            'public_key'=> base64_decode($public_key),
        );
        return (object)$profile;
    }

    private function xpath_eval_value ($xpath, $query)
    {
        return $xpath->evaluate($query)->item(0)->value;
    }

    private function get_lrdd_template ($host_meta_dom)
    {
        $xpath = new DOMXPath($host_meta_dom);
        $xpath->registerNamespace('xrd', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');
        foreach ($xpath->evaluate('//xrd:Link[@rel="lrdd"]') AS $i => $node)
        {
            $template = $node->getAttribute('template');
            if (empty($template))
                return FALSE;

            return $template;
        }
        return FALSE;
    }

    private function get_host_meta ($domain)
    {
        $url = 'https://' . $domain . '/.well-known/host-meta';
        $dom = $this->get_xml($url);
        return $dom;
    }

    private function get_xml ($url)
    {
        $res = $this->request('GET', $url);
        $dom = DOMDocument::loadXML(trim($res));

        return $dom;
    }

    private function request ($method, $url)
    {
        $ci = get_instance();
        $ci->load->library('Http_client');

        return $ci->http_client->request($method, $url);
    }
}

