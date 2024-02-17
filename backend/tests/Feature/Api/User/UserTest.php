<?php

namespace Tests\Feature\Api\User;

use App\Models\Profile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Accept' => 'application/json'
        ]);
    }

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
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $user = User::with(['profile'])
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

        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'full_name',
                'accessToken'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $fullNameUser
            ]
        ]);
    }

    /** test */
    public function test_update_a_user(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        $data = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
            'phone' => '78889998888'
        ];

        $response = $this->actingAs($user)->patch("api/users/{$user->id}", $data);

        $userUpdated = User::with('profile')->get()->first();

        $this->assertEquals($data['last_name'], $userUpdated->profile->last_name);
        $this->assertEquals($data['first_name'], $userUpdated->profile->first_name);
        $this->assertEquals($data['middle_name'], $userUpdated->profile->middle_name);
        $this->assertEquals($data['phone'], $userUpdated->profile->phone);

        $response->assertJson([
            'data' => [
                'id' => $userUpdated->id,
                'email' => $userUpdated->email,
                'last_name' => $userUpdated->profile->last_name,
                'first_name' => $userUpdated->profile->first_name,
                'middle_name' => $userUpdated->profile->middle_name,
                'phone' => $userUpdated->profile->phone,
                'avatar' => $userUpdated->profile->avatar
            ]
        ]);
    }

    /** test */
    public function test_update_avatar_of_user(): void
    {
        $this->withoutExceptionHandling();

        // TODO: создавать уже с аватаркой
        $user = User::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        Storage::fake('private');

        $fileName = 'avatar.jpg';
        $file = UploadedFile::fake()->image($fileName);

        $data = [
            'avatar' => $file
        ];

        $response = $this->actingAs($user)->patch("api/users/{$user->id}/change-avatar", $data);

        $this->assertDatabaseCount('files', 1);

        $user = User::with('profile.avatar')
            ->get()
            ->first();

        $user->profile->avatar->path;

        Storage::disk('private')->assertExists($user->profile->avatar->path);

        $this->assertEquals($fileName, $user->profile->avatar->filename);
        $this->assertEquals("avatars/{$file->hashName()}", $user->profile->avatar->path);

        $url = url('storage/' . $user->profile->avatar->path);

        $response->assertJson([
            'data' => [
                'id' => $user->profile->avatar->id,
                'filename' => $user->profile->avatar->filename,
                'url' => $url
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

        $response = $this->actingAs($user)->delete("api/users/{$user->id}");

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('profiles', 0);

        $response->assertStatus(204);
        $this->assertEmpty($response->content());
    }

    /** test */
    public function test_show_a_user(): void
    {
        $this->withoutExceptionHandling();

        User::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        $user = User::with('profile')->get()->first();

        $response = $this->actingAs($user)->get("api/users/{$user->id}");

        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'last_name' => $user->profile->last_name,
                'first_name' => $user->profile->first_name,
                'middle_name' => $user->profile->middle_name,
                'phone' => $user->profile->phone,
                'avatar' => $user->profile->avatar,
            ]
        ]);
    }

    /** test */
    public function test_index_users(): void
    {
        $this->withoutExceptionHandling();

        User::factory()->count(5)->create();

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('profiles', 5);

        $users = User::with('profile')->get();

        $users = $users->map(function($user) {
            $fullNameUser = "{$user->profile->last_name} {$user->profile->first_name} {$user->profile->middle_name}";
            $fullNameUser = preg_replace('/\s+/', ' ', trim($fullNameUser));

            return [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $fullNameUser
            ];
        })->toArray();

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('api/users');

        $response->assertJson([
            'data' => $users
        ]);
    }

    /** test */
    public function test_user_login(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 1);

        $data = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->post('api/users/login', $data);

        $response->assertJsonStructure([
            'data' => [
                'accessToken'
            ]
        ]);
    }

    /** test */
    public function test_user_logout(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $token = $user->createToken('general')->plainTextToken;
        $this->assertNotNull($token);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/users/logout');

        $response->assertStatus(204);

        Sanctum::actingAs($user);
        $tokenAfterLogout = $user->tokens->first();

        $this->assertNull($tokenAfterLogout);
    }
}
