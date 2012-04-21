<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webfinger extends CI_Controller {
	public function index()
	{
        $this->output->set_status_header(400);
	}

    public function finger ()
    {
        $this->output->set_header('Content-Type: application/xml; charset=utf-8');
        $this->load->helper('url');

        $uri = $this->input->get('uri', TRUE);

        $doc = new MyXMLWriter();
        $doc->openMemory();
        $doc->startDocument('1.0','UTF-8');

        $doc->startElementNS(NULL, 'XRD', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $doc->writeElement('Subject', 'acct:alex@xeen.co.uk');
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://microformats.org/profile/hcard',
                'type'  => 'text/html',
                'href'  => site_url('hcard/users/4d0b3cf12c17436c790029d1'),
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://joindiaspora.com/seed_location',
                'type'  => 'text/html',
                'href'  => 'https://joindiaspora.com/',
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://joindiaspora.com/guid',
                'type'  => 'text/html',
                'href'  => '4d0b3cf12c17436c790029d1',
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://webfinger.net/rel/profile-page',
                'type'  => 'text/html',
                'href'  => 'https://joindiaspora.com/u/alexw',
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'http://schemas.google.com/g/2010#updates-from',
                'type'  => 'application/atom+xml',
                'href'  => 'https://joindiaspora.com/public/alexw.atom',
            ));
        $doc->writeElement('Link', NULL, array(
                'rel'   => 'diaspora-public-key',
                'type'  => 'RSA',
                'href'  => 'Bhagh',
            ));

        $doc->endElement();

        echo $doc->outputMemory(TRUE);
    }

    public function host_meta ()
    {
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

