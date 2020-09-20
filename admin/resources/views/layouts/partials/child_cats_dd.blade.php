@php

echo "<select name='child_cats_id_add'>";

$hasSelections = false;
foreach($catsCollArr as $id => $title) {
    // can't set itself as a child to itself
    // if already set as a child
    if ($id == $currentId || (isset($parentChildArr[$currentId]) && in_array($id, $parentChildArr[$currentId]))) {
        continue;
    }
    // if the cat id is a parent only, don't allow it to be set as a child
    $isParentOnly = false;
    foreach( $parentChildHierArr as $key => $arr) {
        if ($arr['child_id'] == $id) {
            $isParentOnly = true;
            break;
        }
    }
    if ($isParentOnly) {
        continue;
    }
    $hasSelections = true;
    echo "<option value='" . $id . "' ";
    echo ">" . $title . "</option>";
}

if ($hasSelections) {
    echo "<option selected value='0'>Set Child Category</option>";
} else {
    echo "<option selected value='0'>No Available Categories</option>";
}
echo "</select>";


@endphp