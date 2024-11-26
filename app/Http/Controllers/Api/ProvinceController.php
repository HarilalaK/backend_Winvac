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
        return response()->json($province);
    }
} 