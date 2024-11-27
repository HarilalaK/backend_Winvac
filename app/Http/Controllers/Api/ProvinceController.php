<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function index()
    {
        return response()->json(Province::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|unique:provinces,nom'
        ]);

        $province = Province::create($request->all());
        return response()->json($province, 201);
    }

    public function show(Province $province)
    {
        return response()->json($province->load('regions'));
    }

    public function update(Request $request, Province $province)
    {
        $request->validate([
            'nom' => 'required|unique:provinces,nom,' . $province->id
        ]);

        $province->update($request->all());
        return response()->json($province);
    }

    public function destroy(Province $province)
    {
        $province->delete();
        return response()->json(null, 204);
    }
} 