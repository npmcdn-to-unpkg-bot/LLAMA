<?php
// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=LLAMA_B_RESULTS.csv');

require('../php/util.php');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

$q_default_order = loadJSON('../resources/bDefaultOrder.json');
$columns = array('', '');
$i = 0;

foreach ($q_default_order as $question) {
    $i++;
    array_push($columns, $i);
    addEmptyCelLs($columns, 2);
}
array_push($columns, 'Total');
// output the column headings for first row
fputcsv($output, $columns);


$questions = array('Question >', '');
foreach ($q_default_order as $question) {
    array_push($questions, $question);
    addEmptyCelLs($questions, 2);
}
// output questions rows
fputcsv($output, $questions);

$thirdRowColumns = array('Name', 'Nr. of seconds to learn');
for($i = 0; $i<  sizeof($q_default_order); $i++) {
    array_push($thirdRowColumns, "Is correct");
    array_push($thirdRowColumns, "Answer");
    array_push($thirdRowColumns, "Reaction time (s)");
}
// output rows
fputcsv($output, $thirdRowColumns);

$dir = 'b';
$files = array_filter(scandir($dir), function ($item) {
    return is_file('b/' . $item);
});
usort($files, 'strnatcasecmp');

foreach ($files as $file) {
    $result = loadJSON($dir . '/' . $file);
    $result_cols = array($file, $result['nrOfSeconds']);

    $testResults = $result['testResults'];
    usort($testResults, 'sort_based_on_list_of_values');

    foreach ($testResults as $testCaseResult) {
        $isCorrect = $testCaseResult['question'] == $testCaseResult['answer'] ? 1 : 0;
        array_push($result_cols, $isCorrect);
        array_push($result_cols, $testCaseResult['answer']);
        array_push($result_cols, $testCaseResult['answerTimeSeconds']);
    }
    array_push($result_cols, $result['finalResult']);
    fputcsv($output, $result_cols);
}

function sort_based_on_list_of_values($a, $b)
{
    global $q_default_order;
    $cmpa = array_search($a['question'], $q_default_order);
    $cmpb = array_search($b['question'], $q_default_order);
    return ($cmpa > $cmpb) ? 1 : -1;
}