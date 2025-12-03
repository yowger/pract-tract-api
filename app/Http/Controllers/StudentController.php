<?php

namespace App\Http\Controllers;

use App\Filters\StudentFilter;
use App\Http\Resources\StudentListResource;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        Log::info('Incoming student request:', $request->all());

        $query = Student::with([
            'user',
            'program',
            'section',
            'advisor.user',
            'company',
            'documents',
            'evaluationAnswers',
            'attendances',
        ]);

        $perPage = $request->input('per_page', 10);
        $students = (new StudentFilter($query, $request))->apply()->paginate($perPage);

        return StudentListResource::collection($students);
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'program',
            'section',
            'company.schedule',
            'advisor.user',
            'documents',
            'evaluationAnswers.evaluation',
            'evaluationAnswers.submitter',
            'attendances',
        ]);

        return new StudentListResource($student);
    }

    public function bulkUpdateCompany(Request $request)
    {
        $data = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'ojt_start_date' => 'nullable|date',
            'ojt_end_date' => 'nullable|date|after_or_equal:ojt_start_date',
        ]);

        $students = Student::whereIn('user_id', $data['user_ids'])->get();

        Student::whereIn('user_id', $data['user_ids'])->update([
            'company_id' => $data['company_id'],
            'ojt_start_date' => $data['ojt_start_date'] ?? null,
            'ojt_end_date' => $data['ojt_end_date'] ?? null,
        ]);

        foreach ($students as $student) {
            $student->attendances()->delete();
        }

        return response()->noContent();
    }


    public function bulkUpdateAdvisor(Request $request)
    {
        $data = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'advisor_id' => 'required|exists:advisors,id',
        ]);

        Student::whereIn('user_id', $data['user_ids'])
            ->update(['advisor_id' => $data['advisor_id']]);

        return response()->noContent();
    }
}
