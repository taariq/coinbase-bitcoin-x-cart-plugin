<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2014 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 * Coinbase payment
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2014 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    536d95e589c24076e32b35967cd3b39d91407507, v1 (xcart_4_6_1), 2014-01-20 14:19:03, ps_coinbase.php, kolbasa
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require './auth.php';        
require $xcart_dir . '/payment/Coinbase/Coinbase.php';

$api_key = $module_params['param01'];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['mode']) && $_GET['mode'] == 'callback' && isset($_GET['orderids']) && $_GET['orderids']) {
    $response = $_GET['order'];
    $orderids = $_GET['orderids'];
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $orderids . "'");

    if (!empty($response) && $response['status'] == 'completed') {
        $bill_output['code'] = 1;                        
        $bill_output['billmes'] = 'Paid';
    } else {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = $response['cancellation_reason'];
    }

    $skey = $_GET['orderids'];
    require $xcart_dir . '/payment/payment_ccmid.php';
    require $xcart_dir . '/payment/payment_ccwebset.php';
        
} elseif (isset($_GET['mode']) && $_GET['mode'] == 'complete' && isset($_GET['orderids'])) {
    $weblink = 2;
    $skey = $_GET['orderids'];
    require($xcart_dir . '/payment/payment_ccview.php');

} elseif (isset($_GET['mode']) && $_GET['mode'] == 'cancel' && isset($_GET['orderids'])) {
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $_GET['orderids'] . "'");
    $bill_output['code'] = 2;
    $bill_output['billmes'] = 'Canceled';

    $skey = $_GET['orderids'];
    require($xcart_dir . '/payment/payment_ccend.php');

} else {
    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }        
        
    $_orderids = join('-', $secure_oid);
    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref, sessid, trstat) VALUES ('" . $_orderids . "', '" . $XCARTSESSID . "', 'GO|" . implode('|', $secure_oid) . "')");
    }
        
    $coinbase = new Coinbase($api_key);
    $response = $coinbase->createButton('Order #' . $_orderids, "$cart[total_cost]", 'USD', '', array(
        'callback_url' => $current_location . '/payment/ps_coinbase.php?mode=callback&orderids=' . $_orderids,
        'success_url'  => $current_location . '/payment/ps_coinbase.php?mode=complete&orderids=' . $_orderids,
        'cancel_url'   => $current_location . '/payment/ps_coinbase.php?mode=cancel&orderids=' . $_orderids
    ));

    if ($response->button->code) {
        func_html_location('https://coinbase.com/checkouts/' . $response->button->code);
        exit;
    } else {
        x_log_add('debug_coinbase', 'error');
    }
}

exit;

?>
