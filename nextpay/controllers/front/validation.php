<?php
class nextpayValidationModuleFrontController extends ModuleFrontController
{
    private $au = '';

	public function __construct()
	{
		parent::__construct();

		$this->context = Context::getContext();
		$this->ssl = true;
	}
	
	public function postProcess()
	{
		$this->au = Tools::getValue('au');
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		
		if(empty($this->au))
			$this->errors[] = $this->module->l('Payment Information is incorrect.');
		elseif(empty($this->context->cart->id))
			$this->errors[] = $this->module->l('Your cart is empty.');
		if(!count($this->errors))
		{
			$validate = $this->module->verify($this->au);

			if($validate === true)
				$paid = $this->module->validateOrder((int)$this->context->cart->id, _PS_OS_PAYMENT_, (float)$this->context->cart->getOrderTotal(true, 3), $this->module->displayName, $this->module->l('reference').': '.$this->au , array(),(int)$this->context->currency->id, false, $this->context->customer->secure_key);

			elseif($validate === false)
				$paid = $this->module->validateOrder((int)$this->context->cart->id, _PS_OS_ERROR_, (float)$this->context->cart->getOrderTotal(true, 3), $this->module->displayName, $this->module->l('reference').': '.$this->au , array(),(int)$this->context->currency->id, false, $this->context->customer->secure_key);

			$this->context->cookie->__unset("amount");

            if(isset($paid) && $paid)
                Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$this->context->customer->secure_key.'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder.'&au='.$this->au);
		}
		$this->assignTpl();
	}
	
	public function assignTpl()
	{
		$this->context->smarty->assign(array(
            'access' => 'denied',
            'ver' => $this->module->version,
            'trans_id' => $this->au,
			'path' => $this->module->displayName
	));
		return $this->setTemplate('validation.tpl');
	}
	
}
