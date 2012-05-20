<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webfinger extends CI_Controller {
	public function index()
	{
        $this->output->set_status_header(400);
	}

    public function finger ()
    {
        $uri = $this->input->get('uri', TRUE);
        if ($uri === FALSE)
        {
            $this->output->set_status_header(400);
            return;
        }
        if (substr($uri, 0, 5) === 'acct:')
        {
            $uri = substr($uri, 5);
        }

        $this->load->model('profile_model');
        $profile = $this->profile_model->find_by_email($uri);
        if ($profile === FALSE)
        {
            $this->output->set_status_header(404);
            return;
        }

        $this->output->set_header('Content-Type: application/xml; charset=utf-8');
        $this->load->helper('url');

        $uri = $this->input->get('uri', TRUE);

        $doc = new MyXMLWriter();
        $doc->openMemory();
        $doc->startDocument('1.0','UTF-8');

        $doc->startElementNS(NULL, 'XRD', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $doc->writeElement('Subject', 'acct:' . "{$profile->local}@{$profile->domain}");
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://microformats.org/profile/hcard',
                'type'  => 'text/html',
                'href'  => site_url("hcard/users/{$profile->guid}"),
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://joindiaspora.com/seed_location',
                'type'  => 'text/html',
                'href'  => site_url(),
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://joindiaspora.com/guid',
                'type'  => 'text/html',
                'href'  => $profile->guid,
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://webfinger.net/rel/profile-page',
                'type'  => 'text/html',
                'href'  => site_url("hcard/users/{$profile->guid}"),
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://schemas.google.com/g/2010#updates-from',
                'type'  => 'application/atom+xml',
                'href'  => site_url("activity_stream/public/{$profile->guid}"),
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'diaspora-public-key',
                'type'  => 'RSA',
                'href'  => base64_encode($profile->public_key),
            ));

        $doc->endElement();

        echo $doc->outputMemory(TRUE);
    }

    public function host_meta ()
    {
        //$this->output->set_header('Content-Type: application/xml; charset=utf-8');
        $this->output->set_header('Content-Type: text/html');
        $this->load->helper('url');

        $doc = new XMLWriter();
        $doc->openMemory();
        $doc->startDocument('1.0','UTF-8');

        $doc->startElement('XRD');
        $doc->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $doc->startElement('Link');
        $doc->writeAttribute('rel', 'lrdd');
        $doc->writeAttribute('template', site_url('/webfinger/finger?uri={uri}'));
        $doc->writeAttribute('type', 'application/xrd+xml');
        $doc->endElement();

        $doc->endElement();

        echo $doc->outputMemory(TRUE);
    }
}

class MyXMLWriter extends XMLWriter {
    public function writeElement($name, $content, $attrs = NULL)
    {
        if ($attrs === NULL)
        {
            parent::writeElement($name, $content);
        }
        else
        {
            $this->startElement($name);
            if ($content)
            {
                $this->text($content);
            }
            foreach ($attrs AS $name => $value)
            {
                $this->writeAttribute($name, $value);
            }
            $this->endElement();
        }
    }
}

