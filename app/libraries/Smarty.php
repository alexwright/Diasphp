<?php
include_once 'Smarty/libs/Smarty.class.php';

class CI_Smarty extends Smarty {
    protected $default_extension;

    public function __construct ($params = array())
    {
        parent::__construct();

        foreach ($params AS $name => $value)
        {
            $this->{$name} = $value;
        }

        $this->setup_plugins();
    }

    public function view ($template, $view_data = array(), $return = FALSE)
    {
        if (strpos($template, '.') === FALSE)
        {
            $template .= '.' . $this->default_extension;
        }

        foreach ($view_data as $key => $val)
        {
            $this->assign($key, $val);
        }

        if ($return == FALSE)
        {
            $CI =& get_instance();
            $CI->output->set_output( $this->fetch($template) );
            return NULL;
        }
        else
        {
            return $this->fetch($template);
        }
    }

    private function setup_plugins ()
    {
        $this->registerPlugin('modifier', 'site_url', array($this, 'site_url'));
    }

    public function site_url ($url)
    {
        $CI = get_instance();
        $CI->load->helper('url');

        return site_url($url);
    }
}
