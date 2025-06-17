<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Api\Bundle;
use App\Models\Api\Webinar;
use Illuminate\Http\Request;

class EmployeeProgressController extends Controller
{
    public function index()
    {
        $user = apiAuth();

        $bundles = Bundle::with('bundleStudents.student.user') // eager load users
            ->where('teacher_id', $user->id)
            ->get()
            ->map(function ($bundle) {
                return [
                    'id' => $bundle->id,
                    'bundle_name_certificate' => $bundle->bundle_name_certificate,
                    'status' => $bundle->status,
                    'students' => $bundle->bundleStudents->map(function ($bs) {
                        return [
                            'student_id' => $bs->student_id,
                            'user_name' => optional($bs->student->user)->full_name, 
                            'email' => optional($bs->student->user)->email, 
                            'assigned_at' => optional($bs->created_at)->format('d/m/Y'), // added line
                        ];
                    }),
                ];
            });

        $webinars = Webinar::where('teacher_id', $user->id)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name_certificate' => $course->course_name_certificate,
                    'status' => $course->status,
                ];
            });

        return response()->json([
            'bundles' => $bundles,
            'webinars' => $webinars
        ]);
    }
}
