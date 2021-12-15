<?php

/** @var eZModule $Module */
$Module = $Params['Module'];

try {
    $client = DedaClientFactory::instance()->makeClient();
    header('Content-Type: text/xml; charset=utf-8');
    header("Content-Disposition: inline; filename=metadata.xml");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $client->getMetadata();
    eZExecution::cleanExit();

}catch (Exception $e){
    eZDebug::writeError($e->getMessage(), __FILE__);
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}