<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\DepartmentTranslation;

class DepartmentsController extends Controller
{
    /**
     * Display departments management page
     */
    public function index(): View
    {
        $stats = [
            'total_departments' => Department::count(),
            'active_departments' => Department::where('status', 'active')->count(),
            'inactive_departments' => Department::where('status', 'inactive')->count(),
            'languages_count' => DepartmentTranslation::distinct('locale')->count(),
        ];

        return view('Tickets::admin.departments.index', compact('stats'));
    }

    /**
     * Store a new department
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'translations' => 'array',
            'translations.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create department
            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Create translations if provided
            if ($request->has('translations') && is_array($request->translations)) {
                foreach ($request->translations as $locale => $translation) {
                    if (! empty($translation)) {
                        DepartmentTranslation::create([
                            'department_id' => $department->id,
                            'locale' => $locale,
                            'name' => $translation,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'department' => $department->load('translations'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating department: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing department
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($department->id),
            ],
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'translations' => 'array',
            'translations.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update department
            $department->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Update translations
            if ($request->has('translations') && is_array($request->translations)) {
                // Delete existing translations
                DepartmentTranslation::where('department_id', $department->id)->delete();

                // Create new translations
                foreach ($request->translations as $locale => $translation) {
                    if (! empty($translation)) {
                        DepartmentTranslation::create([
                            'department_id' => $department->id,
                            'locale' => $locale,
                            'name' => $translation,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'department' => $department->load('translations'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating department: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a department
     */
    public function destroy(Department $department): JsonResponse
    {
        try {
            // Check if department has tickets
            $ticketsCount = $department->tickets()->count();
            if ($ticketsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete department. It has {$ticketsCount} tickets assigned to it.",
                ], 422);
            }

            DB::beginTransaction();

            // Delete translations first
            DepartmentTranslation::where('department_id', $department->id)->delete();

            // Delete the department
            $department->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error deleting department: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle bulk actions on departments
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'departments' => 'required|array|min:1',
            'departments.*' => 'integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $departmentIds = $request->departments;
            $action = $request->action;
            $affectedCount = count($departmentIds);

            switch ($action) {
                case 'activate':
                    Department::whereIn('id', $departmentIds)->update(['status' => 'active']);
                    $message = "{$affectedCount} department(s) activated successfully";
                    break;

                case 'deactivate':
                    Department::whereIn('id', $departmentIds)->update(['status' => 'inactive']);
                    $message = "{$affectedCount} department(s) deactivated successfully";
                    break;

                case 'delete':
                    // Check if any departments have tickets
                    $departmentsWithTickets = Department::whereIn('id', $departmentIds)
                        ->withCount('tickets')
                        ->having('tickets_count', '>', 0)
                        ->count();

                    if ($departmentsWithTickets > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Some selected departments have tickets assigned to them and cannot be deleted.',
                        ], 422);
                    }

                    // Delete translations first
                    DepartmentTranslation::whereIn('department_id', $departmentIds)->delete();

                    // Delete departments
                    Department::whereIn('id', $departmentIds)->delete();
                    $message = "{$affectedCount} department(s) deleted successfully";
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export departments data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $departments = Department::with('translations')->get();

        $filename = 'departments_'.now()->format('Y-m-d_H-i-s');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function () use ($departments) {
                $file = fopen('php://output', 'w');

                // CSV Headers
                fputcsv($file, ['ID', 'Name', 'Description', 'Status', 'Tickets Count', 'Translations', 'Created At']);

                foreach ($departments as $department) {
                    $translations = $department->translations->pluck('name', 'locale')->toArray();
                    $translationsString = json_encode($translations);

                    fputcsv($file, [
                        $department->id,
                        $department->name,
                        $department->description,
                        $department->status,
                        $department->tickets()->count(),
                        $translationsString,
                        $department->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Default to JSON export
        return response()->json([
            'departments' => $departments,
            'exported_at' => now()->toISOString(),
            'total_count' => $departments->count(),
        ]);
    }

    /**
     * Get translation statistics
     */
    public function translationStats(): JsonResponse
    {
        try {
            $availableLocales = [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'ar' => 'Arabic',
                // Add more locales as needed
            ];

            $totalDepartments = Department::count();
            $stats = [];

            foreach ($availableLocales as $locale => $language) {
                $translatedCount = DepartmentTranslation::where('locale', $locale)->count();
                $completionRate = $totalDepartments > 0 ? round(($translatedCount / $totalDepartments) * 100, 2) : 0;
                $missingCount = max(0, $totalDepartments - $translatedCount);

                $stats[] = [
                    'locale' => $locale,
                    'language' => $language,
                    'translated_count' => $translatedCount,
                    'completion_rate' => $completionRate,
                    'missing_count' => $missingCount,
                ];
            }

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching translation statistics: '.$e->getMessage(),
            ], 500);
        }
    }
}
