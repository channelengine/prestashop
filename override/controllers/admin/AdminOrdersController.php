<?php

class AdminOrdersController extends AdminOrdersControllerCore
{

    public function __construct()
    {

        parent::__construct();

        $this->fields_list = array_merge($this->fields_list, array(
            'channelengine_channel_order_no' => array(
                'title' => $this->l('CE Channel Order No'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'channelengine_channel_tenant' => array(
                'title' => $this->l('CE Account'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
        ));
    }
}
