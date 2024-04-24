<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Http\Responses\CustomerIndexResponse;
use App\Http\Responses\CustomerRecordsResponse;
use App\Http\Responses\GetCustomerWhoBorrowsResponse;
use App\Models\Address;
use App\Models\Customer;

class CustomerController extends Controller
{
    // when searching customer but only when in resort
    public function index()
    {
        $search = request()->query('search');
        $inResort = request()->query('inResort') ?? false;
        $customers = Customer::with('reservation')
            ->inResort($inResort)
            ->search($search)
            ->paginate(5);
        return new CustomerIndexResponse($customers);
    }

    // for record management system.
    public function customerRecords()
    {
        $search = request()->query('search');
        $year = request()->query('year');
        $month = request()->query('month');

        $id = request()->query('id');
        $firstName = request()->query('firstName');

        // Retrieve customers with reservations where status is 'Depart'
        $customers = Customer::with('reservation')
            ->filterByYear($year)
            ->filterByMonth($month)
            ->orderById($id)
            ->orderByFirstName($firstName)
            ->searchRecords($search)
            ->paginate(9);

        return new CustomerRecordsResponse($customers);
    }

    public function addCustomer(AddCustomerRequest $request)
    {
        try {
            $customerData = $request->validated();

            // // Check if the email already exists
            // $existingCustomer = Customer::where('email', $customerData['email'])->first();
            // if ($existingCustomer) {
            //     return ['success' => true, 'message' => 'Customer already exists.', 'customer_id' => $existingCustomer->id];
            // }

            // If the email doesn't exist, proceed to create the customer
            $addressData = [
                'province' => $customerData['province'],
                'city' => $customerData['city'],
                'barangay' => $customerData['barangay'],
            ];
            unset($customerData['province'], $customerData['city'], $customerData['barangay']);

            $customer = Customer::create($customerData);
            $address = new Address($addressData);
            $customer->address()->save($address);

            return response()->json([
                'success' => true,
                'message' => 'Successfully created.',
                'customer_id' => $customer->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create customer'
            ]);
        }
    }

    public function getCustomerWhoBorrows($id)
    {
        try {
            $customer = Customer::with('customersWhoBorrows')->find($id);
            $borrowedItems = $customer->customersWhoBorrows;
            return new GetCustomerWhoBorrowsResponse($borrowedItems);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get borrowed items'
            ]);
        }
    }

    public function getCustomer()
    {
        try {
            $email = request()->query('email');
            $customer = Customer::where('email', $email)->first();
            return response()->json([
                'success' => true,
                'message' => 'Successfully get the customer',
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get the customer'
            ]);
        }
    }
}
