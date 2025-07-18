<?php

namespace App\Http\Controllers;

use App\Models\ExpenseItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ExpenseItemController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseItem $expenseItem): RedirectResponse
    {
        $expenseClaimId = $expenseItem->expense_claim_id;
        $expenseClaim = $expenseItem->expenseClaim;

        // Check if expense claim is in draft status
        if ($expenseClaim->status !== 'draft') {
            return Redirect::back()->with('error', 'Can only remove items from draft expense claims.');
        }

        // Delete receipt image if exists
        if ($expenseItem->receipt_image_path) {
            Storage::disk('public')->delete($expenseItem->receipt_image_path);
        }

        $expenseItem->delete();

        return Redirect::route('expense-claims.edit', $expenseClaimId)
            ->with('success', 'Expense item deleted successfully.');
    }
}