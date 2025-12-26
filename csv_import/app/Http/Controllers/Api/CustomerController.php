<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResource
    {
        return CustomerResource::collection(Customer::with('users')->all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResource
    {
        $customer = Customer::factory(
            array_merge(['user_id' => $request->user()->id], $request->only([
                'full_name',
                'email',
                'house_address',
                'phone',
                'apartment',
            ]))
        )->create();

        return new CustomerResource($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return $customer->toResource();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): JsonResource
    {
        $customer->update($request->only([
            'full_name',
            'house_address',
            'phone',
            'apartment',
        ]));
        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json([
            'data' => [
                'message' => 'Customer was successfully deleted',
            ],
            'code' => 200,
            'status' => 'Successful'
        ]);
    }
}
