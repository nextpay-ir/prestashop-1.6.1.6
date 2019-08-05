<div class="block-center" id="">
<h2>{l s='Pay by nextpay' mod='nextpay'}</h2>

{include file="$tpl_dir./errors.tpl"}

{if isset($prepay) && $prepay}
	<br />
	<p>{l s='Connecting to gateway' mod='nextpay'}...</p>
	<p>{l s='If there is problem on redirectiong click on payment button bellow' mod='nextpay'}</p>
	<script type="text/javascript">
		setTimeout("document.forms.frmpayment.submit();",10);
	</script>
	<form name="frmpayment" action="{$redirect_link}" method="post">
		<input type="hidden" id="id" name="trans_id" value="{$trans_id}" />
		<input type="submit" class="button" value="{l s='Pay Now' mod='nextpay'}" />
	</form>
	<p></p>
{/if}
</div>
