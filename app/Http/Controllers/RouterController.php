<?php

namespace App\Http\Controllers;

use App\Models\Router;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function index()
    {
        return Router::with('nas')->get();
    }

    public function store(Request $request)
    {
        $router = Router::create($request->all());
        return response()->json($router, 201);
    }

    public function show($id)
    {
        return Router::with('nas')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $router = Router::findOrFail($id);
        $router->update($request->all());
        return response()->json($router, 200);
    }

    public function destroy($id)
    {
        Router::destroy($id);
        return response()->json(null, 204);
    }
}
