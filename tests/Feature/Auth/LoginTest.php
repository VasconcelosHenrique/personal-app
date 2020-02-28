<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private static $password = 'password';

    private static function loginRoute()
    {
        return route('login');
    }

    private static function homeRoute()
    {
        return route('dashboard.index');
    }

    public function testUserShouldViewLoginFormWhenNotAuthenticated()
    {
        $response = $this->get(self::loginRoute());

        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    public function testUserShouldNotViewALoginFormWhenAuthenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get(self::loginRoute());

        $response->assertRedirect(self::homeRoute());
    }

    public function testUserShouldLoginWithCorrectCredentials()
    {
        $user = factory(User::class)->create(
            ['password' => bcrypt(self::$password)]
        );

        $response = $this->post(self::loginRoute(), [
            'email'    => $user->email,
            'password' => self::$password,
        ]);

        $response->assertRedirect(self::homeRoute());
        $this->assertAuthenticatedAs($user);
    }

    public function testUserShouldNotLoginWithIncorrectPassword()
    {
        $user = factory(User::class)->create(
            ['password' => bcrypt(self::$password)]
        );

        $response = $this->from(self::loginRoute())->post(self::loginRoute(), [
            'email'    => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect(self::loginRoute());
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserShouldBeRememberedWhenActivateRememberMe()
    {
        $user = factory(User::class)->create([
            'id'       => random_int(1, 100),
            'password' => bcrypt(self::$password),
        ]);

        $response = $this->post(self::loginRoute(), [
            'email'    => $user->email,
            'password' => self::$password,
            'remember' => 'on',
        ]);

        $response->assertCookie(Auth::guard()->getRecallerName(),
            vsprintf('%s|%s|%s', [
                $user->id,
                $user->getRememberToken(),
                $user->password
            ])
        );
    }
}
