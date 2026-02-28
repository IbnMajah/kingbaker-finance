<?php

namespace App\Http\Controllers;

use App\Models\PaymentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentCategoryController extends Controller
{
    public function index(): Response
    {
        $query = PaymentCategory::query()->orderBy('label');

        if (Request::input('search')) {
            $search = Request::input('search');
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $categories = $query->withCount('chequePayments')->paginate(15)
            ->through(fn($category) => [
                'id' => $category->id,
                'value' => $category->value,
                'label' => $category->label,
                'usage_count' => $category->cheque_payments_count,
            ]);

        return Inertia::render('PaymentCategories/Index', [
            'categories' => $categories,
            'filters' => Request::only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PaymentCategories/Create');
    }

    public function store(): RedirectResponse
    {
        $validated = Request::validate([
            'value' => ['required', 'string', 'max:255', Rule::unique('payment_categories', 'value')],
            'label' => ['required', 'string', 'max:255'],
        ]);

        PaymentCategory::create($validated);

        return Redirect::route('payment-categories.index')->with('success', 'Payment category created successfully.');
    }

    public function edit(PaymentCategory $paymentCategory): Response
    {
        return Inertia::render('PaymentCategories/Edit', [
            'category' => [
                'id' => $paymentCategory->id,
                'value' => $paymentCategory->value,
                'label' => $paymentCategory->label,
                'usage_count' => $paymentCategory->chequePayments()->count(),
            ],
        ]);
    }

    public function update(PaymentCategory $paymentCategory): RedirectResponse
    {
        $validated = Request::validate([
            'value' => ['required', 'string', 'max:255', Rule::unique('payment_categories', 'value')->ignore($paymentCategory->id)],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $paymentCategory->update($validated);

        return Redirect::route('payment-categories.index')->with('success', 'Payment category updated successfully.');
    }

    public function destroy(PaymentCategory $paymentCategory): RedirectResponse
    {
        if ($paymentCategory->chequePayments()->exists()) {
            return Redirect::back()->with('error', 'Cannot delete this category. It is being used by existing payments.');
        }

        $paymentCategory->delete();

        return Redirect::back()->with('success', 'Payment category deleted successfully.');
    }
}
