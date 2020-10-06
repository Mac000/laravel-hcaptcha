<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
//      Hcaptcha Verification
        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
           'secret' => '0x1518f4C8e25964Ae86784441e5ab4EdA877A9773',
            'response' => $input['h-captcha-response'],
        ]);

//      Send a 400 response if Captcha response is false
        if ($response->json()['success'] == false) {
            abort(400, 'Invalid Captcha Request. Make sure you have correctly filled Captcha. Please re-submit captcha if you have already filled out');
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
