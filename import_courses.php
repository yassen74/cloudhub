<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(405);
    exit('CLI only');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require __DIR__ . '/dbConnection.php';

$csvPath = __DIR__ . '/courses.csv';

if (!is_file($csvPath)) {
    fwrite(STDERR, "ERROR: courses.csv not found at $csvPath\n");
    exit(1);
}

/**
 * Normalize strings for stable CSV dedupe and track lookup.
 */
function normalize_key(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return strtolower($value);
}

/**
 * Parse CSV rows and dedupe by track + course name while preserving order.
 *
 * @return array<int, array<string, mixed>>
 */
function load_courses_from_csv(string $csvPath): array
{
    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Unable to open CSV: $csvPath");
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        throw new RuntimeException('CSV is empty.');
    }

    $header = array_map(
        static fn($column): string => trim((string) $column),
        $header
    );

    $required = [
        'track_name',
        'course_name',
        'course_desc',
        'course_author',
        'course_duration',
        'course_price',
        'course_original_price',
        'course_img',
    ];

    foreach ($required as $column) {
        if (!in_array($column, $header, true)) {
            fclose($handle);
            throw new RuntimeException("CSV missing required column: $column");
        }
    }

    $rows = [];
    $seen = [];
    $lineNumber = 1;

    while (($data = fgetcsv($handle)) !== false) {
        $lineNumber++;
        if (count($data) === 1 && trim((string) $data[0]) === '') {
            continue;
        }

        if (count($data) !== count($header)) {
            throw new RuntimeException("CSV line $lineNumber has " . count($data) . ' columns; expected ' . count($header));
        }

        /** @var array<string, string> $row */
        $row = array_combine($header, $data);
        $trackName = trim($row['track_name']);
        $courseName = trim($row['course_name']);

        if ($trackName === '' || $courseName === '') {
            continue;
        }

        $dedupeKey = normalize_key($trackName . '|' . $courseName);
        if (isset($seen[$dedupeKey])) {
            continue;
        }

        $seen[$dedupeKey] = true;
        $rows[] = [
            'track_name' => $trackName,
            'course_name' => $courseName,
            'course_desc' => trim($row['course_desc']),
            'course_author' => trim($row['course_author']) !== '' ? trim($row['course_author']) : 'CloudHub',
            'course_duration' => trim($row['course_duration']) !== '' ? trim($row['course_duration']) : '2h',
            'course_price' => (float) $row['course_price'],
            'course_original_price' => (float) $row['course_original_price'],
            'course_img' => trim($row['course_img']),
        ];
    }

    fclose($handle);
    return $rows;
}

/**
 * Fetch live tracks and map normalized track names to ids.
 *
 * @return array<string, int>
 */
function fetch_track_map(mysqli $conn): array
{
    $map = [];
    $result = $conn->query('SELECT track_id, track_name FROM tracks ORDER BY track_id ASC');
    while ($result && $row = $result->fetch_assoc()) {
        $map[normalize_key((string) $row['track_name'])] = (int) $row['track_id'];
    }
    return $map;
}

/**
 * Group parsed courses by track_id.
 *
 * @param array<int, array<string, mixed>> $csvRows
 * @param array<string, int> $trackMap
 * @return array<int, array<int, array<string, mixed>>>
 */
function group_courses_by_track(array $csvRows, array $trackMap): array
{
    $grouped = [];
    $missingTracks = [];

    foreach ($csvRows as $row) {
        $trackKey = normalize_key((string) $row['track_name']);
        if (!isset($trackMap[$trackKey])) {
            $missingTracks[$trackKey] = (string) $row['track_name'];
            continue;
        }

        $trackId = $trackMap[$trackKey];
        $grouped[$trackId][] = $row;
    }

    if ($missingTracks !== []) {
        $missing = implode(', ', array_values($missingTracks));
        throw new RuntimeException("CSV contains track names not found in DB: $missing");
    }

    ksort($grouped);
    return $grouped;
}

/**
 * @return array<int, int>
 */
function fetch_existing_course_ids(mysqli $conn, int $trackId): array
{
    $ids = [];
    $stmt = $conn->prepare('SELECT course_id FROM course WHERE track_id = ? ORDER BY course_id ASC');
    $stmt->bind_param('i', $trackId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
        $ids[] = (int) $row['course_id'];
    }
    $stmt->close();
    return $ids;
}

/**
 * Synchronize all tracks while preserving existing course ids wherever possible.
 *
 * @param array<int, array<int, array<string, mixed>>> $groupedCourses
 * @return array<string, int>
 */
function sync_courses(mysqli $conn, array $groupedCourses): array
{
    $stats = [
        'updated' => 0,
        'inserted' => 0,
        'deleted' => 0,
        'lessons_deleted' => 0,
        'tracks_processed' => 0,
        'csv_courses' => 0,
    ];

    $updateStmt = $conn->prepare(
        'UPDATE course
         SET track_id = ?, course_name = ?, course_desc = ?, course_author = ?, course_img = ?, course_duration = ?, course_price = ?, course_original_price = ?
         WHERE course_id = ? LIMIT 1'
    );
    $insertStmt = $conn->prepare(
        'INSERT INTO course (track_id, course_name, course_desc, course_author, course_img, course_duration, course_price, course_original_price)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $deleteLessonsStmt = $conn->prepare('DELETE FROM lesson WHERE course_id = ?');
    $deleteCourseStmt = $conn->prepare('DELETE FROM course WHERE course_id = ? LIMIT 1');

    foreach ($groupedCourses as $trackId => $courses) {
        $stats['tracks_processed']++;
        $stats['csv_courses'] += count($courses);
        $existingIds = fetch_existing_course_ids($conn, $trackId);
        $sharedCount = min(count($existingIds), count($courses));

        for ($index = 0; $index < $sharedCount; $index++) {
            $courseId = $existingIds[$index];
            $course = $courses[$index];
            $courseName = (string) $course['course_name'];
            $courseDesc = (string) $course['course_desc'];
            $courseAuthor = (string) $course['course_author'];
            $courseImg = (string) $course['course_img'];
            $courseDuration = (string) $course['course_duration'];
            $coursePrice = (float) $course['course_price'];
            $courseOriginalPrice = (float) $course['course_original_price'];

            $updateStmt->bind_param(
                'isssssddi',
                $trackId,
                $courseName,
                $courseDesc,
                $courseAuthor,
                $courseImg,
                $courseDuration,
                $coursePrice,
                $courseOriginalPrice,
                $courseId
            );
            $updateStmt->execute();
            $stats['updated']++;
        }

        for ($index = $sharedCount; $index < count($courses); $index++) {
            $course = $courses[$index];
            $courseName = (string) $course['course_name'];
            $courseDesc = (string) $course['course_desc'];
            $courseAuthor = (string) $course['course_author'];
            $courseImg = (string) $course['course_img'];
            $courseDuration = (string) $course['course_duration'];
            $coursePrice = (float) $course['course_price'];
            $courseOriginalPrice = (float) $course['course_original_price'];

            $insertStmt->bind_param(
                'isssssdd',
                $trackId,
                $courseName,
                $courseDesc,
                $courseAuthor,
                $courseImg,
                $courseDuration,
                $coursePrice,
                $courseOriginalPrice
            );
            $insertStmt->execute();
            $stats['inserted']++;
        }

        for ($index = $sharedCount; $index < count($existingIds); $index++) {
            $courseId = $existingIds[$index];

            $deleteLessonsStmt->bind_param('i', $courseId);
            $deleteLessonsStmt->execute();
            $stats['lessons_deleted'] += $deleteLessonsStmt->affected_rows;

            $deleteCourseStmt->bind_param('i', $courseId);
            $deleteCourseStmt->execute();
            $stats['deleted'] += $deleteCourseStmt->affected_rows;
        }
    }

    $updateStmt->close();
    $insertStmt->close();
    $deleteLessonsStmt->close();
    $deleteCourseStmt->close();

    return $stats;
}

try {
    $csvRows = load_courses_from_csv($csvPath);
    $trackMap = fetch_track_map($conn);
    $groupedCourses = group_courses_by_track($csvRows, $trackMap);

    $conn->begin_transaction();
    $stats = sync_courses($conn, $groupedCourses);
    $conn->commit();

    echo "Import completed successfully.\n";
    echo 'Tracks processed: ' . $stats['tracks_processed'] . "\n";
    echo 'CSV unique courses: ' . $stats['csv_courses'] . "\n";
    echo 'Updated existing courses: ' . $stats['updated'] . "\n";
    echo 'Inserted new courses: ' . $stats['inserted'] . "\n";
    echo 'Deleted surplus placeholder courses: ' . $stats['deleted'] . "\n";
    echo 'Deleted lessons for removed courses: ' . $stats['lessons_deleted'] . "\n";
} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Ignore rollback failures and surface the original error.
        }
    }

    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}
