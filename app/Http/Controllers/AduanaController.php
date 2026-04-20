<?php

namespace App\Http\Controllers;

use App\Models\Aduana;
use Illuminate\Http\Request;

class AduanaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $allowedRoles = ['admin'];

            if (!auth()->check()) {
                return redirect()->route('login');
            }

            $user = auth()->user();

            if (!in_array($user->role, $allowedRoles)) {
                $route = config("dashboards.role_routes.{$user->role}", 'home');
                return redirect()->route($route)
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }

            return $next($request);
        });
    }
    public function index()
    {
        //
        $aduanas =Aduana::latest()->paginate(10);
        return view("aduanas.index", compact("aduanas"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("aduanas.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated= $request->validate([
            'nombre'=>'required|max:255',
            'clave'=> 'required|unique:aduanas|max:10'
            ]);
            Aduana::create($validated);
            return redirect()->route('aduanas.index')
                ->with('success','Aduana Registrada Exitosamente!');

    }

    /**
     * Display the specified resource.
     */
    public function show(Aduana $aduana)
    {
        //
        return view('aduanas.show', compact('aduana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aduana $aduana)
    {
        return view('aduanas.edit', compact('aduana'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $aduana = Aduana::findOrFail($id);
        $validated = $request->validate([
        'nombre' => 'required|max:255',
        'clave' => 'required|max:10|unique:aduanas,clave,'.$id
    ]);

        $aduana->update($validated);

        return redirect()->route('aduanas.index')
            ->with('success', 'Aduana actualizada!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aduana $aduana)
    {
        //
        $aduana->delete();
        return redirect()->route('aduanas.index')
            ->with('success', 'Aduana eliminada!');
    }
}
