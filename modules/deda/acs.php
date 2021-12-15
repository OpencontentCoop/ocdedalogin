<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
try {
    $client = DedaClientFactory::instance()->makeClient();
    $userData = $client->checkAssertion($Http->variable('SAMLResponse'));
    
    return;

}catch (Exception $e){
    eZDebug::writeError($e->getMessage(), __FILE__);
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}