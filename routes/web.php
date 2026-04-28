<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\CatalogueManagement;
use App\Livewire\Exam;
use App\Livewire\OnlineCourse;
use App\Livewire\OfflineCourse;
use App\Livewire\BlendedCourse;
use App\Livewire\CourseDetail;
use App\Livewire\UserManagement;
use App\Livewire\LearningPath;
use App\Livewire\PermissionManagement;
use App\Livewire\Quiz;
use App\Livewire\QuestionBank;
use App\Livewire\QuestionSet;
use App\Livewire\Enrolment;
use App\Livewire\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once dirname(__DIR__) . '/app/Helpers/LangHelper.php';

Route::middleware('initlang')->group(function () {
    Route::match(['GET', 'POST'], '/tms', Home::class);
    Route::match(['GET', 'POST'], '/tms/exam', Exam::class);
    Route::match(['GET', 'POST'], '/tms/online-course', OnlineCourse::class);
    Route::match(['GET', 'POST'], '/tms/offline-course', OfflineCourse::class);
    Route::match(['GET', 'POST'], '/tms/blended-course', BlendedCourse::class);
    Route::match(['GET', 'POST'], '/tms/course/{id}', CourseDetail::class)->where('id', '[0-9]+');
    Route::match(['GET', 'POST'], '/tms/users', UserManagement::class);
    Route::match(['GET', 'POST'], '/tms/catalogue', CatalogueManagement::class);
    Route::match(['GET', 'POST'], '/tms/learning-path', LearningPath::class);
    Route::match(['GET', 'POST'], '/tms/permissions', PermissionManagement::class);
    Route::match(['GET', 'POST'], '/tms/question-bank', QuestionBank::class);
    Route::match(['GET', 'POST'], '/tms/question-set', QuestionSet::class);
    Route::match(['GET', 'POST'], '/tms/organization', Organization::class);
    
    Route::get('/tms/course/{id}/image', function($id) {
        $moodle = app('moodle');
        $image = $moodle->getCourseImage($id);
        
        if (!$image) {
            abort(404);
        }
        
        $filepath = '/var/www/moodledata/files/' . substr($image->contenthash, 0, 2) . '/' . substr($image->contenthash, 2, 2) . '/' . $image->contenthash;
        
        if (!file_exists($filepath)) {
            abort(404);
        }
        
        return response()->file($filepath, [
            'Content-Type' => $image->mimetype ?? 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    })->where('id', '[0-9]+');
    
    Route::get('/tms/download-sample-enrolment', function() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Email');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Thời gian bắt đầu');
        $sheet->setCellValue('D1', 'Thời gian kết thúc');
        
        $sheet->setCellValue('A2', 'user1@example.com');
        $sheet->setCellValue('B2', 'user1');
        $sheet->setCellValue('C2', '2025-01-01 08:00:00');
        $sheet->setCellValue('D2', '2025-12-31 17:00:00');
        
        $sheet->setCellValue('A3', 'user2@example.com');
        $sheet->setCellValue('B3', 'user2');
        $sheet->setCellValue('C3', '2025-01-01 08:00:00');
        $sheet->setCellValue('D3', '2025-12-31 17:00:00');
        
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(22);
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="sample_enrolment.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    });
});
// Quiz management route (render via Livewire component directly)
Route::get('/tms/quiz', Quiz::class);
