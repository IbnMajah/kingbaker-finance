<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContactsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Contacts/Index', [
            'filters' => Request::all('search', 'trashed', 'customer_type'),
            'contacts' => Auth::user()->account->contacts()
                ->orderByName()
                ->filter(Request::only('search', 'trashed', 'customer_type'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn($contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'address' => $contact->address,
                    'customer_type' => $contact->customer_type,
                    'deleted_at' => $contact->deleted_at,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Contacts/Create', [
            'customerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'shop', 'label' => 'Shop'],
                ['value' => 'partner', 'label' => 'Partner'],
                ['value' => 'branch', 'label' => 'Branch'],
                ['value' => 'hotel', 'label' => 'Hotel'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    public function store(): RedirectResponse
    {
        Auth::user()->account->contacts()->create(
            Request::validate([
                'first_name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'customer_type' => ['nullable', 'in:individual,shop,partner,branch,hotel,other'],
            ])
        );

        return Redirect::route('contacts')->with('success', 'Contact created.');
    }

    public function edit(Contact $contact): Response
    {
        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'customer_type' => $contact->customer_type,
                'deleted_at' => $contact->deleted_at,
            ],
            'customerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'shop', 'label' => 'Shop'],
                ['value' => 'partner', 'label' => 'Partner'],
                ['value' => 'branch', 'label' => 'Branch'],
                ['value' => 'hotel', 'label' => 'Hotel'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    public function update(Contact $contact): RedirectResponse
    {
        $contact->update(
            Request::validate([
                'first_name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'customer_type' => ['nullable', 'in:individual,shop,partner,branch,hotel,other'],
            ])
        );

        return Redirect::back()->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return Redirect::back()->with('success', 'Contact deleted.');
    }

    public function restore(Contact $contact): RedirectResponse
    {
        $contact->restore();

        return Redirect::back()->with('success', 'Contact restored.');
    }
}
