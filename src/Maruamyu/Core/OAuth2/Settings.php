<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth2 settings object
 */
class Settings
{
    /** @var string */
    public $clientId;

    /** @var string */
    public $clientSecret;

    /** @var string */
    public $authorizationEndpoint;

    /** @var string */
    public $tokenEndpoint;

    /** @var string */
    public $revocationEndpoint;
}
