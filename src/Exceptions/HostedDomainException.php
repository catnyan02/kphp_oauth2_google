<?php

namespace nyan02\kphp_oauth2_client\Exceptions;

/**
 * Exceptions thrown if the Google Provider is configured with a hosted domain that the user doesn't belong to
 */
class HostedDomainException extends \Exception
{
    /**
     * @param ?string $configuredDomain
     * @param $configuredDomain
     *
     * @return static
     */
    public static function notMatchingDomain($configuredDomain): self
    {
        return new static("User is not part of domain '$configuredDomain'");
    }
}