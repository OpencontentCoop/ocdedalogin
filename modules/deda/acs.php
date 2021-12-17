<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
try {
    if (!$Http->hasVariable('SAMLResponse')){
        $Module->redirectTo('/');
        return;
    }
    $client = DedaClientFactory::instance()->makeClient();
    $userData = $client->checkAssertion($Http->variable('SAMLResponse'));
    $handler = new DedaUserHandler($userData['attributi_utente']);
    $user = $handler->login();

    return $handler->handleRedirect($Module, $user);

}catch (Exception $e){
    eZDebug::writeError($e->getMessage(), __FILE__);
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}
