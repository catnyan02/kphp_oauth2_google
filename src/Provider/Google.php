<?php

namespace nyan02\kphp_oauth2_client\Provider;

use JsonEncoder;
use nyan02\kphp_oauth2_client\AuthorizationParameters\GoogleAuthorizationParameters;
use nyan02\kphp_oauth2_client\Exceptions\HostedDomainException;
use nyan02\kphp_oauth2_client\Token\AccessTokenInterface;


class Google extends AbstractProvider
{

    /**
     * @var ?string If set, this will be sent to google as the "hd" parameter.
     * @link https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters
     */
    protected $hostedDomain;

    /**
     * @var string Scopes that will be used for authentication separated by separator returned by getScopeSeparator().
     * @link https://developers.google.com/identity/protocols/googlescopes
     */
    protected $scopes = '';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    public function getBaseAccessTokenUrl(): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    public function getResourceOwnerDetailsUrl(AccessTokenInterface $token): string
    {
        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }

    /**
     * @param string $hostedDomain
     */
    public function setHostedDomain(string $hostedDomain): void
    {
        $this->hostedDomain = $hostedDomain;
    }

    public function getAuthorizationParameters(?string $state = null, ?string $scope = null, ?string $redirectUri = null,
                                                  ?string $hd = null, ?string $access_type = null, ?string $prompt= null): GoogleAuthorizationParameters
    {
        $this->hostedDomain = $this->hostedDomain?: $hd;

        $hd = $hd?: $this->hostedDomain;
        $state = $state ?: $this->getRandomState();
        $redirectUri = $redirectUri ?: $this->redirectUri;

        // Default scopes MUST be included for OpenID Connect.
        // Additional scopes MAY be added by constructor or option.

        $scopes = $this->getDefaultScopes() . $this->scopes;

        $scopes = $scope? $scopes . $scope : $scopes;

        // The "approval_prompt" MUST be removed as it is not supported by Google, use "prompt" instead:
        // https://developers.google.com/identity/protocols/oauth2/openid-connect#prompt

        $params = new GoogleAuthorizationParameters("code", $this->clientId, $redirectUri, $state, $scopes,
            $hd, $access_type, $prompt);

        $this->state = $params->state;

        $pkceMethod = $this->getPkceMethod();

        if (!empty($pkceMethod)) {
            $this->pkceCode = $this->getRandomPkceCode();
            if ($pkceMethod === self::PKCE_METHOD_S256) {
                $params->code_challenge = trim(
                    strtr(base64_encode(hash('sha256', $this->pkceCode, true)), '+/', '-_'), '=');
            } elseif ($pkceMethod === self::PKCE_METHOD_PLAIN) {
                $params->code_challenge = $this->pkceCode;
            } else {
                throw new \Exception('Unknown PKCE method "' . $pkceMethod . '".');
            }
            $params->code_challenge_method = $pkceMethod;
        }

        return $params;
    }

    protected function getDefaultScopes(): string
    {
        // "openid" MUST be the first scope in the list.
        return 'openid email profile';
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse($data): void
    {

        $code = 0;

        $parsed_data = json_decode($data, true);

        if (empty($parsed_data['error'])) {
            return;
        }

        $error = $parsed_data['error'];

        if (is_array($error)) {
            $code = intval($error['code']);
            $error = (string)$error['message'];
        }

        throw new \Exception((string) $error, $code);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessTokenInterface $token
     */
    public function getResourceOwner(AccessTokenInterface $token): GoogleUser
    {
        $response = $this->fetchResourceOwnerDetails($token);

        return $this->createResourceOwner($response, $token);
    }

    protected function createResourceOwner(string $response, AccessTokenInterface $token): GoogleUser
    {
        $user = JsonEncoder::decode($response, GoogleUser::class);
        echo $user->hd;
        $this->assertMatchingDomain($user->hd);

        return $user;
    }

    /**
     * @param ?string $hostedDomain
     *
     * @throws HostedDomainException If the domain does not match the configured domain.
     */
    protected function assertMatchingDomain(?string $hostedDomain): void
    {
        if ($this->hostedDomain === null) {
            // No hosted domain configured.
            return;
        }

        if ($this->hostedDomain === '*' && $hostedDomain) {
            // Any hosted domain is allowed.
            return;
        }

        if ($this->hostedDomain === $hostedDomain) {
            // Hosted domain is correct.
            return;
        }

        throw HostedDomainException::notMatchingDomain($this->hostedDomain);
    }
}