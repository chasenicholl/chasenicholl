<?php
if (!isset($title)) {
    $title = '';
}
$errors      = false;
$error_color = '';
$error_msg   = '';
$_errors     = Comet::getErrors('backend');

foreach ($_errors as $_error) {

    $_error = (array)$_error;

    if (isset($_error['error']) && !empty($_error['error'])) {
        
        $errors      = true;    
        $error_color = '#FF0000';
        $details     = '';
        
        if (isset($_error['errors'])) {
            foreach ($_error['errors'] as $detail) {
                $details .= $detail . ', ';
            }
            $details = substr($details, 0, -2);
        }
        $error_msg   = $_error['error'] . ' - ' . $details;
    }

}
?>
<div class="header_wrapper">

    <header class="obscurity">
    
        <div class="title">
            <strong>Comet eCommerce</strong>
            <span class="page_title"><?= $title ?></span>
        </div>
        
        <?php if (!empty($user_payload)): ?>
        
        <div class="bttn" id="avatar_bttn">
            <div class="avatar" id="avatar"></div>
            <div class="account_block">
                <strong>username</strong><br />
                <span class="email"><?= $user_payload['username']; ?></span>
                <br /><br/ >
                <strong>email</strong><br />
                <span class="email"><?= $user_payload['email']; ?></span>
                <div class="obscurity_logout_bttn" id="obscurity_logout_bttn">LOGOUT</div>
            </div>
        </div>
                
        <div class="create_new_order_bttn">+</div>
        
        <div class="obscurity_navigation">
            
            <div class="obscurity_navigation_inner">
                
                <div class="nav_item_spacer" style="float:left;"></div>      
                  
                <a href="/dashboard/home/">
                    <div class="nav_item">
                        <span>Dashboard</span>
                    </div>
                </a>
                
                <div class="nav_item">
                    <span>Sales</span>
                    <div class="nav_item_menu">
                        <a href="#"><div class="nav_sub_item">Leads</div></a>
                        <a href="/dashboard/sales/orders/"><div class="nav_sub_item">Orders</div></a>
                        <a href="#"><div class="nav_sub_item">Invoices</div></a>
                        <a href="#"><div class="nav_sub_item">Shipments</div></a>
                        <a href="#"><div class="nav_sub_item">Transactions</div></a>
                    </div>
                </div>
                
                <div class="nav_item">
                    <span>Catalog</span>
                    <div class="nav_item_menu">
                        <a href="/dashboard/catalog/products/?page=1"><div class="nav_sub_item">Manage Products</div></a>
                        <a href="#"><div class="nav_sub_item">Manage Categories</div></a>
                        <a href="#"><div class="nav_sub_item">Inventory</div></a>
                    </div>
                </div>
                
                <div class="nav_item">
                    <span>Customers</span>
                    <div class="nav_item_menu">
                        <a href="/dashboard/customers/"><div class="nav_sub_item">Manage Customers</div></a>
                        <a href="#"><div class="nav_sub_item">Manage Customer Groups</div></a>
                        <a href="#"><div class="nav_sub_item">Manage Accounts</div></a>
                    </div>
                </div>
                
                <div class="nav_item">
                    <span>Promotions</span>
                    <div class="nav_item_menu">
                        <a href="#"><div class="nav_sub_item">Manage Coupons</div></a>
                        <a href="#"><div class="nav_sub_item">Shopping Cart Rules</div></a>
                    </div>
                </div>
                
                <a href="/dashboard/reports/">
                    <div class="nav_item">
                        <span>Reports</span>
                    </div>
                </a>
                
                <a href="/dashboard/feed/">
                    <div class="nav_item">
                        <span>Feed</span>
                    </div>
                </a>
                
                <div class="nav_item_spacer" style="float:right;"></div>
                
                <a href="/dashboard/store/settings/">
                    <div class="nav_item" style="float:right;">
                        <span>Settings</span>
                    </div>
                </a>
                
            </div>
    
        </div>
        <?php endif ?>
    
    </header>

</div>
<div class="notice_container" id="notice_container" style="background-color:<?= $error_color ?>;<?php if($errors): ?>margin-top:92px;<?php else: ?>margin-top:36px;<?php endif ?>"> 
    <div style="float:left;"><?= $error_msg ?></div>
    <div style="float:right;cursor:pointer;" id="close_notice_bttn">[close]</div>
</div>