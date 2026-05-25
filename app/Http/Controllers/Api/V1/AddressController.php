<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\Address\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function __construct(
        private AddressService $addressService
    ) {}

    /**
     * Get all addresses for the authenticated user
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $this->addressService->getUserAddresses($request->user());

        return AddressResource::collection($addresses);
    }

    /**
     * Create a new address
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Address created successfully',
            'data' => new AddressResource($address),
        ], 201);
    }

    /**
     * Show a specific address
     */
    public function show(Address $address): AddressResource
    {
        $this->authorize('view', $address);

        return new AddressResource($address);
    }

    /**
     * Update an address
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $updated = $this->addressService->update($address, $request->validated());

        return response()->json([
            'message' => 'Address updated successfully',
            'data' => new AddressResource($updated),
        ]);
    }

    /**
     * Delete an address
     */
    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $this->addressService->delete($address);

        return response()->json([
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Set address as default
     */
    public function setDefault(Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $updated = $this->addressService->setAsDefault($address);

        return response()->json([
            'message' => 'Default address updated',
            'data' => new AddressResource($updated),
        ]);
    }
}
