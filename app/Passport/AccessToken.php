<?php

namespace App\Passport;

use App\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Laravel\Passport\Bridge\AccessToken as BaseToken;

class AccessToken extends BaseToken {

	private $privateKey;

	/**
	 * Generate a string representation from the access token
	 */
	public function __toString() {
		return (string) $this->convertToJWT( $this->privateKey );
	}

	/**
	 * Set the private key used to encrypt this access token.
	 */
	public function setPrivateKey( CryptKey $privateKey ) {
		$this->privateKey = $privateKey;
	}

	public function convertToJWT( CryptKey $privateKey ) {
		$builder = new Builder();
		$builder->permittedFor( $this->getClient()->getIdentifier() )
		        ->identifiedBy( $this->getIdentifier(), true )
		        ->issuedAt( time() )
		        ->canOnlyBeUsedAfter( time() )
		        ->expiresAt( $this->getExpiryDateTime()->getTimestamp() )
		        ->relatedTo( $this->getUserIdentifier() )
		        ->withClaim( 'iss', 'http://localhost:8080/' )
                ->withClaim( 'scopes', $this->getScopes() );
            

		if ( $user = User::find( $this->getUserIdentifier() ) ) {
            $builder
                ->withClaim('admin', true)
                ->withClaim('name', $user->name)
                ->withClaim('https://hasura.io/jwt/claims',[
                    "x-hasura-allowed-roles" => ["user"],
                    "x-hasura-default-role" => "user",
                    "x-hasura-user-id" => strval($user->id)
                ]
             );
		}

		return $builder
			->getToken( new Sha256(), new Key( $privateKey->getKeyPath(), $privateKey->getPassPhrase() ) );
	}
}