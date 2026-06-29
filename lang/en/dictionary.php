<?php

/*
    |--------------------------------------------------------------------------
    | Default dictionary for new language
    |--------------------------------------------------------------------------
    */

return [

    'index' => [
        'project_name' => 'eStore',
        'hero_title' => 'Welcome',
        'hero_subtitle:html' => 'Starter project on Laravel',

        'first_block_title' => 'Fast',
        'first_block_subtitle:html' => 'Laravel + Filament starter',
        'second_block_title' => 'Flexible',
        'second_block_subtitle:html' => 'Modular architecture',
    ],

    'feedback' => [
        'name' => 'Name',
        'email' => 'Email',
        'message:textarea' => 'Message',
        'saved' => 'Feedback has been saved.',
    ],

    'news' => [
        'read_more' => 'Read more',
    ],

    'shop' => [
        'currency' => '$',
        'categories_list' => 'Categories List',
        'category_info'   => 'Full category information',
        'category_products'   => 'Products list',
        'product_info'        => 'Product information',
        'view_order'                  => 'View Order',
        'payment_success_title'       => 'Payment Successful!',
        'payment_success_description' => 'Thank you for your purchase. Your order has been confirmed.',
        'payment_error_title'         => 'Payment Failed',
        'payment_error_description'   => 'Something went wrong while processing your payment. Please try again.',
    ],

    'impersonation' => [
        'banner_viewing_as' => 'Viewing as',
        'banner_return' => 'Return to my account',
    ],

    'auth' => [
        'login' => 'Sign In',
        'register' => 'Register',
        'password_recovery' => 'Password recovery',
        'name' => 'Name',
        'email' => 'Email address',
        'password' => 'Password',
        'new_password' => 'New password',
        'current_password' => 'Current Password',
        'password_confirmation' => 'Confirm password',
        'remember_me' => 'Remember me',
        'or_continue_with' => 'or continue with',
        'invalid_credentials' => 'Invalid credentials',
        'forgot_password' => 'Forgot Password?',
        'terms:html' => 'I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>',
        'user_not_found' => 'User not found',
        'password_reset_email_sent' => 'Email with password reset link has been sent.',
        'password_reset_success' => 'Password reset success.',
        'password_reset_error' => 'Error occurred while resetting password.',
    ],

    'profile' => [
        'profile_dropdown_header_text' => 'Welcome to',
        'profile_dropdown_header_subtext' => 'Access account & manage orders',
        'my_profile' => 'My profile',
        'account_settings' => 'Account Settings',
        'personal_information' => 'Personal Information',
        'user_data_updated' => 'Personal Information has been successfully updated.',
        'user_data_update_error' => 'Error occurred while updating personal data. Please try again.',
        'security' => 'Security',
        'password_updated' => 'Password has been successfully updated.',
        'password_updated_error' => 'Error occurred while updating password. Please try again.',
        'delete_account' => 'Delete Account',
        'delete_account_text' => 'Once you delete your account, there is no going back. Please be certain.',
        'delete_account_confirm_text' => 'Enter your email to confirm account deletion.',
        'delete_account_email_mismatch' => 'The email address does not match your account.',
        'account_deleted' => 'Account Deleted',
        'orders' => 'My Orders',
        'orders_all' => 'All Orders',
        'orders_search' => 'Search by order number...',
        'orders_empty' => 'You have no orders yet.',
        'order_id' => 'Order ID',
        'order_status' => 'Status',
        'order_items' => 'Items',
        'order_items_unit' => 'items',
        'order_total' => 'Total',
        'order_view_details' => 'View Details',
        'order_information' => 'Order Information',
        'order_payment_type' => 'Payment Method',
        'order_price_details' => 'Price Details',
        'order_pay' => 'Pay Order',
        'order_review' => 'Write Review',
        'order_reorder' => 'Reorder',
        'qty' => 'Qty',
        'sku' => 'SKU',
    ],

    'errors' => [
        'something_went_wrong' => 'Unexpected error. Please try again.',
        '404_title' => '404 - Page not found',
        '404_h1' => 'Page not found',
        '404_text:input12' => 'The page you are looking for doesn’t exist or has been moved.',

        '500_title' => '500 - Server error',
        '500_h1' => 'Something went wrong',
        '500_text:input12' => ' We’re working to fix the problem. Please try again later.',
    ],

    'buttons' => [
        'send' => 'Send',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'login' => 'Login',
        'logout' => 'Sign Out',
        'create_acc' => 'Create Account',
        'request_password_recovery' => 'Reset Password',
        'menu' => 'Reset Password',
        'save_changes' => 'Save Changes',
        'update_password' => 'Update Password',
        'delete_account' => 'Delete Account',
        'go_home' => 'Go home',
    ],

    'api' => [
        'validation_error' => 'Validation error.',
        'unauthenticated' => 'Unauthenticated.',
        'forbidden' => 'Forbidden.',
        'resource_not_found' => 'Resource not found.',
        'route_not_found' => 'Route not found.',
        'to_many_requests' => 'Too many requests.',
        'login_success' => 'Login successful.',
        'register_success' => 'Registration successful.',
        'logout_success' => 'Logged out.',
    ],

    'social' => [
        'section_title'             => 'Social Accounts',
        'section_subtitle'          => 'Connect your social accounts to sign in faster.',
        'connect'                   => 'Connect',
        'disconnect'                => 'Disconnect',
        'connected'                 => 'Social account connected successfully.',
        'disconnected'              => 'Social account disconnected.',
        'auth_failed'               => 'Social authentication failed. Please try again.',
        'cannot_disconnect_last'    => 'Cannot disconnect the only login method. Add a password or email first.',
        'sign_in_with'              => 'Sign in with :provider',
        'google'                    => 'Google',
        'facebook'                  => 'Facebook',
        'telegram'                  => 'Telegram',
    ],
];
