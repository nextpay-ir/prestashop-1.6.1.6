{l s='Your order on %s is complete.' sprintf=$shop_name mod='nextpay'}
		{if !isset($reference)}
			<br /><br />{l s='Your order number' mod='nextpay'}: {$id_order}
		{else}
			<br /><br />{l s='Your order number' mod='nextpay'}: {$id_order}
			<br /><br />{l s='Your order reference' mod='nextpay'}: {$reference}
		{/if}		<br /><br />{l s='An email has been sent with this information.' mod='nextpay'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as posible.' mod='nextpay'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='nextpay'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='nextpay'}</a>.
	</p><br />
