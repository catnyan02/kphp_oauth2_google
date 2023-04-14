This package provides Google OAuth 2.0 support for the KPHP

###Installation
To install, use composer:

```composer require nyan02/kphp_oauth2_google```

###Usage
Usage is similar to KPHP OAuth client, using nyan02\kphp_oauth2_client\Provider\Google
as the provider.

You need to create a new Provider object specifying google-client-id,
google-client-secret and callback-url.

If you want to restrict access and allow it only for users 
on your G Suite/Google Apps for Business accounts (corporate emails), 
you can configure the provider to set Hosted Domain.

You can see the example below.
###Authorization Code Example
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

###Authorization Code Flow
After configuring provider we want to get Authorization Code. We use
method getAuthorizationParameters() to get parameters from the provider
including permission scopes and other info needed for generating
AuthorizationUrl.

Next we generate AuthorizationUrl using method getAuthorizationUrl($params)
and passing parameters we've got before. Now that we have the Url we can
redirect the user to Authorization page of provider.

Once we've got Authorization Code we create a placeholder class for it

```new AuthorizationCode($provider->getClientId(), $provider->getClientSecret(), $provider->getRedirectUri())```

And pass it to getAccessToken method together with the code we've got.

```$token = $provider->getAccessToken($grant, ['code' => $_GET['code']]);```

Now we have the Access Token to Resource.

###Getting ResourceOwner Information
With Access Token we can now access User's information.

```$ownerDetails = $provider->getResourceOwner($token);```

Implemented methods for GoogleResourceOwner are getId() and
toJSON(). All the class attributes are public, so you can directly
access them. GoogleResourceOwner has the following attributes:
sub, name, given_name, family_name, locale, hd, email, picture.
