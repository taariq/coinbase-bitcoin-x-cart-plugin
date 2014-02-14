{*
$Id: ps_coinbase.tpl,v 1 2014/01/16 20:04:51 kolbasa Exp $
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>Coinbase</h1>

{$lng.txt_cc_configure_top_text}

<br /><br />

{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">
  <table cellspacing="10">
    <tr>
      <td>{$lng.lbl_coinbase_api_key}:</td>
      <td><input type="text" name="param01" size="64" value="{$module_data.param01|escape}" /></td>
    </tr>
  </table>

  <br /><br />
  <input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>
{/capture}

{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
