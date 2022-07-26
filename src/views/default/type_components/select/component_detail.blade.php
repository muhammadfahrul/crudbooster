<?php
$joinPk = 'id';
if (isset($form['join_pk'])) {
    $joinPk = $form['join_pk'];
}
if ($form['datatable']) {
    $datatable = explode(',', $form['datatable']);
    $table = $datatable[0];
    $field = $datatable[1];
    // echo CRUDBooster::first($table, ['id' => $value])->$field;
    echo CRUDBooster::first($table, [$joinPk => $value])->$field;
}
if ($form['dataquery']) {
    $dataquery = $form['dataquery'];
    $query = DB::select(DB::raw($dataquery));
    if ($query) {
        foreach ($query as $q) {
            if ($q->value == $value) {
                echo $q->label;
                break;
            }
        }
    }
}
if ($form['dataenum']) {
    echo $value;
}
?>