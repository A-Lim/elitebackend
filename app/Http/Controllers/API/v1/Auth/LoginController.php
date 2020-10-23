<?php
namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Passport\Passport;

use App\Http\Controllers\ApiController;

use Carbon\Carbon;
use App\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;

use App\Repositories\Auth\OAuthRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;

class LoginController extends ApiController {

    use AuthenticatesUsers, ThrottlesLogins;

    private $oAuthRepository;
    private $userRepository;

    public function __construct(OAuthRepositoryInterface $oAuthRepositoryInterface,
        UserRepositoryInterface $iUserRepository) {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth:api')->except(['login', 'refresh']);

        $this->oAuthRepository = $oAuthRepositoryInterface;
        $this->userRepository = $iUserRepository;
    }

    public function login(LoginRequest $request) {
        // if too many login attemps
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        
        if ($this->attemptLogin($request)) {
            $this->clearLoginAttempts($request);
            
            $user = auth()->user();
            switch ($user->status) {
                case User::STATUS_LOCKED:
                    return $this->responseWithMessage(401, 'This account is locked. Please contact administrator.');
                    break;
                
                case User::STATUS_UNVERIFIED:
                    return $this->responseWithMessage(401, 'This account is unverified. Please verify your email');
                    break;
            }

            $tokenResult = $user->createToken('accesstoken');
            $permissions = $this->userRepository->permissions($user);
            
            return $this->responseWithLoginData(200, $tokenResult, $user, $permissions);
        }

        // if unsuccessful, increase login attempt count
        // lock user count limit reached
        $this->incrementLoginAttempts($request);

        return $this->responseWithMessage(401, 'Invalid login credentials.');
    }

    public function logout(Request $request) {
        $accessToken = auth()->user()->token();
        // revoke refresh token
        $this->oAuthRepository->revokeRefreshToken($accessToken->id);
        // revoke access token
        $accessToken->revoke();

        return $this->responseWithMessage(201, "Successfully logged out.");
    }
}
