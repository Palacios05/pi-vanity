<?php

namespace App\Http\Controllers\Config;

use Illuminate\Http\Request;
use App\Models\Config\Sucursale;
use App\Http\Controllers\Controller;

class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        //
        $sucursales = Sucursale::where("name","ilike","%".$search."%")->orderBy("id","desc")->get();

        return response()->json([
            "sucursales" => $sucursales->map(function($sucursal) {
                return [
                    "id" => $sucursal->id,
                    "name" => $sucursal->name,
                    "address" => $sucursal->address,
                    "state" => $sucursal->state,
                    "created_at" => $sucursal->created_at->format("Y/m/d h:i:s"),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $exist_sucursal = Sucursale::where("name",$request->name)->first();

        if($exist_sucursal){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DE LA SUCURSAL YA EXISTE, INTENTE UNO NUEVO"
            ]);
        }
        // $request->all() -> name, address y state
        $sucursal = Sucursale::create($request->all());
        
        return response()->json([
            "message" => 200,
            "sucursal" => [
                "id" => $sucursal->id,
                "name" => $sucursal->name,
                "address" => $sucursal->address,
                "state" => $sucursal->state,
                "created_at" => $sucursal->created_at->format("Y/m/d h:i:s"),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $exist_sucursal = Sucursale::where("name",$request->name)->where("id","<>",$id)->first();

        if($exist_sucursal){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DE LA SUCURSAL YA EXISTE, INTENTE UNO NUEVO"
            ]);
        }
        // $request->all() -> name, address y state
        $sucursal = Sucursale::findOrFail($id);
        $sucursal->update($request->all());

        return response()->json([
            "message" => 200,
            "sucursal" => [
                "id" => $sucursal->id,
                "name" => $sucursal->name,
                "address" => $sucursal->address,
                "state" => $sucursal->state,
                "created_at" => $sucursal->created_at->format("Y/m/d h:i:s"),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sucursal = Sucursale::findOrFail($id);
        $sucursal->delete();
        return response()->json([
            "message" => 200,
        ]);
    }
}
