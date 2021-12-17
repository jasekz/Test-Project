<?php

namespace App\Listeners;

use App\Events\ShopperCheckedout;
use App\Services\Shopper\ShopperService;
use App\Services\Shopper\StatusService;
use App\Services\Store\Location\LocationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateShopperQueue
{
    protected $location;

    protected $shopper;

    protected $status;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(LocationService $location, ShopperService $shopper, StatusService $status)
    {
        $this->location = $location;
        $this->shopper = $shopper;
        $this->status = $status;
    }

    /**
     * Handle the event.
     *
     * @param  ShopperCheckedout  $event
     * @return void
     */
    public function handle(ShopperCheckedout $event)
    {
        $location = $this->location->show(
            [
                'id' => $event->getData()['location_id'],
            ],
            [
                'Store',
                'Shoppers',
                'Shoppers.Status'
            ]
        );

        \Log::info('Event::shopper checked out. loc id: ' . $location['id'] . ', shopper id: ' . $event->getData()['id']);

        $activeShoppers = (int) collect($location['shoppers'])->where('status.name', 'Active')->count();
        $openSlots = $location['shopper_limit'] - $activeShoppers;

        if($openSlots > 0) {

            $activeStatusId = $this->status->show(['name' => 'Active'])['id'];

            $pendingShoppers = collect($location['shoppers'])->where('status.name', 'Pending')->sortBy('check_in')->take($openSlots);



            if($pendingShoppers) {

                foreach($pendingShoppers as $pendingShopper) {

                    // let in as many pending shoppers as possible
                    $this->shopper->update(
                        $pendingShopper['id'],
                        [
                            'status_id' => $activeStatusId,
                        ]
                    );

                    \Log::info('Event::setting to active automatically. loc id: ' . $location['id'] . ', shopper id: ' . $pendingShopper['id']);

                    // we'll set only the next shopper to active as per specs;
                    // to set as many pending shoppers as possible to active, take out the break
                    break;
                }
            }
        }
    }
}
