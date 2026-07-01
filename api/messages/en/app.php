<?php 

/**
 * English language translations for the app
 */
return [
    // AuthController actionLogin translations
    'Missing Phone number or password' => 'Missing Phone number or password',
    'Vendor not found' => 'Vendor not found',
    'Account pending activation. Click forgot password' => 'Account pending activation. Click forgot password',
    'Vendor is inactive' => 'Vendor is inactive',
    'Invalid password' => 'Invalid password',
    'System error. Please try again later or contact support' => 'System error. Please try again later or contact support',
    'Vendor logged in successfully' => 'Vendor logged in successfully',
    'No location description' => 'No location description',
    'Missing Phone number' => 'Missing Phone number',
    'OTP sent to vendor successfully' => 'OTP sent to vendor successfully',
    'ACTIVATION_SMS' => 'Hello %name%, your activation code is %code%. Please enter this code to verify your account.',
    
    // SMS Template Translations
    'Hello %name%, Your Demo password reset code is %code%' => 'Hello %name%, Your Demo password reset code is %code%',
    'Hello %name%, Your Demo Activation code is %code%' => 'Hello %name%, Your Demo Activation code is %code%',
    'Hello %name%, Your Demo order for %volume% from %vendor_name% has been rejected' => 'Hello %name%, Your Demo order for %volume% from %vendor_name% has been rejected',
    'Hello %name%, REF:DEMO-%ref%, You have a %single_shared% Demo order of %volume% to %address%,  %date%. %order_details%' => 'Hello %name%, REF:DEMO-%ref%, You have a %single_shared% Demo order of %volume% to %address%,  %date%. %order_details%',
    'Hello %name%, Your Demo order for %volume% from %vendor_name% has been accepted and ready for delivery on %date%' => 'Hello %name%, Your Demo order for %volume% from %vendor_name% has been accepted and ready for delivery on %date%',
    'Hello %name%, Your Demo order for %volume% from %vendor_name% has been delivered on %date%' => 'Hello %name%, Your Demo order for %volume% from %vendor_name% has been delivered on %date%',
    'Hello %name%, Your %single_shared% Demo order for %volume% from %vendor_name% on %date% has been created successfully' => 'Hello %name%, Your %single_shared% Demo order for %volume% from %vendor_name% on %date% has been created successfully',
    'Hello %name%, Your Demo order for %volume% from %vendor_name% on %date% has been rejected' => 'Hello %name%, Your Demo order for %volume% from %vendor_name% on %date% has been rejected',
    'Hello %name%, Your Demo vendor account has been created successfully. Your login credentials are as follows: Phone: %phone% and Password: %password%' => 'Hello %name%, Your Demo vendor account has been created successfully. Your login credentials are as follows: Phone: %phone% and Password: %password%',
    'Hello %name%, Your Demo vendor account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%' => 'Hello %name%, Your Demo vendor account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%',
    
    // Time of Day Translations
    'Good Morning' => 'Good Morning',
    'Good Afternoon' => 'Good Afternoon',
    'Good Evening' => 'Good Evening',

    // AppController translations
    'Missing API token' => 'Missing API token',
    'Failed to update vendor online status' => 'Failed to update vendor online status',
    'Vendor online status updated successfully' => 'Vendor online status updated successfully',
    'Missing required fields' => 'Missing required fields',
    'Failed to update vendor location' => 'Failed to update vendor location',
    'Vendor location updated successfully' => 'Vendor location updated successfully',
    
    // actionVerifyPhone translations
    'Missing Phone number or code' => 'Missing Phone number or code',
    'Invalid code' => 'Invalid code',
    'Vendor verified successfully' => 'Vendor verified successfully',
    
    // actionForgotPassword translations
    'Failed to save vendor details.' => 'Failed to save vendor details.',

    // actionUpdateLocation translations
    'Location Updated' => 'Location Updated',
    'Your location has been updated successfully to %location%' => 'Your location has been updated successfully to %location%',
    
    // actionAcceptOrder translations
    'Missing or invalid Authorization header' => 'Missing or invalid Authorization header',
    'Missing API token' => 'Missing API token',
    'Vendor not found' => 'Vendor not found',
    'Vendor is inactive' => 'Vendor is inactive',
    'Missing required fields' => 'Missing required fields',
    'Invalid Request' => 'Invalid Request',
    'Order is already accepted' => 'Order is already accepted',
    'Vendor has no available volume to deliver this order' => 'Vendor has no available volume to deliver this order',
    'Order Save Error' => 'Order Save Error',
    'Order Accepted' => 'Request Accepted',
    'Order Declined' => 'Request Declined',
    'Your order with %vendor_name% for %volume% barrels has been accepted dhaamiye://order/%order_id%' => 'Your order with %vendor_name% for %volume% barrels has been accepted dhaamiye://order/%order_id%',
    'Order has been accepted successfully' => 'Order has been accepted successfully',
    'Hello %name%, You have accepted to deliver %volume% to %address% on %date%. REF-%ref%' => 'Hello %name%, You have accepted to deliver %volume% to %address% on %date%. REF-%ref%',
    'Order has been declined successfully' => 'Order has been declined successfully',
    'Hello %name%, Your Demo order for %volume% from %vendor_name% on %date% has been declined. Kindly select another vendor from Demo app' => 'Hello %name%, Your Demo order for %volume% from %vendor_name% on %date% has been declined. Kindly select another vendor from Demo app',
    'Order has been marked as delivered successfully' => 'Order has been marked as delivered successfully',
    'pending' => 'pending',
    'success' => 'success',
    'order' => 'order',
    'customer' => 'customer',
    'vendor' => 'vendor',
];