<?php
class nextpayPaymentModuleFrontController extends ModuleFrontController
{
	
	public function __construct()
	{
		parent::__construct();

		$this->context = Context::getContext();
		$this->ssl = true;
	}
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		//$this->display_column_left = false;
		parent::initContent();
		$this->assignTpl();
	}
	
	public function assignTpl()
	{
		$return = $this->module->prePayment();
		if($return === true)
			$this->context->smarty->assign('prepay', 'true');
		return $this->setTemplate('payment.tpl');
	}
}
