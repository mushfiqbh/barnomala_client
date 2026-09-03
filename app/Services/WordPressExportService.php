<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class WordPressExportService
{
    public function getExportResources(): array
    {
        return [
            'students',
            'student/enrollments',
            'subjects',
            'classes',
            'teachers',
            'exams',
            'exams/schedules',
            'exams/results',
            'slider-images',
            'committees',
            'governing-body',
            'options',
        ];
    }

    public function getExportPayload(string $resource): array
    {
        return match ($resource) {
            'students' => $this->exportStudents(),
            'student/enrollments' => $this->exportStudentEnrollments(),
            'subjects' => $this->exportSubjects(),
            'classes' => $this->exportClasses(),
            'teachers' => $this->exportTeachers(),
            'exams' => $this->exportExams(),
            'exams/schedules' => $this->exportExamSchedules(),
            'exams/results' => $this->exportExamResults(),
            'slider-images' => $this->exportSliderImages(),
            'committees' => $this->exportCommittees(),
            'governing-body' => $this->exportGoverningBody(),
            'options' => $this->exportOptions(),
            default => throw new RuntimeException('Unsupported export resource: '.$resource),
        };
    }

    public function exportStudents(): array
    {
        $rows = $this->rows(
            'SELECT studentid, stdName, stdNameBangla, stdGender, stdBldGrp, stdImg, stdFather, fatherLate, stdFatherProf, stdMother, motherLate, stdMotherProf, stdParentIncome, stdlocalGuardian, stdGuardianNID, stdPhone, stdPermanent, stdPresent, stdBrith, stdNationality, stdReligion, stdAdmitClass, stdAdmitYear, stdTcNumber, sscRoll, sscReg, stdPrevSchool, stdGPA, stdIntellectual, stdScholarsClass, stdScholarsYear, stdScholarsMemo, birth_reg_no, c.className as stdAdmitClass
             FROM ct_student s
             LEFT JOIN ct_class c ON s.stdAdmitClass = c.classid'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        $genderMap = [0 => 'female', 1 => 'male', 2 => 'other'];
        foreach ($rows as &$student) {
            $genderValue = (int) ($student['stdGender'] ?? 2);
            $student['stdGender'] = $genderMap[$genderValue] ?? 'other';
        }
        unset($student);

        return ['success' => true, 'data' => $rows];
    }

    public function exportStudentEnrollments(): array
    {
        $rows = $this->rows(
            'SELECT si.infoid, si.infoStdid, c.className as infoClass, s.sectionName as infoSection, si.infoYear, g.groupName as infoGroup, si.infoRoll, si.infoOptionals, si.info4thSub
             FROM ct_studentinfo si
             LEFT JOIN ct_class c ON si.infoClass = c.classid
             LEFT JOIN ct_section s ON si.infoSection = s.sectionid
             LEFT JOIN ct_group g ON si.infoGroup = g.groupId
             ORDER BY si.infoYear DESC, CAST(si.infoClass AS UNSIGNED) ASC, CAST(si.infoRoll AS UNSIGNED) ASC'
        );

        return [
            'success' => true,
            'count' => count($rows),
            'data' => $rows,
        ];
    }

    public function exportSubjects(): array
    {
        $rows = $this->rows(
            'SELECT s.*, c.className as subjectClassName, t.teacherName as subjectTeacherName
             FROM ct_subject s
             LEFT JOIN ct_class c ON s.subjectClass = c.classid
             LEFT JOIN ct_teacher t ON s.subjectTeacher = t.teacherid'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        foreach ($rows as &$subject) {
            $groupCk = (string) ($subject['forGroup'] ?? '');
            $groupNames = [];

            if ($groupCk === '0' || $groupCk === 'all' || $groupCk === '') {
                $groupNames[] = 'All Group';
            } else {
                $groupIds = [];
                if (is_numeric($groupCk)) {
                    $groupIds[] = (int) $groupCk;
                } else {
                    $decoded = json_decode($groupCk, true);
                    if (is_array($decoded)) {
                        $groupIds = array_map('intval', $decoded);
                    }
                }

                if ($groupIds !== []) {
                    $groups = DB::connection('wordpress')
                        ->table('ct_group')
                        ->whereIn('groupId', $groupIds)
                        ->pluck('groupName')
                        ->all();

                    foreach ($groups as $groupName) {
                        $groupNames[] = (string) $groupName;
                    }
                }
            }

            $subject['forGroupNames'] = $groupNames;
        }
        unset($subject);

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportTeachers(): array
    {
        $rows = $this->rows(
            'SELECT t.*, u.user_login
             FROM ct_teacher t
             LEFT JOIN sm_users u ON u.ID = t.tecUserId AND t.tecUserId > 0
             ORDER BY t.teacher_serial ASC, t.teacherName ASC'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        foreach ($rows as &$teacher) {
            $jsonFields = ['tecAssignSub', 'teacherQualificarion', 'assignSection', 'teacherTraining'];
            foreach ($jsonFields as $field) {
                if (isset($teacher[$field]) && $teacher[$field] !== '' && $teacher[$field] !== null) {
                    $decoded = json_decode((string) $teacher[$field], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $teacher[$field] = $decoded;
                    }
                }
            }
        }
        unset($teacher);

        return [
            'success' => true,
            'count' => count($rows),
            'data' => $rows,
        ];
    }

    public function exportExams(): array
    {
        $rows = $this->rows(
            'SELECT e.*, c.className, c2.className as className2, g.groupName
             FROM ct_exam e
             LEFT JOIN ct_class c ON e.examClass = c.classid
             LEFT JOIN ct_class c2 ON e.examClass2 = c2.classid
             LEFT JOIN ct_group g ON e.examGroup = g.groupId
             ORDER BY e.examSirial ASC, e.examid DESC'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        foreach ($rows as &$exam) {
            if (isset($exam['examSubjects']) && $exam['examSubjects'] !== '' && $exam['examSubjects'] !== null) {
                $decoded = json_decode((string) $exam['examSubjects'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $exam['examSubjects'] = $decoded;
                }
            }
        }
        unset($exam);

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportExamSchedules(): array
    {
        $rows = $this->rows(
            'SELECT es.*, c.className, e.examName
             FROM ct_exam_schedule es
             LEFT JOIN ct_class c ON es.classid = c.classid
             LEFT JOIN ct_exam e ON es.examid = e.examid
             ORDER BY es.year DESC, es.examid DESC, es.classid ASC'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        foreach ($rows as &$schedule) {
            if (isset($schedule['subject_dates']) && $schedule['subject_dates'] !== '' && $schedule['subject_dates'] !== null) {
                $decoded = json_decode((string) $schedule['subject_dates'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $schedule['subject_dates'] = $decoded;
                }
            }
        }
        unset($schedule);

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportExamResults(?int $perPage = null): array
    {
        $query = DB::connection('wordpress')
            ->table('ct_result as r')
            ->leftJoin('ct_class as c', 'r.resClass', '=', 'c.classid')
            ->leftJoin('ct_group as g', 'r.resgroup', '=', 'g.groupId')
            ->leftJoin('ct_section as s', 'r.resSec', '=', 's.sectionid')
            ->leftJoin('ct_exam as e', 'r.resExam', '=', 'e.examid')
            ->orderByDesc('r.resultYear')
            ->orderByDesc('r.resExam')
            ->orderBy('r.resClass')
            ->orderByRaw('CAST(r.resStdRoll AS UNSIGNED) ASC')
            ->select('r.*', 'c.className', 'g.groupName', 's.sectionName', 'e.examName');

        if ($perPage !== null) {
            $results = $query->paginate($perPage);

            return [
                'success' => true,
                'count' => $results->count(),
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'last_page' => $results->lastPage(),
                ],
            ];
        }

        $rows = $query->get()->map(static fn ($row) => (array) $row)->all();

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportSliderImages(): array
    {
        $rows = $this->rows('SELECT id, image_url, created_at FROM sm_slider_images ORDER BY created_at DESC');

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportCommittees(): array
    {
        $committees = $this->rows(
            'SELECT committee_id, committee_title, job_description, sort_order, is_primary, is_active, created_at, updated_at
             FROM ct_committees
             ORDER BY sort_order ASC'
        );

        if ($committees === []) {
            return ['success' => true, 'data' => []];
        }

        foreach ($committees as &$committee) {
            $committeeId = (int) ($committee['committee_id'] ?? 0);
            $members = $this->rows(
                'SELECT member_id, member_label, member_name, member_designation, member_subject, member_position, sort_order, is_active, created_at, updated_at
                 FROM ct_committee_members
                 WHERE committee_id = ?
                 ORDER BY sort_order ASC',
                [$committeeId]
            );

            $committee['members'] = $members;
        }
        unset($committee);

        return ['success' => true, 'count' => count($committees), 'data' => $committees];
    }

    public function exportGoverningBody(): array
    {
        $rows = $this->rows(
            'SELECT governing_body_id, governing_body_session, governing_body_name, governing_body_image, governing_body_father_name, governing_body_mother_name, governing_body_designation, note, order_number, is_active, created_at, updated_at
             FROM ct_governing_body
             ORDER BY (order_number + 0) ASC'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportClasses(): array
    {
        $rows = $this->rows(
            'SELECT classid, className, haveOptionalSub, have4thSub, havecgpa, combineMark, session
             FROM ct_class
             ORDER BY classid ASC'
        );

        if ($rows === []) {
            return ['success' => true, 'data' => []];
        }

        return ['success' => true, 'count' => count($rows), 'data' => $rows];
    }

    public function exportOptions(): array
    {
        $allowedOptions = [
            'institute_name', 'institute_address', 'institute_email', 'institute_phone',
            'institute_eiin', 'institute_code', 'center_code', 'estd_year',
            'inst_head_title', 'inst_head_name', 'homeHeadmasterTitle', 'headmasterSpeechTitle',
            'homeHeadmaster', 'homeHeadmasterImg', 'homeChairmanTitle', 'chairmanSpeechTitle',
            'homeChairman', 'homeChairmanImg', 'principalSign', 'aboutTitelText',
            'aboutUsText', 'aboutUsTextLimit', 'aboutUsMoreBtn', 'footerAddress',
            'footerContact', 'footerFbUrl', 'copyrightText', 'board_name_1',
            'board_name_2', 'admitCareNote', 'stdidpref',
        ];

        $optionsData = [];
        $smOptionsTable = $this->resolveTable(['sm_options']);

        if ($smOptionsTable !== null) {
            $smRows = DB::connection('wordpress')
                ->table($smOptionsTable)
                ->select(['option_name', 'option_value'])
                ->whereIn('option_name', $allowedOptions)
                ->get();

            foreach ($smRows as $row) {
                $value = $this->decodeMaybeJson($row->option_value);
                $optionsData[$row->option_name] = $value;
            }
        }

        $wpOptionsTable = $this->resolveTable(['options', 'wp_options']);
        if ($wpOptionsTable !== null) {
            $optNameValue = DB::connection('wordpress')
                ->table($wpOptionsTable)
                ->where('option_name', 'opt_name')
                ->value('option_value');

            $reduxOptions = $this->decodeMaybeSerialized($optNameValue);
            if (is_array($reduxOptions)) {
                foreach ($allowedOptions as $key) {
                    if (! array_key_exists($key, $optionsData) && array_key_exists($key, $reduxOptions)) {
                        $optionsData[$key] = $reduxOptions[$key];
                    }
                }
            }
        }

        return ['success' => true, 'data' => $optionsData];
    }

    private function rows(string $sql, array $bindings = []): array
    {
        $rows = DB::connection('wordpress')->select($sql, $bindings);

        return array_map(static fn ($row) => (array) $row, $rows);
    }

    private function resolveTable(array $candidates): ?string
    {
        foreach ($candidates as $table) {
            if (Schema::connection('wordpress')->hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function decodeMaybeJson(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    private function decodeMaybeSerialized(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = @unserialize($value, ['allowed_classes' => false]);
        if ($decoded !== false || $value === 'b:0;') {
            return $decoded;
        }

        return $this->decodeMaybeJson($value);
    }
}
