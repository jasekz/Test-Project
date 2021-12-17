<?php

namespace App\Http\Controllers\Shopper;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shopper\ShopperQueueCreateRequest;
use App\Services\Shopper\ShopperService;
use App\Services\Shopper\StatusService;
use App\Services\Store\Location\LocationService;
use Mockery\Exception;

class ShopperQueueController extends Controller
{
    /**
     * @var ShopperService
     */
    protected $shopper;

    protected $location;

    protected $status;

    /**
     * ShopperSController constructor.
     * @param ShopperService $shopper
     */
    public function __construct(ShopperService $shopper, LocationService $location, StatusService $status)
    {
        $this->shopper = $shopper;
        $this->location = $location;
        $this->status = $status;
    }


    /**
     * @param ShopperQueueCreateRequest $request
     * @param string $locationUuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ShopperQueueCreateRequest $request, string $locationUuid): \Illuminate\Http\RedirectResponse
    {
        try {
            /*
             * Although it would have been a bit easier to use a Location model, I wanted to follow
             * the existing coding standards and use the repository pattern.
             */
            $location = $this->location->show(
                [
                    'uuid' => $locationUuid,
                ],
                [
                    'Shoppers',
                    'Shoppers.Status'
                ]
            );

            // If < X shoppers are currently actively shopping, the shopper should automatically become active upon check-in.
            // This logic is as per the spec, however, it doesn't take into consideration "pending" shoppers and will "cut" in front of them
            if( $location['shopper_limit'] > collect($location['shoppers'])->where('status.name', 'Active')->count()) {
                $status = $this->status->show(['name' => 'Active']);
            }

            // If > X people are currently shopping, they should enter the shopping queue as a "pending" shopper.
            else {
                $status = $this->status->show(['name' => 'Pending']);
            }

            $this->shopper->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'location_id' => $location['id'],
                'status_id' => $status['id'],
                'check_in' => date('Y-m-d G:i:s'),
            ]);

            return redirect()->route('public.location', ['location' => $locationUuid])->with('status', 'You are now in the queue with the status ' . $status['name']);
        }

        catch (Exception $e) {
            return redirect()->route('public.location', ['location' => $locationUuid])->with('status', 'Ooops.  ' . $e->getMessage());
        }
    }
}
