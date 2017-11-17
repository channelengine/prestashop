<?php
//Please notice that MyModule here is the name of your module (the best practice – it matches the name of the module directory), MyController – is the name of your controller (the best practice – it matches the name of the controller file)
 
class ChannelEnginecallbackModuleFrontController extends ModuleFrontController
{
	public function init()
	{
		parent::init();
		$this->display_column_left = false; 
		$this->display_column_right = false; 
		$this->display_header = false;
		$this->display_footer = false;
		$this->setTemplate("module:channelengine/views/templates/front/empty.tpl");
		$this->module->handleRequest();
	}
}