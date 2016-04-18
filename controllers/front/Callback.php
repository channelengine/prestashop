<?php
//Please notice that MyModule here is the name of your module (the best practice – it matches the name of the module directory), MyController – is the name of your controller (the best practice – it matches the name of the controller file)
 
class ChannelEnginecallbackModuleFrontController extends ModuleFrontController
{
	public function init() {
		$this->module->handleRequest();
		die();
	}

}