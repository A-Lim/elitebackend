<?php
namespace App\Http\Traits;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse {
    protected $headers = ['Content-Type' => 'application/json'];

    public function responseWithRedirect($statusCode, $redirect) {
        return response()
            ->json(['redirect' => $redirect], $statusCode, $this->headers);
    }

    public function responseWithData($statusCode, $data) {
        return response()
            ->json(['data' => $data], $statusCode, $this->headers);
    }

    public function responseWithDataAndRedirect($statusCode, $data, $redirect) {
        return response()
            ->json(['data' => $data, 'redirect' => $redirect], $statusCode, $this->headers);
    }

    public function responseWithMessage($statusCode, $message) {
        return response()
            ->json(['message' => $message], $statusCode, $this->headers);
    }

    public function responseWithMessageAndRedirect($statusCode, $message, $redirect) {
        return response()
            ->json(['message' => $message, 'redirect' => $redirect], $statusCode, $this->headers);
    }

    public function responseWithMessageAndData($statusCode, $data, $message) {
        return response()
            ->json(['data' => $data, 'message' => $message], $statusCode, $this->headers);
    }

    public function responseWithError($statusCode, $error_code, $message) {
        return response()
            ->json(['error_code' => $error_code, 'message' => $message], $statusCode, $this->headers);
    }

    public function responseWithToken($statusCode, $token) {
        $data = [
            'tokenType' => $token->token_type,
            'expiresIn' => $token->expires_in,
            'accessToken' => $token->access_token,
            'refreshToken' => $token->refresh_token,
        ];
        return response()
            ->json(['data' => $data], $statusCode, $this->headers);
    }

    public function responseWithLoginData($statusCode, $token, $user, $permissions) {
        $createdAt = new Carbon($token->token->created_at);
        $expiresAt = new Carbon($token->token->expires_at);

        $data = [
            'tokenType' => 'Bearer',
            'expiresIn' => $expiresAt->diffInSeconds($createdAt),
            'accessToken' => $token->accessToken,
            'user' => $user,
            'permissions' => $permissions
        ];
        return response()
            ->json(['data' => $data], $statusCode, $this->headers);
    }
}
