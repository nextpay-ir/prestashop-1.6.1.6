<?php
/**
* @package    Nextpay payment module
* @author     Nextpay co [Nextpay.ir]
* @copyright  2019
* @version    1.5
*/

if (!defined('_PS_VERSION_'))
	exit;

@session_start();
include_once dirname(__FILE__).'/nextpay_payment.php';

class nextpay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'nextpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.5';
		$this->author = 'Nextpay';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		$this->bootstrap = true;
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Nextpay Payment Modlue');
		$this->description = $this->l('Online Payment With Nextpay');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency has been set for this module');
		$config = Configuration::getMultiple(array('nextpay_API'));
		if (!isset($config['nextpay_API']))
            $this->warning = $this->l('You have to enter your nextpay Api key to use nextpay for your online payments.');
	}
	
	public function install() {
		if (!parent::install() || !Configuration::updateValue('nextpay_API', '') || !$this->registerHook('payment') || !$this->registerHook('paymentReturn')) return false;
		return true;
	}

	public function uninstall() {
		if (!Configuration::deleteByName('nextpay_API') || !parent::uninstall()) return false;
        return true;
	}
	
	public function hash_key() {
		$en = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$one = rand(1, 26);
		$two = rand(1, 26);
		$three = rand(1, 26);
		return $hash = $en[$one] . rand(0, 9) . rand(0, 9) . $en[$two] . $en[$tree] . rand(0, 9) . rand(10, 99);
	}

	/*public function getContent() {

		if (Tools::isSubmit('nextpay_setting')) {

			Configuration::updateValue('nextpay_API', $_POST['nx_API']);
			$this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
		}

		$this->_generateForm();
		return $this->_html;
	}*/
	
	/*private function _generateForm() {
		$this->_html .= '<div align="center"><form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
		$this->_html .= $this->l('Please Enter Your Api Key :') . '<br/><br/>';
		$this->_html .= '<input type="text" name="nx_API" value="' . Configuration::get('nextpay_API') . '" ><br/><br/>';
		$this->_html .= '<input type="submit" name="nextpay_setting"';
		$this->_html .= 'value="' . $this->l('Save it!') . '" class="button" />';
		$this->_html .= '</form><br/></div>';
	}*/
	
	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings: Nextpay Payment'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Api Key [Nextpay]'),
						'name' => 'nx_API',
						'class' => 'fixed-width-lg',
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'nextpay_setting';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array('nextpay_API' => Tools::getValue('nextpay_API', Configuration::get('nextpay_API')));
	}

	public function getContent()
	{
        $output = '';
		$errors = array();        
        if (Tools::isSubmit('nextpay_setting')) {
            if (empty($_POST['nx_API']))
				$errors[] = $this->l('Your api_key code is required.');

			if (!count($errors))
			{
				Configuration::updateValue('nextpay_API', $_POST['nx_API']);
                //$this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';                
                $output = '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
				$output = $this->displayConfirmation($this->l('Your settings have been updated.'));
			}
			else
				$output = $this->displayError(implode('<br />', $errors));			
		}
        //$this->_generateForm();
		//return $this->_html;
        return $output.$this->renderForm();
		
	}

	public function prePayment()
	{
		$Nextpay_API_Key = Configuration::get('nextpay_API');
		$purchase_currency = new Currency(Currency::getIdByIsoCode('IRR'));
		$current_currency = new Currency($this->context->cookie->id_currency);
		if($current_currency->id == $purchase_currency->id)
			$amount = number_format($this->context->cart->getOrderTotal(true, 3), 0, '', '');
		else
			$amount = number_format($this->convertPriceFull($this->context->cart->getOrderTotal(true, 3), $current_currency, $purchase_currency), 0, '', '');

		$callbackUrl = $this->context->link->getModuleLink('nextpay', 'validation');
		$orderId = $this->context->cart->id;
		
		try{
            $parameters = array (
                "api_key"=> $Nextpay_API_Key,
                "order_id"=> $orderId,
                "amount"=> $amount,
                "callback_uri"=> $callbackUrl,
                "custom"=> json_encode(array("amount"=>$amount))
            );

            $nextpay = new Nextpay_Payment($parameters);
            $res = $nextpay->token();

            $hash = $this->hash_key();
            $_SESSION['order' . $orderId] = md5($orderId . $amount . $hash);


            if(intval($res->code) == -1){
                //echo $this->success($this->l('Redirecting...'));
                //echo '<script>window.location=("https://api.nextpay.org/gateway/payment/' .  $res->trans_id . '");</script>';
                $this->context->cookie->__set("amount", (int)$amount);
                $this->context->smarty->assign(array(
                    'redirect_link' => "https://api.nextpay.org/gateway/payment/" .  $res->trans_id,
                    'trans_id' => $res->trans_id
                ));
                return true;
            } else {
                $this->context->controller->errors[] = $this->showMessages(intval($res->code));
                return false;
            }
		}
		catch(PrestaShopException $e)
		{
			$this->context->controller->errors[] = $this->l('Could not connect to bank or service.');
			return false;
		}
	}
	
	public function verify($au)
	{
        if (isset($_POST['order_id']) && isset($_POST['trans_id']) && isset($_POST['amount'])) {
            $api_key = Configuration::get('nextpay_API');
            $purchase_currency = new Currency(Currency::getIdByIsoCode('IRR'));
            $current_currency = new Currency($this->context->cookie->id_currency);
            if($current_currency->id == $purchase_currency->id)
                $amount = number_format($this->context->cart->getOrderTotal(true, 3), 0, '', '');
            else
                $amount = number_format($this->convertPriceFull($this->context->cart->getOrderTotal(true, 3), $current_currency, $purchase_currency), 0, '', '');
            if((int)$amount != (int)$this->context->cookie->__get("amount"))
            {
                $this->context->controller->errors[] = $this->l('Payment amount is incorrect.');
                return false;
            }
            try
            {
                $amount = $amount / 10;
                $parameters = array
                (
                    'api_key'	=> $api_key,
                    'order_id'	=> $orderId ,
                    'trans_id' 	=> $trans_id,
                    'amount'	=> $amount
                );

                $nextpay_payment = new Nextpay_Payment();
                $result = $nextpay_payment->verify_request($parameters);
                
                if (intval($result) == 0) {
						return true;
					} else {
						$this->context->controller->errors[] = $this->showMessages($result);
                        return false;
					}
                
            }
            catch(PrestaShopException $e){
                $this->context->controller->errors[] = $this->l('Could not connect to bank or service.');
                return false;
            }
            
        }else{
            $this->context->controller->errors[] = $this->l('Payment params is incorrect.');
            return false;
        }
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

        $order = new Order(Tools::getValue('id_order'));

        $this->context->smarty->assign(array(
            'id_order' => Tools::getValue('id_order'),
			'reference' => $order->reference,
			'trans_id' => Tools::getValue('au'),
            'ver' => $this->version
        ));

		return $this->display(__FILE__, 'confirmation.tpl');
	}
	
	public function showMessages($code)
	{
        $nextpay = new Nextpay_Payment();
        return $nextpay->code_error($code);
	}
	
	/**
	 *
	 * @return float converted amount from a currency to an other currency
	 * @param float $amount
	 * @param Currency $currency_from if null we used the default currency
	 * @param Currency $currency_to if null we used the default currency
	 */
	public static function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null)
	{
		if ($currency_from === $currency_to)
			return $amount;
		if ($currency_from === null)
			$currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		if ($currency_to === null)
			$currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT'))
			$amount *= $currency_to->conversion_rate;
		else
		{
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
			$amount = Tools::ps_round($amount / $conversion_rate, 2);
			$amount *= $currency_to->conversion_rate;
		}
		return Tools::ps_round($amount, 2);
	}
}
