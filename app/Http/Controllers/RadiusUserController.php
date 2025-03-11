<?php
namespace App\Http\Controllers;

use App\Models\RadiusUser;
use Illuminate\Http\Request;

class RadiusUserController extends Controller
{
    public function index()
    {
        return RadiusUser::with('nas')->get();
    }

    public function store(Request $request)
    {
        $radiusUser = RadiusUser::create($request->all());
        return response()->json($radiusUser, 201);
    }

    public function show($id)
    {
        return RadiusUser::with('nas')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $radiusUser = RadiusUser::findOrFail($id);
        $radiusUser->update($request->all());
        return response()->json($radiusUser, 200);
    }

    public function destroy($id)
    {
        RadiusUser::destroy($id);
        return response()->json(null, 204);
    }
}
