<?php
//TEST URLS ONLYYYYY

// Auth routes (User/Patient))
define('URL_SIGNUP',      '/JAM_Lyingin/auth/signup.php');
define('URL_LOGIN',       '/JAM_Lyingin/auth/login.php');
define('URL_LOGOUT',      '/JAM_Lyingin/logout.php');
// Auth function routes
define('URL_UPDATE_USER', '/JAM_Lyingin/auth/update_user.php');
define('URL_UPDATE_PATIENT', '/JAM_Lyingin/auth/handle_update_patient.php');

// Auth routes (Medical Staff)
define('URL_STAFF_LOGIN', '/JAM_Lyingin/auth/mslogin.php');
define('URL_STAFF_SIGNUP',  '/JAM_Lyingin/msdash.php');
define('URL_STAFF_DASH', '/JAM_Lyingin/dashboard.php');
// Dashboard routes
define('URL_DASH_PATIENT', '/JAM_Lyingin/pdash.php');

// Home
define('URL_HOME', '/JAM_Lyingin/front.php');
define('URL_OTP_PAGE', '/JAM_Lyingin/auth/otp_page.php');