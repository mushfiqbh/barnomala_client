<?php

return [
    'navigation_items' => [
        ['label' => 'Home', 'route' => 'home', 'children' => []],
        ['label' => 'About', 'url' => '#', 'children' => [
            ['label' => 'Speech', 'route' => 'speeches.index'],
            ['label' => 'History', 'route' => 'history.index'],
            ['label' => 'Achivements', 'route' => 'achievements.index'],
        ]],
        ['label' => 'Academic', 'route' => 'academic.index', 'children' => [
            ['label' => 'Academic Calendar', 'route' => 'academic.calendar'],
            ['label' => 'Academic Rules', 'route' => 'academic.rules'],
            ['label' => 'Class Schedule', 'route' => 'academic.schedule'],
            ['label' => 'Exam Schedule', 'route' => 'academic.exam-schedule'],
            ['label' => 'Lecturers', 'route' => 'lecturers.index'],
            ['label' => 'Teachers', 'route' => 'teachers.index'],
            ['label' => 'Incharges', 'route' => 'staff.incharges'],
            ['label' => 'Staff', 'route' => 'staff.index'],
            ['label' => 'Committees', 'route' => 'committees.index'],
            ['label' => 'Former Teachers', 'route' => 'teachers.former'],
            ['label' => 'Former Staff', 'route' => 'staff.former'],
        ]],
        ['label' => 'Student', 'route' => 'teachers.index', 'children' => [
            ['label' => 'Student', 'route' => 'student.index'],
            ['label' => 'Result', 'route' => 'student.result'],
            ['label' => 'Tution Fees', 'route' => 'student.tuition-fees'],
            ['label' => 'Student Uniform', 'route' => 'student.uniform'],
            ['label' => 'Daily Activities', 'route' => 'student.activities'],
            ['label' => 'Mobile Banking', 'route' => 'student.mobile-banking'],
        ]],
        ['label' => 'Result', 'route' => 'student.result', 'children' => []],
        ['label' => 'Notice', 'route' => 'notices.index', 'children' => []],
        ['label' => 'Gallery', 'route' => 'gallery.index', 'children' => []],
        ['label' => 'Download', 'route' => 'download.index', 'children' => []],
        ['label' => 'Contact', 'route' => 'contact.index', 'children' => []],
    ],
];
