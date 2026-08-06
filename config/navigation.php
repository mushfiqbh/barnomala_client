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
            ['label' => 'Teachers', 'route' => 'teachers.index'],
            ['label' => 'Former Teachers', 'route' => 'teachers.former'],
            ['label' => 'Lecturers', 'route' => 'lecturers.index'],
            ['label' => 'Staff', 'route' => 'staff.index'],
            ['label' => 'Former Staff', 'route' => 'staff.former'],
            ['label' => 'Committees', 'route' => 'committees.index'],
        ]],
        ['label' => 'Student', 'route' => 'teachers.index', 'children' => [
            ['label' => 'Student', 'route' => 'students.index'],
            ['label' => 'Result', 'route' => 'student.result'],
            ['label' => 'Tution Fees', 'route' => 'student.tuition-fees'],
            ['label' => 'Student Uniform', 'route' => 'student.uniform'],
            ['label' => 'Daily Activities', 'route' => 'student.activities'],
            ['label' => 'Mobile Banking', 'route' => 'student.mobile-banking'],
        ]],
        ['label' => 'Result', 'route' => 'student.result', 'children' => []],
        ['label' => 'Notice', 'route' => 'notices.index', 'children' => []],
        ['label' => 'News', 'route' => 'news.index', 'children' => []],
        ['label' => 'Gallery', 'route' => 'gallery.index', 'children' => []],
        ['label' => 'Contact', 'route' => 'contact.index', 'children' => []],
    ],
];
