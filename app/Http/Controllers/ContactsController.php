<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\SaleCreditItem;
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

    public function show(Contact $contact): Response
    {
        // Find invoices for this customer by matching name, email, or phone
        $invoices = Invoice::where(function ($query) use ($contact) {
            $query->where('customer_name', $contact->name)
                ->orWhere(function ($q) use ($contact) {
                    if ($contact->email) {
                        $q->where('customer_email', $contact->email);
                    }
                })
                ->orWhere(function ($q) use ($contact) {
                    if ($contact->phone) {
                        $q->where('customer_phone', $contact->phone);
                    }
                });
        })
            ->with(['bankAccount', 'branch', 'items', 'payments'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Find sales credit items for this customer
        $salesCreditItems = SaleCreditItem::where('customer_id', $contact->id)
            ->with(['sale.branch', 'sale.shift'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total credits (invoices + sales credit items)
        $totalInvoiceAmount = $invoices->sum('amount');
        $totalInvoicePaid = $invoices->sum('amount_paid');
        $totalSalesCredit = $salesCreditItems->sum('total');
        $totalCredits = $totalInvoiceAmount + $totalSalesCredit;
        $totalPaid = $totalInvoicePaid;
        $totalOutstanding = $totalCredits - $totalPaid;

        return Inertia::render('Contacts/Show', [
            'contact' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'customer_type' => $contact->customer_type,
                'deleted_at' => $contact->deleted_at,
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at,
            ],
            'invoices' => $invoices->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'amount' => $invoice->amount,
                'amount_paid' => $invoice->amount_paid,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
                'invoice_type' => $invoice->invoice_type,
                'description' => $invoice->description,
                'bank_account' => $invoice->bankAccount ? [
                    'id' => $invoice->bankAccount->id,
                    'name' => $invoice->bankAccount->name,
                ] : null,
                'branch' => $invoice->branch ? [
                    'id' => $invoice->branch->id,
                    'name' => $invoice->branch->name,
                ] : null,
                'items_count' => $invoice->items->count(),
                'payments_count' => $invoice->payments->count(),
                'created_at' => $invoice->created_at,
            ]),
            'sales_credit_items' => $salesCreditItems->map(fn($item) => [
                'id' => $item->id,
                'sale_id' => $item->sale_id,
                'description' => $item->description,
                'unit_price' => $item->unit_price,
                'unit_measurement' => $item->unit_measurement,
                'quantity' => $item->quantity,
                'total' => $item->total,
                'sale' => $item->sale ? [
                    'id' => $item->sale->id,
                    'sales_date' => $item->sale->sales_date->format('Y-m-d'),
                    'branch' => $item->sale->branch ? [
                        'id' => $item->sale->branch->id,
                        'name' => $item->sale->branch->name,
                    ] : null,
                    'shift' => $item->sale->shift ? [
                        'id' => $item->sale->shift->id,
                        'name' => $item->sale->shift->name,
                    ] : null,
                ] : null,
                'created_at' => $item->created_at,
            ]),
            'credit_summary' => [
                'total_credits' => $totalCredits,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'invoices_count' => $invoices->count(),
                'sales_credit_items_count' => $salesCreditItems->count(),
            ],
        ]);
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
