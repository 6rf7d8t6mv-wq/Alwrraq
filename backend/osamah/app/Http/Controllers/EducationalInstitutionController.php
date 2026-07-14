<?php

namespace App\Http\Controllers;

use App\Models\EducationalInstitution;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EducationalInstitutionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'education_stage' => ['nullable', Rule::in(['kindergarten', 'primary', 'intermediate', 'secondary', 'higher_education', 'training'])],
            'institution_type' => ['nullable', Rule::in(['school', 'university', 'college', 'institute'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = EducationalInstitution::query()
            ->where('is_active', true);

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        foreach (['region', 'city', 'education_stage', 'institution_type'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $institutions = $query
            ->orderBy('name_ar')
            ->paginate((int) ($filters['per_page'] ?? 20));

        return response()->json([
            'data' => $institutions->getCollection()->map(fn (EducationalInstitution $institution) => [
                'id' => $institution->id,
                'name_ar' => $institution->name_ar,
                'name_en' => $institution->name_en,
                'institution_type' => $institution->institution_type,
                'education_stage' => $institution->education_stage,
                'ownership_type' => $institution->ownership_type,
                'region' => $institution->region,
                'city' => $institution->city,
                'source' => $institution->source,
                'last_verified_at' => $institution->last_verified_at?->toDateString(),
            ]),
            'meta' => [
                'current_page' => $institutions->currentPage(),
                'last_page' => $institutions->lastPage(),
                'per_page' => $institutions->perPage(),
                'total' => $institutions->total(),
            ],
        ]);
    }
}
