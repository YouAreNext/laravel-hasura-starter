<?php

namespace App\GraphQL;

use App\Contracts\AuthModelFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

class BaseAuthResolver
{
    /**
     * @param array $args
     * @param string $grantType
     * @return mixed
     */
    public function buildCredentials(array $args = [], $user)
    {
        $credentials = $args;
        // $credentials['password'] = Hash::make($args['password']);
        $credentials['sub'] = '1234567890';
        $credentials['name'] = $user->name;
        $credentials['iat'] = 1516239022;
        $credentials['admin'] = true;
        $credentials['https://hasura.io/jwt/claims'] = [
            "x-hasura-allowed-roles" => ["user"],
            "x-hasura-default-role" => "user",
            "x-hasura-user-id" => $user['id'],
            "x-hasura-custom" => "custom-value"
        ];
        $credentials['client_id'] = 2;
        $credentials['client_secret'] = 'EPXK09v0E58fYVYCp0unTFegbDsi5nZNXvQIlOqP';
        $credentials['grant_type'] = "password";

        return $credentials;
    }

    public function makeRequest(array $credentials)
    {
        $request = Request::create('oauth/token', 'POST', $credentials,[], [], [
            'HTTP_Accept' => 'application/json'
        ]);
        $response = app()->handle($request);
        $decodedResponse = json_decode($response->getContent(), true);
        if ($response->getStatusCode() != 200) {
            throw new AuthenticationException($decodedResponse['message']);
        }
        return $decodedResponse;
    }

    protected function getAuthModelFactory(): AuthModelFactory
    {
        return app(AuthModelFactory::class);
    }
}