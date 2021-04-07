<?php
ob_start();
require __DIR__."/vendor/autoload.php";

if(empty($_SESSION["userLogin"])){
    echo "<h1>Guest</h1>";

    /**
     * Auth Facebook
     */
    $facebook = new \League\OAuth2\Client\Provider\Facebook([
        'clientId'          => FACEBOOK["app_id"],
        'clientSecret'      => FACEBOOK["app_secret"],
        'redirectUri'       => FACEBOOK["app_redirect"],
        'graphApiVersion'   => FACEBOOK["app_version"],
    ]);

    if (!isset($_GET['code'])) {

        // If we don't have an authorization code then get one
        $authUrl = $facebook->getAuthorizationUrl([
            'scope' => ['email'],
        ]);
        $_SESSION['oauth2state'] = $facebook->getState();
        
        echo '<a href="'.$authUrl.'">Log in with Facebook!</a>';
        exit;
    
        // Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;

}

// Try to get an access token (using the authorization code grant)
$token = $facebook->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

try {

    // We got an access token, let's now get the user's details
    $user = $facebook->getResourceOwner($token);

    // Use these details to create a new profile
    echo "<img width='120' alt='' src='{$user->getPictureUrl()}'/> <h1>Bem-vindo (a)  {$user->getName()} </h1>";

    echo "<a title='Sair' href='?off=true'>Sair</a>";
    $off = filter_input(INPUT_GET, "off", FILTER_VALIDATE_BOOLEAN);
    if($off){
        unset($_SESSION["userLogin"]);
        header("Refresh: 0");
    }
 
} catch (\Exception $e) {

    // Failed to get user details
    exit('Oh dear...');
}

}
ob_end_flush();

