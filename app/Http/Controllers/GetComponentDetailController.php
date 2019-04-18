<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;

class GetComponentDetailController extends Controller
{
    /**
     * Get Component Name and Component Description
     *
     * @param Request $request
     * @return Response|string
     */
    /**
* @SWG\Get(
*     path="/getDetails",
*     description="Return a Component name and description",
*     @SWG\Parameter(
*         name="ComponentName",
*         in="query",
*         type="string",
*         description="Component name",
*         required=true,
*     ),
*     @SWG\Parameter(
*         name="ComponentDescription",
*         in="query",
*         type="string",
*         description="Component Description",
*         required=true,
*     ),
*     @SWG\Response(
*         response=200,
*         description="OK",
*     ),
*     @SWG\Response(
*         response=404,
*         description="Please provide both data"
*     )
* )
*/
    public function getData(Request $request)
    {
        $userData = $request->only([
            'ComponentName',
            'ComponentDescription',
        ]);

        if (empty($userData['ComponentName']) && empty($userData['ComponentDescription'])) {
            return new \Exception('Missing data', 404);
        }
        return $userData['ComponentName'] . ' ' . $userData['ComponentDescription'];
    }
}