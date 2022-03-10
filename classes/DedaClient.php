<?php

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function GuzzleHttp\json_decode;

class DedaClient implements DedaClientInterface
{
    const TEST_BASE_URL = 'https://authtest.soluzionipa.it/spid';

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $issuer;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(HttpClientInterface $httpClient, $baseUrl, $clientId, $secret, $issuer)
    {
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
    }

    public function getMetadata()
    {
        $response = $this->httpClient->request(
            'GET',
            $this->baseUrl . '/get_metadata?client_id=' . $this->clientId,
            [
                'auth_bearer' => $this->getAuthBearer()
            ]
        );

        $content = json_decode($response->getContent(), true);
        if ($content['esito'] === 'ko') {
            throw new Exception($content['msg_errore']);
        }

        return $content['metadata'];
    }

    public function getAuthRequest($idp = 'poste')
    {
        $response = $this->httpClient->request(
            'GET',
            $this->baseUrl . '/get_auth_request?client_id=' . $this->clientId . '&idp=' . $idp,
            [
                'auth_bearer' => $this->getAuthBearer()
            ]
        );

        $content = json_decode($response->getContent(), true);
        if ($content['esito'] === 'ko') {
            throw new Exception($content['msg_errore']);
        }

        return $content['sso_request'];
    }

    public function checkAssertion($assertion)
    {
        if ($this->baseUrl == self::TEST_BASE_URL) {
            $assertionSample = '{
    "esito": "ok",
    "provider_id": "infocert", 
    "attributi_utente": {
        "spidCode": "INF___",
        "name": "Pinco",
        "familyName": "Pallino",
        "fiscalNumber": "TINIT-PNCPLN80A01H501P",
        "email": "pinco@pallino.it",
        "gender": "M",
        "dateOfBirth": "1984-11-09",
        "placeOfBirth": "C383",
        "countyOfBirth": "VE",
        "idCard": "cartaIdentita AS___ Comune 2012-07-02 2022-11-09", 
        "address": "Via Test 22 35045 Padova PD",
        "digitalAddress": null,
        "expirationDate": "2022-02-20",
        "mobilePhone": "+3933_____",
        "ivaCode": null,
        "registeredOffice": null
    },
    "response_id": "_ce91d0c0-f769-0138-d91c-005056a556a5",
    "info_tracciatura": {
        "response": "eJztWulu4sq2fhXEnFoATb2GBH....+jegQZbQ",
        "response_id": "_7ea5b0264e041ff82a78ff1f11afe272", 
        "response_issue_instant": "2020-10-23T14:28:07.138Z", 
        "response_issuer": "https:\/\/identity.infocert.it", 
        "assertion_id": "_e8b5f42d67e8104aba6ae53ba018daf4", 
        "assertion_subject": "_8817a7e9e0c1a755f5ae9f3f4e386410",
        "assertion_subject_name_qualifier": "https:\/\/identity.infocert.it"
    }
}';

            return json_decode($assertionSample, true);
        }

        $response = $this->httpClient->request(
            'POST',
            $this->baseUrl . '/check_assertion',
            [
                'json' => [
                    'client_id' => $this->clientId,
                    'assertion' => $assertion
                ],
                'auth_bearer' => $this->getAuthBearer(),
            ]
        );

        $content = json_decode($response->getContent(), true);
        if ($content['esito'] === 'ko') {
            throw new Exception($content['msg_errore'] . ' ' . $content['dettaglio_log_errore']);
        }

        return $content;
    }

    public function createHeadersFromAssertion($assertion)
    {
        $data = [
            'session' => $assertion['response_id'],
            'spid-level' => '',
            'idp-entity-id' => $assertion['provider_id'],
            'provider' => $assertion['provider_id'],
        ];

        $data = array_merge($data, (array)$assertion['attributi_utente']);

        return ['X-Forwarded-User' => base64_encode(json_encode($data))];
    }

    private function getAuthBearer()
    {
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->secret)
        );

        $now = new \DateTimeImmutable();
        $token = $configuration->builder()
            ->issuedBy($this->issuer)
            ->issuedAt($now)
            ->withClaim('hash_assertion_consumer', '')
            ->withClaim('start', $now->format('dmYHis'))
            ->getToken($configuration->signer(), $configuration->signingKey());

        return $token->toString();
    }
}
