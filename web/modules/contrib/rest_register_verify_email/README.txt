INTRODUCTION
------------

The REST Register User with Email Verification module 
Registers Users via a custom REST endpoint, Blocking the User initally, 
then provides another endpoint to validate a user's email address 
with a custom token.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/rest_register_verify_email

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/rest_register_verify_email




REQUIREMENTS
------------

This module only requires these Core Modules be activated:

 * RESTful Web Services (https://www.drupal.org/docs/8/api/restful-web-services-api)
 * Serialization (https://www.drupal.org/docs/8/api/serialization-api)



INSTALLATION
------------
 
 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.

 * Enable module (admin/modules -> CUSTOM -> REST Register User with Email Verification)



CONFIGURATION
-------------
 
 * Configure user permissions in Administration » Configuration » REST:

    - Enable "Create account"
        * Select Method -> POST, format -> json, and Auth -> Na 
    - Enable "Activate user Via Temp token"
        * Select Method -> POST, format -> json, and Auth -> Na 

 * Configure user permissions in Administration » Configuration » REST » Account Settings
    
    - Who can register accounts?
        * Visitors - no admin approval needed
        * no email validation required - we’ll handle in this module

    - Emails -> Rest Verify Email Token
        * Modify Email as needed

 * Clear your Drupal cache

 

HOW TO USE
----------

1. Post "mail" and "pass" to endpoint. optionally add "lang" for language of email.

ENDPOINT: Register New User and Send Verification Email
Method: POST

SITE + /rest/create-account?_format=json
{
  "mail": "your@yoursite.email",
  "pass": "password"
}
optionally add "lang" for language of email


2. The User will get an email (from Admin/Configuration/REST/Account settings) with the temp_token.

3. Post this "temp_token" (with "name") to this endpoint to activate the user account.

ENDPOINT: Verify User With Token and Activate
Method: POST
SITE + /rest/verify-account?_format=json
{
  "name": "your@yoursite.email",
  "temp_token":"TOKEN_SENT_IN_EMAIL"
}


4. User is ready for login



MAINTAINERS
-----------

Current maintainers:
 * J.D. Gibbs (jdiza) - https://drupal.org/u/jdiza
 * Matt King (matteking) - https://drupal.org/u/matteking
 * Matt Rahr (mogollon22) - https://www.drupal.org/u/mogollon22

This project has been sponsored by:
 * University of Arizona College of Agriculture and Life Sciences, Communications and Cyber Technologies
   https://cct.cals.arizona.edu/




Please see the project page for more information
at https://www.drupal.org/project/rest_register_verify_email
