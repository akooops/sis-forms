<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submissions\StoreSubmissionRequest;
use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\Route;

class SubmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return inertia('Submissions/Index');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSubmissionRequest $request)
    {
        $submission = Submission::create($request->validated());

        return back()->with('success', 'Submission created successfully!');
    }
}
