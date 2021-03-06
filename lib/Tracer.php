<?php

namespace lib;

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

    //obtiene los viajes que existen
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

    //devuelve codigo html con la ruta mas economica entre el origen y el destino
    public function getMinCostRoute($origin, $destiny){
        $origin = array_keys($this->cities, $origin)[0];
        $destiny = array_keys($this->cities, $destiny)[0];

        $resultHTML = '';
        $close = false;
        $currentRoute = array();

        array_push($currentRoute, $origin);
        $this->getRoutes($origin, $destiny, $currentRoute);

        //obtiene la ruta mas economica de entre todas las rutas posibles
        $shortRoutes = array();
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

        $resultHTML .= '<h1>The cheapest journey/s from <span style = "color: blue">' . $this->cities[$origin] . '</span> to <span style="color: blue">' . $this->cities[$destiny] . '</span> are be the next/s:</h1>
            <h3>Route 1:</h3>
            <table style="border: 1px solid black; margin-bottom: 2vh">
                    <thead>
                        <th>Origin</th>
                        <th>Destiny</th>
                    </thead>
                    <tbody>';

        foreach ($shortRoutes as $indexShort => $trip){
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
                            $resultHTML .= '<h3>Route ' .($indexShort+1). ':</h3>
                                <table style="border: 1px solid black; margin-bottom: 2vh">
                                <thead>
                                    <th>Origin</th>
                                    <th>Destiny</th>
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
                            $resultHTML .= '<h3>Route ' .($indexShort+1). ':</h3>
                                <table style="border: 1px solid black; margin-bottom: 2vh">
                                <thead>
                                    <th>Origin</th>
                                    <th>Destiny</th>
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
                    <div style="border-bottom: 1px solid black"><h2>Total Cost: ' .$currentCost. '</h2></div>';

        return $resultHTML;
    }

    //devuelve codigo html con la ruta mas economica entre el origen y todos los destinos
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

    //obtiene todas las rutas posibles entre el origej y el destino
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

    //devuelve un boolean true si exisete el valor de $in dentro del array $used
    private function existRoute($used, $in){
        foreach ($used as $itinerary){
            if ($itinerary['origin'] == $in['destiny'] && $itinerary['destiny'] == $in['origin']){
                return true;
            }
        }
        return false;
    }

    //obtiene los viajes dadas las posiciones posibles del array $routes
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

    //obtiene el viaje existente para ir del punto origen al punto de destino dados
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

    //devuelve un boolean true si esa ruta ya esta siendo usada
    private function used($route){
        foreach ($this->itinerarys as $itinerary){
            if ($itinerary['route'] === $route){
                return true;
            }
        }
        return false;
    }

}