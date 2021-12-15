<?php

interface DedaClientInterface
{
    public function getMetadata();

    public function getAuthRequest($idp = 'poste');

    public function checkAssertion($assertion);

    public function createHeadersFromAssertion($assertion);
}