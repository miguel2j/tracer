<?php

include 'lib/Tracer.php';

$tracer = new \lib\Tracer();

$origin = 'Logroño';
$destiny = 'Ciudad Real';

if (isset($_POST['city'])){
    $getAllRoutes = $tracer->getCities()[intval($_POST['city'])];
}else{
    $getAllRoutes = '';
}

echo '<h1>Exercise 1:</h1><br><br>' .$tracer->getMinCostRoute($origin,$destiny). '
    <br><br>
    <h1>Exercise 2:</h1>
    <form id="form" action="" method="post">
        <select id="city" name="city" onchange="changeSelectCity()">
            <option value="-1">Select...</option>';

foreach ($tracer->getCities() as $indexCity => $city){
    if ($city == $getAllRoutes){
        echo '<option value="' . $indexCity . '" selected>' . $city . '</option>';
    }else {
        echo '<option value="' . $indexCity . '">' . $city . '</option>';
    }
}

echo '</select></form>';

if ($getAllRoutes != ''){
    echo $tracer->getMinCostAllRoute($getAllRoutes);
}
?>
<script>
    function changeSelectCity() {
        if (document.getElementById('city').value != -1) {
            document.getElementById('form').submit();
        }
    }
</script>
