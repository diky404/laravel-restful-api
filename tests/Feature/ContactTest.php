<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateContact()
    {
        $this->seed([UserSeeder::class]);
        
        $this->post('/api/contacts', 
            [
                'first_name' => 'omi',
                'last_name' => 'omi',
                'email' => 'omi@mail.com',
                'phone' => '08888'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201);
    }

    public function testCreateContactFailed()
    {
        $this->seed([UserSeeder::class]);
        
        $this->post('/api/contacts', 
            [
                'first_name' => 'omi',
                'last_name' => 'omi',
                'email' => 'omi',
                'phone' => '08888'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400);
    }

    public function testCreateContactUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        
        $this->post('/api/contacts', 
            [
                'first_name' => 'omi',
                'last_name' => 'omi',
                'email' => 'omi',
                'phone' => '08888'
            ],
            [
                'Authorization' => 'tests'
            ]
        )->assertStatus(401);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/'.$contact->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'first_name' => 'test',
                'last_name' => 'test'
            ]
          ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        // $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/1', [
            'Authorization' => 'test'
        ])->assertStatus(404)
          ->assertJson([
            'errors' => [
                'message' => [
                    'Not found'
                ]
            ]
          ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/'.$contact->id, [
            'first_name' => 'updated',
            'last_name' => 'updated'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'first_name' => 'updated',
                'last_name' => 'updated'
            ]
          ]);
    }

    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/'.$contact->id, [
            'first_name' => '',
            'last_name' => 'updated'
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete(uri:'/api/contacts/'.$contact->id, headers:[
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => true
          ]);
    }
}
