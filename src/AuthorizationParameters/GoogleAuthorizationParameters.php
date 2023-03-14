<?php

namespace nyan02\kphp_oauth2_client\AuthorizationParameters;

class GoogleAuthorizationParameters implements AuthorizationParametersInterface
{
    public string $response_type;
    public string $client_id;
    public string $redirect_uri;
    public string $state;
    public ?string $hosted_domain;
    public ?string $access_type;
    public ?string $prompt;
    public string $scope;
    public ?string $code_challenge_method;
    public ?string $code_challenge;

    /**
     * Needed in order to use KPHP JsonEncoder.
     *
     */
    public function __construct(string $response_type, string $client_id, string $redirect_uri, string $state,
                                string $scope, ?string $hosted_domain, ?string $access_type, ?string $prompt){

        $this->response_type = $response_type;
        $this->client_id = $client_id;
        $this->redirect_uri = $redirect_uri;
        $this->scope = $scope;
        $this->state = $state;
        $this->hosted_domain = $hosted_domain;
        $this->access_type = $access_type;
        $this->prompt = $prompt;

    }

    /**
     * Builds the authorization URL's query string.
     *
     * @return string Query string
     */
    public function getAuthorizationQuery()
    {
        return http_build_query(to_array_debug($this), '', '&', \PHP_QUERY_RFC3986);
    }
}