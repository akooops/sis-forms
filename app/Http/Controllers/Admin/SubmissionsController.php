<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Http\Response;

class SubmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $this->indexService->limitPerPage($request->query('perPage', 10));
        $page = $this->indexService->checkPageIfNull($request->query('page', 1));
        $search = $this->indexService->checkIfSearchEmpty($request->query('search'));

        $submissions = Submission::latest();

        if ($search) {
            $submissions->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('firstname', 'like', '%' . $search . '%')
                      ->orWhere('lastname', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('grade', 'like', '%' . $search . '%');
            });
        }

        $submissions = $submissions->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'submissions' => $submissions->items(),
                'pagination' => $this->indexService->handlePagination($submissions)
            ]);
        }

        return inertia('Admin/Submissions/Index');
    }

    /**
     * Export submissions to CSV
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $search = $this->indexService->checkIfSearchEmpty($request->query('search'));

        $submissions = Submission::latest();

        if ($search) {
            $submissions->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('firstname', 'like', '%' . $search . '%')
                      ->orWhere('lastname', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%')
                      ->orWhere('grade', 'like', '%' . $search . '%');
            });
        }

        $submissions = $submissions->get();

        $filename = 'submissions_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Grade',
                'Submitted At'
            ]);

            // Add data rows
            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->firstname,
                    $submission->lastname,
                    $submission->email,
                    $submission->phone,
                    $submission->grade,
                    $submission->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Submission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.submissions.index')
                        ->with('success','Submission deleted successfully');
    }
}
