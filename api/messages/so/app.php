<?php

/**
 * Somali language translations for the app
 */
return [
    // AuthController actionLogin translations
    'Missing Phone number or password' => 'Lambarka telefoonka ama furaha sirta ah ma jiro',
    'Vendor not found' => 'Biyoolaha lama helin',
    'Account pending activation. Click forgot password' => 'Xisaabta waa sugaya in la hawlgeliyo. Guji furaha sirta ah waa la illaaway',
    'Vendor is inactive' => 'Biyoolaha aan shaqaynayn',
    'Invalid password' => 'Furaha sirta ah waa khalad',
    'System error. Please try again later or contact support' => 'Khaladka nidaamka. Fadlan mar kale isku day ama la xiriir caawinta',
    'Vendor logged in successfully' => 'Biyoolaha si guul leh ayaa u galay',
    'No location description' => 'Faahfaahin goob ah ma jiro',
    'Missing Phone number' => 'Lambarka telefoonka ma jiro',
    'OTP sent to vendor successfully' => 'OTP si guul leh ayaa loo diray biyoolaha',
    'ACTIVATION_SMS' => 'Salaan %name%, koodkaaga hawlgelinta waa %code%. Fadlan geli koodkan si aad u xaqiijiso xisaabtaaga.',
    
    // SMS Template Translations
    'Hello %name%, Your Dhaamiye Darawal password reset code is %code%' => 'Salaan %name%, koodkaaga cusub ee furaha sirta ah waa %code%',
    'Hello %name%, Your Dhaamiye Activation code is %code%' => 'Salaan %name%, koodkaaga hawlgelinta waa %code%',
    'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been rejected' => 'Salaan %name%, dalabaadkaaga Dhaamiye ee %volume% ka soo qaaday %vendor_name% waa la diiday',
    'Hello %name%, REF:HWA-%ref%, You have a %single_shared% Dhaamiye order of %volume% to %address%,  %date%. %order_details%' => 'Salaan %name%, TIX:HWA-%ref%, waxaad haysataa dalabaad %single_shared% Dhaamiye oo %volume% ah oo loo diray %address%, %date%. %order_details%',
    'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been accepted and ready for delivery on %date%' => 'Salaan %name%, dalabaadkaaga Dhaamiye ee %volume% ka soo qaaday %vendor_name% waa la aqbalay oo diyaar u ah soo saarida %date%',
    'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% has been delivered on %date%' => 'Salaan %name%, dalabaadkaaga Dhaamiye ee %volume% ka soo qaaday %vendor_name% waa la soo saaray %date%',
    'Hello %name%, Your %single_shared% Dhaamiye order for %volume% from %vendor_name% on %date% has been created successfully' => 'Salaan %name%, dalabaadkaaga %single_shared% Dhaamiye ee %volume% ka soo qaaday %vendor_name% %date% si guul leh ayaa loo sameeyey',
    'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% on %date% has been rejected' => 'Salaan %name%, dalabaadkaaga Dhaamiye ee %volume% ka soo qaaday %vendor_name% %date% waa la diiday',
    'Hello %name%, Your Dhaamiye vendor account has been created successfully. Your login credentials are as follows: Phone: %phone% and Password: %password%' => 'Salaan %name%, xisaabtaaga biyoolaha Dhaamiye si guul leh ayaa loo sameeyey. Aqoonsigaaga galitaanka waa: Telefoon: %phone% iyo Furaha sirta ah: %password%',
    'Hello %name%, Your Dhaamiye vendor account has been reset. Your new login credentials are as follows: Phone: %phone% and Password: %password%' => 'Salaan %name%, xisaabtaaga biyoolaha Dhaamiye waa la dib u habeyn. Aqoonsigaaga cusub ee galitaanka waa: Telefoon: %phone% iyo Furaha sirta ah: %password%',
    
    // Time of Day Translations
    'Good Morning' => 'Subax Wanaagsan',
    'Good Afternoon' => 'Galab Wanaagsan',
    'Good Evening' => 'Fiid Wanaagsan',

    // AppController translations
    'Missing API token' => 'Lambarka API ma jiro',
    'Failed to update vendor online status' => 'Ku guuldareystay in la cusbooneysiiyo xaaladda online-ka biyoolaha',
    'Vendor online status updated successfully' => 'Xaaladda online-ka biyoolaha si guul leh ayaa loo cusbooneysiiyay',
    'Missing required fields' => 'Goobaha muhiimka ah ma jiraan',
    'Failed to update vendor location' => 'Ku guuldareystay in la cusbooneysiiyo goobta biyoolaha',
    'Vendor location updated successfully' => 'Goobta biyoolaha si guul leh ayaa loo cusbooneysiiyay',
    
    // actionVerifyPhone translations
    'Missing Phone number or code' => 'Lambarka telefoonka ama koodka ma jiro',
    'Invalid code' => 'Koodka waa khalad',
    'Vendor verified successfully' => 'Biyoolaha si guul leh ayaa la xaqiijiyay',
    
    // actionForgotPassword translations
    'Failed to save vendor details.' => 'Aan la keydin karin faahfaahinta biyoolaha.',

    // actionUpdateLocation translations
    'Location Updated' => 'Goobta biyoolaha si guul leh ayaa loo cusbooneysiiyay',
    'Your location has been updated successfully to %location%' => 'Goobta biyoolaha si guul leh ayaa loo cusbooneysiiyay %location%',
    
    // actionAcceptOrder translations
    'Missing or invalid Authorization header' => 'Madaxa aqoonsiga ma jiro ama waa khalad',
    'Invalid Request' => 'Fadlan ma jiro',
    'Order is already accepted' => 'Dalabaadka horey u aqbalay',
    'Vendor has no available volume to deliver this order' => 'Biyoolaha ma haysan wax volume ah oo ku filan in uu soo saaro dalabaadkan',
    'Order Save Error' => 'Khaladka keydinta dalabaadka',
    'Order Accepted' => 'Dalabaadka waa la aqbalay',
    'Order Declined' => 'Request Declined',
    'Your order with %vendor_name% for %volume% barrels has been accepted dhaamiye://order/%order_id%' => 'Dalabaadkaaga %vendor_name% ee %volume% barrels ayaa la aqbalay dhaamiye://order/%order_id%',
    'Order has been accepted successfully' => 'Dalabaadka si guul leh ayaa la aqbalay',
    'Hello %name%, You have accepted to deliver %volume% to %address% on %date%. REF-%ref%' => 'Salaan %name%, waxaad haysataa dalabaad %volume% ah oo loo diray %address%, %date%. TIX:HWA-%ref%',
    'Order has been declined successfully' => 'Dalabaadka si guul leh ayaa la diiday',
    'Hello %name%, Your Dhaamiye order for %volume% from %vendor_name% on %date% has been declined. Kindly select another vendor from Dhaamiye app' => 'Salaan %name%, dalabaadkaaga Dhaamiye ee %volume% ka soo qaaday %vendor_name% %date% waa la diiday. Fadlan xaqiiji dalabaadkaaga biyoolaha dhaamiye app',
    'Order has been marked as delivered successfully' => 'Dalabaadka si guul leh ayaa la soo saaray',
    'pending' => 'sugaya',
    'success' => 'guul',
    'order' => 'dalabaad',
    'customer' => 'macmiil',
    'vendor' => 'biyoolaha',
];