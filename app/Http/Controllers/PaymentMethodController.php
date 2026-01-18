<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $methods = PaymentMethod::ordered()->paginate(25);

        return view('admin.payment_methods.index', compact('methods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payment_methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:payment_methods,slug',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (PaymentMethod::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        PaymentMethod::create($data);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Способ оплаты создан.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment_methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:payment_methods,slug,'.$paymentMethod->id,
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['title'] ?? $paymentMethod->title);
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (PaymentMethod::where('slug', $slug)->where('id', '<>', $paymentMethod->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $paymentMethod->update($data);

        // Если передан sort_order и он поменялся — используем модельный метод moveToPosition
        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $paymentMethod->sort_order) {
            $paymentMethod->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('payment-methods.index')
            ->with('success', 'Способ оплаты обновлён.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Сдвигаем позиции ниже удаляемой вверх на 1
        PaymentMethod::where('sort_order', '>', $paymentMethod->sort_order)->decrement('sort_order');

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Способ оплаты удалён.');
    }

    /**
     * Переместить элемент на указанную позицию (через отдельный маршрут).
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:payment_methods,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            PaymentMethod::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
