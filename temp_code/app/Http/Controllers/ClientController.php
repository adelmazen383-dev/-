<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreClientRequest;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Customer::latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        Customer::create($request->validated());
        return redirect()->route('clients.index')->with('success', 'تم إضافة العميل بنجاح.');
    }

    public function edit(Customer $client)
    {
        return view('clients.create', compact('client'));
    }

    public function update(StoreClientRequest $request, Customer $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.index')->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    public function destroy(Customer $client)
    {
        // Add check if client has contracts later if needed
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'تم حذف العميل بنجاح.');
    }
}
