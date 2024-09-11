<?php

class modal
{
    protected $_modalTemplate;
    public function __construct()
    {
        $this->_modalTemplate = [];
    }

    public function add($template)
    {
        $this->_modalTemplate[] = $template;
        return $this;
    }

    public function get()
    {
        foreach ($this->_modalTemplate as $modalTemplate) {
            echo $modalTemplate;
        }
    }
}

$modal = new modal();
