<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StudentFilter
{
    protected Builder $query;
    protected Request $request;

    public function __construct(Builder $query, Request $request)
    {
        $this->query = $query;
        $this->request = $request;
    }

    public function apply(): Builder
    {
        $this->filterStudent();
        $this->filterAdvisor();
        $this->filterCompany();
        $this->filterProgram();
        $this->filterSection();
        $this->filterStatus();
        $this->applySorting();

        return $this->query;
    }

    protected function filterStudent(): void
    {
        if ($studentSearch = $this->request->input('student')) {
            $this->query->where(function ($q) use ($studentSearch) {
                $q->where('student_id', 'like', "%{$studentSearch}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$studentSearch}%"));
            });
        }
    }

    protected function filterAdvisor(): void
    {
        $advisorFilter = $this->request->input('advisor');

        if ($advisorFilter === 'null') {
            $this->query->whereDoesntHave('advisor');
            return;
        }

        if ($advisorFilter) {
            $this->query->whereHas(
                'advisor.user',
                fn($a) =>
                $a->where('name', 'like', "%{$advisorFilter}%")
            );
        }
    }

    protected function filterCompany(): void
    {
        $companyFilter = $this->request->input('company');

        if ($companyFilter === 'null') {
            $this->query->whereDoesntHave('company');
            return;
        }

        if ($companyFilter) {
            $this->query->whereHas(
                'company',
                fn($c) =>
                $c->where('name', 'like', "%{$companyFilter}%")
            );
        }
    }

    protected function filterProgram(): void
    {
        if ($programId = $this->request->input('program_id')) {
            $this->query->where('program_id', $programId);
        }
    }

    protected function filterSection(): void
    {
        if ($sectionId = $this->request->input('section_id')) {
            $this->query->where('section_id', $sectionId);
        }
    }

    protected function filterStatus(): void
    {
        if ($status = $this->request->input('status')) {
            $this->query->whereHas('user', fn($q) => $q->where('status', $status));
        }
    }

    protected function applySorting(): void
    {
        $sortBy = $this->request->input('sort_by', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        $this->query->orderBy($sortBy, $sortOrder);
    }
}
