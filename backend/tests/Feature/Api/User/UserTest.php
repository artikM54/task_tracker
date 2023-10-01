<?php

namespace Tests\Feature\Api\User;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /** test */
    public function test_store_a_user(): void
    {
        $this->withoutExceptionHandling();

        $data = [
            'email' => 'test.test@test.test',
            'password' => 'testpassword',
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович'
        ];

        $response = $this->post('api/users', $data);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        $user = User::with('profile')
            ->get()
            ->first();

        $isPasswordCorrect = Hash::check($data['password'], $user->password);
        $this->assertTrue($isPasswordCorrect);

        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['last_name'], $user->profile->last_name);
        $this->assertEquals($data['first_name'], $user->profile->first_name);
        $this->assertEquals($data['middle_name'], $user->profile->middle_name);

        $fullNameUser = "{$data['last_name']} {$data['first_name']} {$data['middle_name']}";
        $fullNameUser = preg_replace('/\s+/', ' ', trim($fullNameUser));

        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $fullNameUser
            ]
        ]);
    }

    /** test */
    public function test_destroy_a_user(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        $profile = Profile::first();

        $this->assertEquals($user->id, $profile->id);

        $response = $this->delete("api/users/{$user->id}");

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('profiles', 0);

        $response->assertStatus(204);
        $this->assertEmpty($response->content());
    }
}
