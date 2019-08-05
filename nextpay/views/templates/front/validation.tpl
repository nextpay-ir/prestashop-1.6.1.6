<div class="block-center" id="">
    <h2>{l s='Pay by nextpay' mod='nextpay'}</h2>

    {include file="$tpl_dir./errors.tpl"}

    <p>{l s='Your order on' mod='nextpay'} <span class="bold">{$shop_name}</span> {l s='is not complete.' mod='nextpay'}
        <br /><br /><span class="bold">{l s='There is some errors in your payment.' mod='nextpay'}</span>
        <br /><br />{l s='For any questions or for further information, please contact our' mod='nextpay'} <a href="{$link->getPageLink('contact-form', true)}">{l s='customer support' mod='nextpay'}</a>.
    </p>

    {if !empty($trans_id)}
        <p class="required">{l s='Payment Details' mod='nextpay'}:</p>
        <p>
            {l s='Payment ID' mod='nextpay'}: {$trans_id}
        </p><br />
    {/if}

    <p style="float:left; font-size:9px;color:#c4c4c4">nextpay ver <a href="https://nextpay.ir/" style="color:#c4c4c4">{$ver}</a></p>
</div>
