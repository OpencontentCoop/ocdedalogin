<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$idp = $Params['IDP'];

if (empty($idp)){

    $tpl = eZTemplate::factory();
    echo $tpl->fetch('design:deda/login.tpl');
    eZDisplayDebug();
    eZExecution::cleanExit();

}else{
    try {
        $client = DedaClientFactory::instance()->makeClient();
        $redirect = $client->getAuthRequest($idp);

        $Module->RedirectURI = $redirect;
        $Module->setExitStatus(eZModule::STATUS_REDIRECT);
        return;

    }catch (Exception $e){
        eZDebug::writeError($e->getMessage(), __FILE__);
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    }
}
