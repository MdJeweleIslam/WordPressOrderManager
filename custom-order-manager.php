<?php
/*
Plugin Name: Custom Order Manager
Plugin URI: https://wordpress.com/
Description: After a successful order, it will be triggered. Simple but flexibl.
Author: Md Jewele Islam
Author URI: https://wordpress.com/
Text Domain: custom-order-manager
Version: 0.5
*/



function PostList($product_id){
    $cc_args = array(
        'posts_per_page'   => -1,
        'post_type'        => 'vouchers',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'product_id',
                'value'   => $product_id,
                'compare' => '=',
            ),
            array(
                'key' => 'used_before',
                'value'   => 1,
                'compare' => 'NOT EXISTS',
            ),
        ),
    );
    $cc_query = new WP_Query( $cc_args );
    return $cc_query;
}



function Msgenrolled($name, $course_name, $email_name){
    $msg = "Hello $name!<br><br>

    Congratulations on your purchase.<br><br>
    
    $course_name<br><br>
    
    <b>Steps to Redeem your Voucher Code:</b><br>
    To begin studying online:<br>
    Go to our <a href='https://checkout.examfx.com/voucher'>Partner Website</a><br>
    Your Voucher Code is<br>
    Enter the tracking manager’s email in the MANAGER EMAIL field (if applicable)<br>
    Click REGISTER<br>
    Select the state in which you will earn your license and click CONTINUE
    Follow all prompts and instructions to complete registration
    Voucher Codes are for individual use only.<br><br>
    
    Your course activity will begin once the voucher code has been redeemed.<br><br>
    
    Please be sure you are ready to begin the course.<br><br>
    
    
    Thank you again for choosing Insurance Training Academy for your prelicensing needs.<br><br>
    
    
    Your Partner In Success<br><br>
    
    John";
    //php mailer variables
    $to = $email_name;
    $subject = "Congrats On Your Course Purchase!";
    // $headers = 'From: info@insurancetrainingacademy.com'."\r\n" .
    //     'Reply-To: info@insurancetrainingacademy.com' . "\r\n";
    
    $sent = wp_mail($to, $subject, $msg, 'Content-type: text/html');
}



function enroll_student( $order_id ) {
    echo "Order Executed ".$order_id;
    if ( ! $order_id )
        return;

    // Allow code execution only once 
    //! get_post_meta( $order_id, '_thankyou_action_done', true )
    if( true ) {

        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );
        
        
        $order_mail = $order->get_billing_email();
        $order_name = $order->get_billing_first_name();

        // Get the order key
        $order_key = $order->get_order_key();

        // Get the order number
        $order_key = $order->get_order_number();

        // if($order->is_paid())
        //     $paid = __('yes');
        // else
        //     $paid = __('no');

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();

            // Get the product Id
            $product_id = $product->get_id();

            // Get the product name
            $product_name = $item->get_name();
            
            $post = PostList($product_id);
            
            
            $stock_quantity = $product->get_stock_quantity();
            
            update_post_meta($product_id, '_stock', $stock_quantity-1);
            
            
            echo "The voucher code query Executed";
            if($post->post_count != 0){
                $single_post = $post->posts[0];
                $get_voucher =  $single_post->post_title;
                echo "<br>The voucher code ".$get_voucher;
                $date = date('Y-m-d H:i:s');
                update_post_meta($single_post->ID, 'used_before', true);
                update_post_meta($single_post->ID, 'order_id', $order_id);
                update_post_meta($single_post->ID, 'used_date', $date);
                Msgenrolled($order_name, $product_name, $order_mail);
                echo "Post Meta Updated";
            }
            
        }
    }
}



add_action('woocommerce_thankyou', 'enroll_student', 10, 1);
?>