```
<?php

use nyan02\kphp_oauth2_client\Grant\AuthorizationCode;
use nyan02\kphp_oauth2_client\Provider\Google;

require_once __DIR__ . '/vendor/autoload.php';

$provider = new Google('{google-client-id}',
    '{google-client-secret}',
    'https://example.com/callback-url',
    );
    
$provider->setHostedDomain('example.com'); // optional; used to restrict access to users on your G Suite/Google Apps for Business accounts

if (!empty($_GET['error'])) {

    // Got an error, probably user denied access
    exit('Got error.');

} elseif (empty($_GET['code'])) {

    // If we don't have an authorization code then get one
    $params = $provider->getAuthorizationParameters();
    $authUrl = $provider->getAuthorizationUrl($params);
//    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

} else {

    // Try to get an access token (using the authorization code grant)
    $grant = new AuthorizationCode($provider->getClientId(), $provider->getClientSecret(), $provider->getRedirectUri());
    $token = $provider->getAccessToken($grant, ['code' => $_GET['code']]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the owner details
        $ownerDetails = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!<br>', $ownerDetails->name);

        // Use this to interact with an API on the users behalf
        echo $token->getToken().'<br>';

        // Use this to get a new access token if the old one expires
        echo $token->getRefreshToken().'<br>';

        // Unix timestamp at which the access token expires
        echo $token->getExpires().'<br>';

    } catch (Exception $e) {

        // Failed to get user details
        exit('Something went wrong: ' . $e->getMessage());

    }
}
```