<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\SendVerificationEmailRequest;
use App\Repositories\User\UserRepositoryInterface;

class VerificationController extends ApiController {

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepositoryInterface) {
        $this->middleware('signed')->only('verifyEmail');
        $this->middleware('throttle:6,1')->only('verifyEmail', 'resend');

        $this->userRepository = $userRepositoryInterface;
    }

    public function verifyEmail(VerifyEmailRequest $request) {
        $statusCode = '';
        $message = '';

        $user = $this->userRepository->searchForOne(['email' => $request->email]);
        if ($user == null) {
            $statusCode = 401;
            $message = 'User not found.';
        }

        if (!hash_equals((string) $request->get('id'), (string) $user->getKey())) {
            $statusCode = 401;
            $message = 'Invalid id.';
        }

        if (!hash_equals((string) $request->get('hash'), sha1($user->getEmailForVerification()))) {
            $statusCode = 401;
            $message = 'Invalid hash.';
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $statusCode = 200;
            $message = 'Email succesfully verified.';
        }

        return $this->responseWithMessage($statusCode, $message);
    }

    public function sendVerificationEmail(SendVerificationEmailRequest $request) {

        $user = $this->userRepository->searchForOne(['email' => $request->email]);
        $user->sendEmailVerificationNotification();
        
        if ($user->hasVerifiedEmail()) {
            return $this->responseWithMessage(400, 'Email is already verified.');
        }

        return $this->responseWithMessage(200, 'Verification email sent.');
    }
}
