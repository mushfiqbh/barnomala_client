<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admission Application Slip{{ !empty($application['application_no']) ? ' - ' . $application['application_no'] : '' }}</title>
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,600,700" rel="stylesheet">
    <style>
        body { font-family: Quicksand, Arial, sans-serif; color: #000; font-size: 14px; }

        .slip { border: 2px solid #000; padding: 24px; }
        .slip .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 16px; margin-bottom: 16px; }
        .slip .school-name { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .slip .school-address { font-size: 13px; margin-bottom: 12px; }
        .slip .badge {
            font-size: 18px; font-weight: bold; background: #000; color: #fff;
            text-align: center; padding: 8px 20px;
        }
        .slip h4 { background: #e5e7eb; padding: 5px 10px; margin: 15px 0 5px; font-size: 14px; }
        .slip table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; }
        .slip table td { border: 1px solid #ccc; padding: 7px 10px; }
        .slip table td.label { background: #f3f4f6; width: 35%; font-weight: bold; }
        .slip .student-info-row { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .slip .student-info-row > td { border: none; padding: 0; vertical-align: top; }
        .slip .student-info-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .slip .student-info-table td { border: 1px solid #ccc; padding: 7px 10px; }
        .slip .student-info-table td.label { background: #f3f4f6; width: 35%; font-weight: bold; }
        .slip .student-photo-cell {
            width: 130px;
            padding-left: 14px;
            text-align: center;
            vertical-align: top;
        }
        .slip .student-photo-frame {
            width: 120px;
            height: 150px;
            border: 1px solid #000;
            background: #fff;
            display: block;
            overflow: hidden;
        }
        .slip .student-photo-frame img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            display: block;
        }
        .slip .student-photo-placeholder {
            width: 120px;
            height: 150px;
            border: 1px solid #000;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 11px;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .slip .student-photo-caption {
            margin-top: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .slip .grid-2 { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 14px; }
        .slip .grid-2 td { border: 1px solid #ccc; padding: 7px 10px; width: 50%; vertical-align: top; }
        .slip .grid-2 td.label { background: #f3f4f6; width: 25%; font-weight: bold; }
    </style>
</head>
<body>
    @if(! $application)
        <div class="slip" style="text-align:center; padding:60px 30px;">
            <p style="font-size:18px; font-weight:bold;">Application not found.</p>
            <p style="font-size:14px; margin-top:8px;">The application could not be located. Please search again from My Applications.</p>
        </div>
    @else
        @php
            $applicationNo = $application['application_no'] ?? null;
            $appId = ($applicationNo !== null && $applicationNo !== '')
                ? (strlen((string) $applicationNo) >= 5 ? (string) $applicationNo : str_pad((string) $applicationNo, 5, '0', STR_PAD_LEFT))
                : null;

            $genderMap = ['male' => 'Boy', 'female' => 'Girl', 'other' => 'Other', 1 => 'Boy', 0 => 'Girl'];
            $gender = $genderMap[$application['gender'] ?? ''] ?? ($application['gender'] ?? null);
            $religion = $application['religion_name'] ?? $application['religion'] ?? $application['religion_id'] ?? null;
            $bloodGroup = $application['blood_group'] ?? null;
            $className = $application['admission_class_name'] ?? $application['class_name'] ?? null;
            $admissionYear = $application['admission_year'] ?? null;
            $sessionName = $application['session_name'] ?? $application['session'] ?? $admissionYear;
            $groupName = $application['group_name']
                ?? $application['applying_group_name']
                ?? $application['applying_group']
                ?? $application['group']
                ?? null;

            $fourthSubjectNames = [];
            $rawFourthSubjects = $application['fourth_subjects'] ?? null;
            if (is_array($rawFourthSubjects)) {
                foreach ($rawFourthSubjects as $entry) {
                    if (is_string($entry)) {
                        $name = trim($entry);
                    } elseif (is_array($entry)) {
                        $name = $entry['name'] ?? $entry['title'] ?? $entry['label'] ?? null;
                        $name = is_string($name) ? trim($name) : '';
                    } else {
                        $name = '';
                    }
                    if ($name !== '') {
                        $fourthSubjectNames[] = $name;
                    }
                }
            }
            if (empty($fourthSubjectNames)) {
                $rawIds = $application['fourth_subject_ids'] ?? null;
                if (is_array($rawIds)) {
                    foreach ($rawIds as $id) {
                        if (is_scalar($id) && trim((string) $id) !== '') {
                            $fourthSubjectNames[] = 'Subject #' . trim((string) $id);
                        }
                    }
                }
            }
            $fourthSubjectText = empty($fourthSubjectNames) ? null : implode(', ', $fourthSubjectNames);

            $rawStatus = $application['application_status']
                ?? $application['status']
                ?? $application['admission_status']
                ?? 'pending';
            $statusLabel = trim((string) (is_scalar($rawStatus) ? $rawStatus : (string) $rawStatus));
            $statusLabel = $statusLabel === '' ? 'pending' : ucfirst(strtolower($statusLabel));
            $printDate = now()->format('d/m/Y');

            $scalarize = function ($value) {
                if ($value === null) {
                    return null;
                }

                if (is_bool($value)) {
                    return $value ? '1' : '0';
                }

                if (is_scalar($value)) {
                    return $value;
                }

                if ($value instanceof \DateTimeInterface) {
                    return $value->format('d/m/Y');
                }

                if (is_array($value)) {
                    $parts = [];
                    foreach ($value as $piece) {
                        if (is_scalar($piece) || $piece === null) {
                            $pieceScalar = is_bool($piece) ? ($piece ? '1' : '0') : $piece;
                        } elseif ($piece instanceof \DateTimeInterface) {
                            $pieceScalar = $piece->format('d/m/Y');
                        } elseif (is_object($piece) && method_exists($piece, '__toString')) {
                            $pieceScalar = (string) $piece;
                        } else {
                            $pieceScalar = '';
                        }

                        if ($pieceScalar !== null && $pieceScalar !== '') {
                            $parts[] = $pieceScalar;
                        }
                    }

                    return empty($parts) ? null : implode(', ', $parts);
                }

                if (is_object($value)) {
                    return method_exists($value, '__toString')
                        ? (string) $value
                        : null;
                }

                return null;
            };

            $row = function (string $label, $value) use ($scalarize): string {
                $value = $scalarize($value);
                $value = trim((string) $value);

                if ($value === '') {
                    $value = '-';
                }

                return '<tr>'
                    . '<td class="label">' . e($label) . '</td>'
                    . '<td>' . e($value) . '</td>'
                    . '</tr>';
            };
        @endphp

        <div class="slip">
            <div class="header">
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-address">{!! $schoolAddress !== '' ? e($schoolAddress) : '&nbsp;' !!}</div>
                <div class="badge">Admission Application Slip</div>
            </div>

            <table style="width:100%; border-collapse:collapse; margin-bottom:16px; font-size:13px; font-weight:bold;">
                <tr>
                    <td style="width:50%;">Application ID: {{ $appId ? 'APP-' . $appId : 'N/A' }}</td>
                    <td style="width:50%; text-align:right;">Date: {{ $printDate }}</td>
                </tr>
            </table>

            <h4>Student Information</h4>
            <table class="student-info-row">
                <tr>
                    <td>
                        <table class="student-info-table">
                            {!! $row('Name (English)', $application['full_name'] ?? null) !!}
                            @if(!empty($application['full_name_bn']))
                                {!! $row('Name (Bangla)', $application['full_name_bn']) !!}
                            @endif
                            {!! $row('Gender', $gender) !!}
                            {!! $row('Date of Birth', $application['dob'] ?? null) !!}
                            {!! $row('Religion', $religion) !!}
                            {!! $row('Blood Group', $bloodGroup) !!}
                        </table>
                    </td>
                    <td class="student-photo-cell">
                        @if(!empty($photoDataUri))
                            <img class="student-photo-frame" src="{{ $photoDataUri }}" alt="Applicant Photo">
                        @else
                            <div class="student-photo-placeholder">Photo<br>Not Available</div>
                        @endif
                        <div class="student-photo-caption">Applicant Photo</div>
                    </td>
                </tr>
            </table>

            <h4>Admission Details</h4>
            <table class="grid-2">
                <tr>
                    <td class="label">Class</td>
                    <td>{{ $className ?? '-' }}</td>
                    <td class="label">Session</td>
                    <td>{{ $sessionName ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Group</td>
                    <td>{{ $groupName ?: '-' }}</td>
                    <td class="label">Status</td>
                    <td>{{ $statusLabel }}</td>
                </tr>
                @if(!empty($fourthSubjectText))
                    <tr>
                        <td class="label">Fourth Subject</td>
                        <td colspan="3">{{ $fourthSubjectText }}</td>
                    </tr>
                @endif
            </table>

            <h4>Guardian Information</h4>
            <table>
                {!! $row("Father's Name", $application['father_name'] ?? null) !!}
                {!! $row("Mother's Name", $application['mother_name'] ?? null) !!}
                {!! $row("Father's Profession", $application['father_profession'] ?? null) !!}
                {!! $row("Mother's Profession", $application['mother_profession'] ?? null) !!}
                {!! $row("Guardian NID", $application['guardian_nid'] ?? null) !!}
                {!! $row('Contact Phone', $application['phone'] ?? null) !!}
            </table>

            <h4>Address</h4>
            <table>
                {!! $row('Present Address', $application['present_address'] ?? null) !!}
            </table>

            <table style="width:100%; margin-top:60px; text-align:center; font-size:13px;">
                <tr>
                    <td style="width:33%; border-top:1px solid #000; padding-top:5px;">Guardian's Signature</td>
                    <td style="width:33%; border-top:1px solid #000; padding-top:5px;">Applicant's Signature</td>
                    <td style="width:33%; border-top:1px solid #000; padding-top:5px;">Office Seal & Signature</td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>
