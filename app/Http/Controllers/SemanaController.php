<?php

namespace App\Http\Controllers;

use App\Models\Semana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\SemanaRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;



class SemanaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request): View
    {
        $semanas = Semana::paginate();

        return view('semana.index', compact('semanas'))
            ->with('i', ($request->input('page', 1) - 1) * $semanas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $semana = new Semana();

        return view('semana.create', compact('semana'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SemanaRequest $request): RedirectResponse
    {
        Semana::create($request->validated());

        return Redirect::route('semanas.index')
            ->with('success', 'Semana created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $semana = Semana::find($id);

        return view('semana.show', compact('semana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $semana = Semana::find($id);

        return view('semana.edit', compact('semana'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SemanaRequest $request, Semana $semana): RedirectResponse
    {
        $semana->update($request->validated());

        return Redirect::route('semanas.index')
            ->with('success', 'Semana updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Semana::find($id)->delete();

        return Redirect::route('semanas.index')
            ->with('success', 'Semana deleted successfully');
    }

    public function filter(Request $request): View
    {
        $week = $request->input('week'); // Obtener el parámetro de semana de la solicitud

        if ($week) {
            $semanas = Semana::where('week', $week)->paginate();
        } else {
            $semanas = Semana::paginate();
        }

        return view('semana.index', compact('semanas'))
            ->with('i', ($request->input('page', 1) - 1) * $semanas->perPage());
    }
}
