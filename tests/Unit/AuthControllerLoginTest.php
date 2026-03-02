<?php

namespace Tests\Unit;

use App\Http\Controllers\API\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthControllerLoginTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_login_returns_token_for_valid_credentials()
    {
        $email = 'valid@example.com';
        $user = new class {
            public $id = 1;
            public $name = 'Valid User';
            public $email = 'valid@example.com';
            public $password;

            public function createToken($name)
            {
                return (object) ['plainTextToken' => 'token-123'];
            }
        };
        $user->password = Hash::make('secret123');

        $this->mockUserLookup($email, $user);

        $controller = new AuthController();
        $request = LoginRequest::create('/api/login', 'POST', [
            'email' => $email,
            'password' => 'secret123',
        ]);

        $response = $controller->login($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('token-123', $response->getData(true)['token']);
    }

    public function test_login_rejects_invalid_password()
    {
        $email = 'valid@example.com';
        $user = new class {
            public $password;
        };
        $user->password = Hash::make('secret123');

        $this->mockUserLookup($email, $user);

        $controller = new AuthController();
        $request = LoginRequest::create('/api/login', 'POST', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

        $response = $controller->login($request);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Invalid credentials', $response->getData(true)['message']);
    }

    private function mockUserLookup(string $email, $user): void
    {
        $query = Mockery::mock();
        $query->shouldReceive('first')->once()->andReturn($user);

        $userAlias = Mockery::mock('alias:App\Models\User');
        $userAlias->shouldReceive('where')
            ->once()
            ->with('email', $email)
            ->andReturn($query);
    }
}
