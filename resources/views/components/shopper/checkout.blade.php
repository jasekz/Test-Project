@if($shopper['status_id'] == 1)
    <form method="post" action="{{ route('store.location.checkout', ['storeUuid' => $location['store']['uuid'], 'locationUuid' => $location['uuid']]) }}">
        @csrf
        <input type="hidden" name="shopper_id" value="{{ $shopper['uuid'] }}" >
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase transition">Checkout</button>
    </form>
@else
    {{ $shopper['check_out'] }}
@endif
