<?php

namespace classes;

class Tracer
{

    private $cities=['Logroño','Zaragoza','Teruel','Madrid','Lleida','Alicante','Castellón','Segovia','Ciudad Real'];
    private $connections=[[0,4,6,8,0,0,0,0,0],
        [4,0,2,0,2,0,0,0,0],
        [6,2,0,3,5,7,0,0,0],
        [8,0,3,0,0,0,0,0,0],
        [0,2,5,0,0,0,4,8,0],
        [0,0,7,0,0,0,3,0,7],
        [0,0,0,0,4,3,0,0,6],
        [0,0,0,0,8,0,0,0,4],
        [0,0,0,0,0,7,6,4,0]];

    private $tableTrips = [];

    private $itinerarys = array();

    public function __construct()
    {
        $this->getTableTrips();
    }

    public function getCities(){
        return $this->cities;
    }

    private function getTableTrips(){
        foreach ($this->connections as $indexOrigin => $conection){
            foreach ($conection as $indexDestiny => $cost) {
                $itinerary = ['origin' => $this->cities[$indexOrigin], 'destiny' => $this->cities[$indexDestiny], 'cost' => $cost];

                if ($cost == 0){
                    continue;
                }elseif (count($this->tableTrips) > 0 && $this->existRoute($this->tableTrips, $itinerary)){
                    continue;
                }
                array_push($this->tableTrips, $itinerary);
            }
        }
    }

    public function getMinCostRoute($origin, $destiny){
        $origin = array_keys($this->cities, $origin)[0];
        $destiny = array_keys($this->cities, $destiny)[0];

        $resultHTML = '';
        $close = false;
        $currentRoute = array();

        array_push($currentRoute, $origin);
        $this->getRoutes($origin, $destiny, $currentRoute);

        $currentCost = -1;
        foreach ($this->itinerarys as $itinerary){
            if ($currentCost > $itinerary['cost'] || $currentCost == -1){
                $shortRoutes = array();
                $currentCost = $itinerary['cost'];
                array_push($shortRoutes, $itinerary['route']);
            }elseif ($currentCost == $itinerary['cost']){
                array_push($shortRoutes, $itinerary['route']);
            }else{
                continue;
            }
        }

        $shortRoutes = $this->getItirenary($shortRoutes);

        $resultHTML .= '<h1>El trayecto/s más económico/s desde <span style="color: blue">' . $this->cities[$origin] . '</span> hasta: <span style="color: blue">' . $this->cities[$destiny] . '</span> sería/n el/los siguiente/s:</h1>
            <table style="border: 1px solid black; margin-bottom: 2vh">
                    <thead>
                        <th>Origen</th>
                        <th>Destino</th>
                    </thead>
                    <tbody>';

        foreach ($shortRoutes as $trip){
            if (is_int($trip)) {
                if ($trip < 0 || is_string($trip)) {
                    $trip = abs($trip);
                    $resultHTML .= '<tr>
                    <td>' . $this->tableTrips[$trip]['destiny'] . '</td>
                    <td>' . $this->tableTrips[$trip]['origin'] . '</td>
                    </tr>';
                } else {
                    $resultHTML .= '<tr>
                    <td>' . $this->tableTrips[$trip]['origin'] . '</td>
                    <td>' . $this->tableTrips[$trip]['destiny'] . '</td>
                    </tr>';
                }
            }else{
                foreach ($trip as $indexTrip){
                    if ($indexTrip < 0 || is_string($indexTrip)) {
                        $indexTrip = abs($indexTrip);
                        if ($close){
                            $resultHTML .= '<table style="border: 1px solid black; margin-bottom: 2vh">
                                <thead>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                </thead>
                                <tbody>';

                            $close = false;
                        }
                        $resultHTML .= '<tr>
                            <td>' . $this->tableTrips[$indexTrip]['destiny'] . '</td>
                            <td>' . $this->tableTrips[$indexTrip]['origin'] . '</td>
                            </tr>';
                    } else {
                        if ($close){
                            $resultHTML .= '<table style="border: 1px solid black; margin-bottom: 2vh">
                                <thead>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                </thead>
                                <tbody>';

                            $close = false;
                        }
                        $resultHTML .= '<tr>
                            <td>' . $this->tableTrips[$indexTrip]['origin'] . '</td>
                            <td>' . $this->tableTrips[$indexTrip]['destiny'] . '</td>
                            </tr>';
                    }
                }

                $resultHTML .= '</tbody></table>';
                $close = true;
            }
        }

        $resultHTML .= '</tbody></table>
                    <div style="border-bottom: 1px solid black"><h2>El coste total seria: ' .$currentCost. '</h2></div>';

        return $resultHTML;
    }

    public function getMinCostAllRoute($origin){
        $resultHTML = '';

        foreach ($this->cities as $indexCity => $city){
            $this->itinerarys = array();
            if ($city != $origin) {
                $route = $this->getMinCostRoute($origin, $city);
                $resultHTML .= $route;
            }
        }
        return $resultHTML;
    }

    private function getRoutes($origin, $destiny, $currentRoute, $currentCost = 0, $tried = array()){
        foreach ($this->connections[$origin] as $indexConection => $cost){
            if ($cost == 0){
                continue;
            }

            if ($indexConection == $destiny){
                array_push($currentRoute, $indexConection);
                if (!$this->used($currentRoute)) {
                    $currentCost += $cost;
                    array_push($this->itinerarys, ['route' => $currentRoute, 'cost' => $currentCost]);
                }
                if (count($currentRoute) > 2) {
                    return true;
                }
            }elseif (!in_array($indexConection, $currentRoute) && $indexConection != $origin && !in_array($indexConection, $tried)) {
                array_push($currentRoute, $indexConection);
                $currentCost += $cost;

                $getRoutes = $this->getRoutes($indexConection, $destiny, $currentRoute, $currentCost, $tried);
                if(is_bool($getRoutes)){
                    array_pop($currentRoute);
                    $currentCost -= $cost;
                    array_push($tried, $indexConection);
                }
            }

        }
        return false;
    }
    
    private function existRoute($tabla, $check){
        foreach ($tabla as $itinerario){
            if ($itinerario['origin'] == $check['destiny'] && $itinerario['destiny'] == $check['origin']){
                return true;
            }
        }
        return false;
    }

    private function getItirenary($routes){
        $trips = array();
        $currentTrip = array();
        $lastPosition = -1;
        foreach ($routes as $positions){
            $currentTrip = array();
            $lastPosition = -1;

            foreach ($positions as $position){
                if ($lastPosition == -1){
                    $lastPosition = $position;
                }elseif ($lastPosition != $position){
                    if (!is_bool($trip = $this->getTrip($lastPosition, $position))){
                        array_push($currentTrip, $trip);
                    }
                    $lastPosition = $position;
                }
            }
            array_push($trips, $currentTrip);
        }
        return $trips;
    }

    private function getTrip($origin, $destiny){
        foreach ($this->tableTrips as $indexTrip => $trip){
            if ($trip['origin'] == $this->cities[$origin] && $trip['destiny'] == $this->cities[$destiny]){
                return $indexTrip;
            }elseif ($trip['origin'] == $this->cities[$destiny] && $trip['destiny'] == $this->cities[$origin]){
                if ($indexTrip == 0){
                    return '-0';
                }
                return -$indexTrip;
            }
        }
        return false;
    }

    private function used($route){
        foreach ($this->itinerarys as $itinerary){
            if ($itinerary['route'] === $route){
                return true;
            }
        }
        return false;
    }

}