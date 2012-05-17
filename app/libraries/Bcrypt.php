<?php

class Bcrypt {
    private $rounds = 13;
    public function __construct ($params = array())
    {
        if (CRYPT_BLOWFISH != 1)
        {
            throw new Exception("bcrypt not supported");
        }

        foreach ($params AS $name => $value)
        {
            $this->{$name} = $value;
        }
    }

    public function hash ($password)
    {
        $hash = crypt($password, $this->getSalt());

        if(strlen($hash) > 13)
            return $hash;

        return FALSE;
    }

    public function verify ($password, $existingHash)
    {
        $hash = crypt($password, $existingHash);

        return $hash === $existingHash;
    }

    private function getSalt ()
    {
        $salt = sprintf('$2a$%02d$', $this->rounds);

        $bytes = $this->getRandomBytes(16);

        $salt .= $this->encodeBytes($bytes);

        return $salt;
    }

    private function getRandomBytes($count) {
        if ( ! function_exists('openssl_random_pseudo_bytes'))
            throw new Exception('OpenSSL not avaliable');
        $bytes = openssl_random_pseudo_bytes($count);

        if (strlen($bytes) < $count)
        {
            throw new Exception('Failed to aquire random bytes');
        }

        return $bytes;
    }

    private function encodeBytes($input)
    {
        // The following is code from the PHP Password Hashing Framework
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '';
        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }
}

