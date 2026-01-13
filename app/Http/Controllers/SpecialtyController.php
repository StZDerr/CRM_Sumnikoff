<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Список
    public function index()
    {
        $specialties = Specialty::orderBy('name')->paginate(25);

        return view('admin.specialty.index', compact('specialties'));
    }

    // Форма создания
    public function create()
    {
        return view('admin.specialty.create', ['specialty' => new Specialty]);
    }

    // Сохранение
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'salary' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        Specialty::create($data);

        return redirect()->route('specialties.index')->with('success', 'Специальность создана.');
    }

    // Форма редактирования
    public function edit(Specialty $specialty)
    {
        return view('admin.specialty.edit', compact('specialty'));
    }

    // Обновление
    public function update(Request $request, Specialty $specialty)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'salary' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        $specialty->update($data);

        return redirect()->route('specialties.index')->with('success', 'Специальность обновлена.');
    }

    // Удаление (простое удаление; можно заменить на soft-delete по необходимости)
    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return redirect()->route('specialties.index')->with('success', 'Специальность удалена.');
    }
}
