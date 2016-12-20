<?php

namespace App\Http\Controllers\Auth;

use Log;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function form()
    {
        return view('pages.auth.login');
    }

    public function submit(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'password' => 'required',
            'full_name'   => 'honeypot',
            'time'   => 'required|honeytime:2',
        ]);

        if ($this->signIn($request)) {
            Log::info('User logged in', [
                'name' =>  $request->name,
            ]);

            return redirect('/')
                ->with('info', trans('auth.login.login.info'));
        }

        return redirect()
            ->back()
            ->with('info', trans('auth.login.failed.info'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect()
            ->route('login.form')
            ->with('info', trans('auth.login.logout.info'));
    }

    protected function signIn(Request $request)
    {
        return Auth::attempt($this->getCredentials($request), $request->has('remember'));
    }

    protected function getCredentials(Request $request)
    {
        return [
            'name'    => $request->input('name'),
            'password' => $request->input('password'),
            'verified' => true,
        ];
    }
}
