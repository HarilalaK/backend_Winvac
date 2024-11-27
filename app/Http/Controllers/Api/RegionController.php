<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        return response()->json(Region::with('province')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'province_id' => 'required|exists:provinces,id',
            'nom' => 'unique:regions,nom,NULL,id,province_id,' . $request->province_id
        ]);

        $region = Region::create($request->all());
        return response()->json($region->load('province'), 201);
    }

    public function show(Region $region)
    {
        return response()->json($region->load(['province', 'centres']));
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'nom' => 'sometimes|required',
            'province_id' => 'sometimes|required|exists:provinces,id',
            'nom' => 'unique:regions,nom,' . $region->id . ',id,province_id,' . ($request->province_id ?? $region->province_id)
        ]);

        $region->update($request->all());
        return response()->json($region->load('province'));
    }

    public function destroy(Region $region)
    {
        $region->delete();
        return response()->json(null, 204);
    }
} 