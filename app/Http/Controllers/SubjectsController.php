<?php

namespace App\Http\Controllers;

use App\Support\DashboardMockData;
use App\Support\SubjectsMockData;
use Illuminate\View\View;

class SubjectsController extends Controller
{
    public function __invoke(): View
    {
        return view('subjects.index', DashboardMockData::make() + [
            'active_nav' => 'subjects',
            'page_title' => 'Mata Pelajaran',
            'search_placeholder' => 'Cari mata pelajaran...',
            'subjects' => SubjectsMockData::make(),
        ]);
    }
}
