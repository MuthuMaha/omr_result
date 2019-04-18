<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class redisController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function showProfile()
    {
    	Redis::set('name', 'Taylor markallnters' );
        $user = Redis::get('user:profile:1');

        return $values = Redis::lrange('names', 1, 10);
    }
}
