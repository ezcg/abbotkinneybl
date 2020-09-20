@php

    // Checkboxes for the parent cats
    if (!isset($parentChildArr[$currentId])) {
        return;
    }

    foreach($parentChildArr[$currentId] as $childId) {

        foreach($catsCollArr as $id => $title ){
            // only compare toplevel child_id as parent_id is 0
            if ($id == $childId) {
                echo $title;
            }
        }
        echo ":<input type='checkbox' name='child_id_arr[]' value='" . $childId . "' checked> | ";

    }


@endphp