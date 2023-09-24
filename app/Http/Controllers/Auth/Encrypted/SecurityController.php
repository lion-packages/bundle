<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Encrypted;

use LionSecurity\RSA;

class SecurityController
{
    private object $rsa_data;

	public function __construct()
    {
        RSA::setPath(env->RSA_URL_PATH === "" ? storage_path("keys/") : env->RSA_URL_PATH);
	}

	public function keys(string $path): SecurityController
    {
		RSA::setPath(storage_path($path));
        return $this;
	}

    public function encode(array $data): SecurityController
    {
        $this->rsa_data = RSA::encode($data);
        return $this;
    }

    public function decode(): SecurityController
    {
        $this->rsa_data = RSA::decode((array) $this->rsa_data);
        return $this;
    }

    public function get(): object
    {
        return $this->rsa_data;
    }
}
