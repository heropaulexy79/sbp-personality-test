<?php

namespace App\Http\Controllers;

use App\Models\PersonalityTrait;
use Illuminate\Http\Request;

class PersonalityTraitController extends Controller
{
    public function index()
    {
        // Fetch all traits from the database
        $traits = PersonalityTrait::all(['name', 'description']);

        return response()->json($traits);
    }
}