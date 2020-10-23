<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use App\Repositories\User\UserRepositoryInterface;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

class ForgotPasswordController extends ApiController {

    use SendsPasswordResetEmails;

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepositoryInterface) {
        $this->middleware('guest');
        $this->userRepository = $userRepositoryInterface;
    }

    public function sendResetLink(ForgotPasswordRequest $request) {
        // send reset link
        $response = Password::broker()->sendResetLink($request->only('email'));
        $statusCode = null;
        $message = null;

        if ($response == Password::RESET_LINK_SENT) {
            $statusCode = 200;
            $message = 'A password reset email is sent.';
        } else {
            $statusCode = 400;
            $message = 'Unable to send password reset email. Please contact the administrator.';
        }

        return $this->responseWithMessage($statusCode, $message);
    }

    public function resetPassword(ResetPasswordRequest $request) {
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
        
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = Password::broker()->reset(
            $credentials, function ($user, $password) {
                $this->userRepository->resetPassword($user, $password);
                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($response == Password::PASSWORD_RESET) {
            $statusCode = 200;
            $message = 'Password successfully resetted.';
        } else {
            $statusCode = 400;
            $message = 'Invalid reset password token.';
        }

        return $this->responseWithMessage($statusCode, $message);
    }
}
