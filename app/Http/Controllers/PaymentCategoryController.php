<?php

namespace App\Http\Controllers;

use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $items = PaymentCategory::ordered()->get();

        return view('admin.payment_categories.index', compact('items'));
    }

    public function create()
    {
        $paymentCategory = new PaymentCategory;

        return view('admin.payment_categories.create', compact('paymentCategory'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:payment_categories,slug',
            'sort_order' => 'nullable|integer|min:1',
            'is_domains_hosting' => 'sometimes|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (PaymentCategory::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $status = PaymentCategory::create($data);

        if (! empty($data['sort_order'])) {
            $status->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('payment-categories.index')->with('success', 'Категория платежа создана.');
    }

    public function show(PaymentCategory $paymentCategory)
    {
        return view('admin.payment_categories.show', compact('paymentCategory'));
    }

    public function edit(PaymentCategory $paymentCategory)
    {
        return view('admin.payment_categories.edit', compact('paymentCategory'));
    }

    public function update(Request $request, PaymentCategory $paymentCategory)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:payment_categories,slug,'.$paymentCategory->id,
            'sort_order' => 'nullable|integer|min:1',
            'is_domains_hosting' => 'sometimes|boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title'] ?? $paymentCategory->title);
        }

        $paymentCategory->update($data);

        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $paymentCategory->sort_order) {
            $paymentCategory->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('payment-categories.index')->with('success', 'Категория обновлена.');
    }

    public function destroy(PaymentCategory $paymentCategory)
    {
        PaymentCategory::where('sort_order', '>', $paymentCategory->sort_order)->decrement('sort_order');
        $paymentCategory->delete();

        return redirect()->route('payment-categories.index')->with('success', 'Категория удалена.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:payment_categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            PaymentCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
