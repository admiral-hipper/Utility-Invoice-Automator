<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(): JsonResource
    {
        $customers = Auth::user()->isAdmin() ?
            Customer::query()->with('user')->get()
            : Customer::query()->whereIn('id', Auth::user()->customers->pluck('id'))
            ->with('user')->get();
        return CustomerResource::collection(
            $customers
        );
    }

    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'full_name'      => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:customers,email'],
            'house_address'  => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'apartment'      => ['nullable', 'string', 'max:50'],
        ]);

        $customer = Customer::query()->create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return new CustomerResource($customer);
    }

    public function show(Customer $customer): JsonResource
    {
        return new CustomerResource($customer->load('user'));
    }

    public function update(Request $request, Customer $customer): JsonResource
    {
        $customer->update($request->validate([
            'full_name'      => ['sometimes', 'required', 'string', 'max:255'],
            'email'          => ['sometimes', 'required', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'house_address'  => ['sometimes', 'required', 'string', 'max:255'],
            'phone'          => ['sometimes', 'nullable', 'string', 'max:50'],
            'apartment'      => ['sometimes', 'nullable', 'string', 'max:50'],
        ]));

        return new CustomerResource($customer->fresh()->load('user'));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'data' => ['message' => 'Customer was successfully deleted'],
            'code' => 200,
            'status' => 'Successful',
        ]);
    }
}
