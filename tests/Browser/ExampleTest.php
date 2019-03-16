<?php

namespace Tests\Browser;
// use Cache;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        //  Cache::shouldReceive('get')
        //             ->once()
        //             ->with('key')
        //             ->andReturn('value');

        // $response = $this->get('/users');
        //  $this->browse(function (Browser $browser) {
        //     $browser->visit('http://127.0.0.1:8000/register')
        //             ->type('name', '1234y56')
        //      ->type('email', 'ganaraj123456@gmail.com')
        //             ->type('password', '1234y56')
        //             ->type('password_confirmation', '1234y56')
        //             // ->press('Register')

        //             ->assertSee('Register');
        // });
           $response = $this->json('GET', 'http://103.206.115.37/omr_new/public/api/sendmessage?exam_id=30&notify_type=1&api_key=2y10CcFVl6k3gFHaKDzW1TH4TDJ0uM15hwnlIb0/fDUkRviOO4McnT');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => "",                
            ]);

    }
}
