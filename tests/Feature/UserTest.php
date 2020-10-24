<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use  WithFaker, RefreshDatabase;

    /**
     * User can Login via browser
     * @test
     */
    public function userCanLoginWithBrowser()
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'browser',
        ]);
        $response->assertStatus(200);
    }

    /**
     * User Cannot send a request to protected route without api token
     * @test
     */

    public function userCannotRequestToProtectedRoute()
    {
        $response = $this->getJson('/api/testUsers');
        $response->assertUnauthorized();
    }

    /**
     * User Cannot send a request to protected route without api token
     * @test
     */

    public function userCanRequestToProtectedRouteWithToken()
    {
        $this->withExceptionHandling();
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'browser',
        ]);
        $token = json_decode($response->getContent());
        $responseUsers = $this->withHeader('Authorization',
            'Bearer ' . $token->data->token)->getJson('/api/testUsers');
        $responseUsers->assertJsonFragment(['name' => $user->name, 'email' => $user->email]);
        $responseUsers->assertStatus(200);
    }

    /**
     * User data type validation test
     * @test
     */

    public function userEmailIsRequired()
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => $password,
            'device_name' => 'browser',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'email' => 'The email field is required.'
        ]);
    }

    /**
     * @test
     */
    public function userEmailMustValidEmail()
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => 'sdvdsfdsf',
            'password' => $password,
            'device_name' => 'browser',
        ]);

        $response->assertJsonValidationErrors([
            'email' => 'The email must be a valid email address.'
        ]);
    }

    /**
     * @test
     */
    public function userPasswordIsRequired()
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '',
            'device_name' => 'browser',
        ]);

        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
    }

    /**
     * @test
     */
    public function userHaveToValidCredential()
    {
        $password = $this->faker->password;
        $user = factory(User::class)->create(['password' => Hash::make($password)]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $this->faker->password,
            'device_name' => 'browser',
        ]);

        $response->assertJsonValidationErrors([
            'email' => 'The provided credentials are incorrect.'
        ]);
    }
}
