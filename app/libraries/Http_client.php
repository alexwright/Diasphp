<?php
class Http_client {
    public function get ($url)
    {
        return $this->request('GET', $url);
    }

    public function request ($method, $url)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);

        $this->cacert_setup($c);

        $res = curl_exec($c);
        if ($res === FALSE)
        {
            return FALSE;
            $this->curl_errors(curl_errno($c));
        }

        return $res;
    }

    private function cacert_setup ($curl_handel)
    {
        $ci = get_instance();
        $ci->load->config('curl', TRUE);
        $ca_cert_file = $ci->config->item('cacert_file', 'curl');

        if ($ca_cert_file !== FALSE)
        {
            curl_setopt($curl_handel, CURLOPT_CAINFO, $ca_cert_file);
        }
    }

    private function curl_errors ($err_no)
    {
        throw new Exception('Curl Error: ' . $err_no);
    }
}
