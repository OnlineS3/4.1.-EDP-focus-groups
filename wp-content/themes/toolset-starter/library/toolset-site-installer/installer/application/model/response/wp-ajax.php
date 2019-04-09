<?php

class TT_Response_Wp_Ajax implements TT_Response_Interface
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var 0|1
     */
    private $success = 0;

    /**
     * @var 0|1
     */
    private $failed  = 0;

    /**
     * @param array $args
     */
    public function response($args = array())
    {
        if (! empty($this->message) && ! array_key_exists('message', $args)) {
            $args['message'] = $this->message;
        }

        if ($this->success === 1) {
            wp_send_json_success($args);
        }

        if ($this->failed === 1) {
            wp_send_json_error($args);
        }

        if( ! array_key_exists( 'data', $args ) ) {
            $args['data'] = $args;
        }

        wp_send_json($args);
    }

    public function success()
    {
        $this->success = 1;
        $this->failed = 0;

        return $this;
    }

    public function failed()
    {
        $this->failed = 1;
        $this->success = 0;

        return $this;
    }

    public function setMessage($msg)
    {
        $this->message = $msg;

        return $this;
    }


}