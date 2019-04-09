<?php

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

class TT_Upgrader_Skin_Ajax extends WP_Upgrader_Skin
{
    public $in_loop = false;
    public $error = false;

    private $errors_to_show = 1;
    private $errors_shown = 0;


    public function error($errors)
    {
        die($errors->get_error_message() . ' ' . $errors->get_error_data());
    }

    /**
     *
     * @param string $string
     */
    public function feedback($string)
    {
    }

    public function header()
    {
    }

    public function footer()
    {
    }

    public function bulk_header()
    {
    }

    public function bulk_footer()
    {
    }

    public function before($title = '')
    {
    }

    public function after($title = '')
    {
    }
}
