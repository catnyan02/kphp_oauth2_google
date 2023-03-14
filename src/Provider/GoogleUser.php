<?php

namespace nyan02\kphp_oauth2_client\Provider;

use JsonEncoder;

class GoogleUser implements ResourceOwnerInterface
{
    /** @var string */
    protected $sub;

    /** @var string */
    public $name;

    /** @var ?string */
    public $given_name;

    /** @var ?string */
    public $family_name;

    /** @var ?string */
    public $locale;

    /** @var ?string */
    public $hd;

    /** @var ?string */
    public $email;

    /** @var ?string */
    public $picture;


    public function __construct(string $sub, string $name, ?string $given_name = null, ?string $family_name = null,
                                ?string $locale = null, ?string $hd = null, ?string $email = null,
                                ?string $picture = null)
    {
        $this->sub = $sub;
        $this->name = $name;
        $this->given_name = $given_name;
        $this->family_name = $family_name;
        $this->locale = $locale;
        $this->hd = $hd;
        $this->email = $email;
        $this->picture = $picture;

    }

    public function getId()
    {
        return $this->sub;
    }

    public function toJSON(): string
    {
        return JsonEncoder::encode($this);
    }
}