<?php
hola error
header("Content-type:application/json");
session_start();
if (empty($_SESSION['bd']['centros'])) {
    $_SESSION['bd']['centros'] = ['Stucom', 'UAB', 'Academia Pérez'];
}
if (empty($_SESSION['bd']['alumnos'])) {
    $_SESSION['bd']['alumnos'] = ['Ana', 'Eva', 'Juan'];
}

$verbo = $_SERVER['REQUEST_METHOD'];
$id = filter_input(INPUT_GET, 'id');
$tabla = filter_input(INPUT_GET, 'tabla');
$funcion = filter_input(INPUT_GET, 'funcion');
if (empty($tabla)) {
    $tabla = 'centros';
}
$message = new stdClass();
$message->verbo = $verbo;
$message->tabla = $tabla;
switch ($verbo) {
    case 'GET':
        $message->result = "Ok";
        if ($funcion == "largo") {

            $largo = "";
            foreach ($_SESSION['bd'][$tabla] as $elemento) {
                if (strlen($elemento) > strlen($largo)) {
                    $largo = $elemento;
                }
            }
            $message->data = $largo;
        } elseif (empty($id)) {
            $message->data = $_SESSION['bd'][$tabla];
            //2
        } else {
            $message->data = $_SESSION['bd'][$tabla][$id];
            if (!isset($_SESSION['bd'][$tabla][$id]))
                $message->result = "Registo $id no encontrado";
        }
        break;

    case 'POST':
// funcion que añade 20 de golpe
//Recuperamos los datos en crudo y los decodificamos
        $datos = file_get_contents("php://input");
        $centro = json_decode($datos);
        if ($funcion == "masiva") {
            for ($i = 0; $i < 20; $i++) {
                foreach ($centro as $nombre) {
                    $_SESSION['bd'][$tabla][] = $nombre;
                }
            }
            $message->data = $_SESSION['bd'][$tabla];
        } elseif (!empty($centro)) {
            $_SESSION['bd'][$tabla][] = $centro->nombre;
            $message->result = "Ok";
            $message->data = ['Elementos' => count($_SESSION['bd'][$tabla]), 'Nuevo' => $centro->nombre];
        } else {
            $message->result = "Error, faltan datos";
            $message->data = $datos;
        }

        break;
    case 'PUT':

        $datos = file_get_contents("php://input");
        $centro = json_decode($datos);
        if (!empty($centro)) {
            if (!empty($id)) {
                $message->data = ['id' => $id, 'Antiguo' => $_SESSION['bd'][$tabla][$id], 'Nuevo' => $centro->nombre];
                $_SESSION['bd'][$tabla][$id] = $centro->nombre;
                $message->result = "Ok";
            } else {
                $message->result = "Error, falta id";
                $message->data = null;
            }
        } else {
            $message->result = "Error, faltan datos";
            $message->data = $datos;
        }

        break;
    case 'DELETE':
        $datos = file_get_contents("php://input");
        $alumno = json_decode($datos);
        $a=[];
        if ($funcion == "masiva") {
            foreach ($alumno as $id) {
                array_push($a, $_SESSION['bd'][$tabla][$id]);
                unset($_SESSION['bd'][$tabla][$id]);
                           
            }
            
            
            $message->data = $a;
        } elseif (!empty($id)) {
            $message->data = ['elementos' => count($_SESSION['bd'][$tabla]) - 1, 'id' => $id, 'Nombre' => $_SESSION['bd'][$tabla][$id]];
            unset($_SESSION['bd'][$tabla][$id]);
            $message->result = "Ok";
        } else {
            $message->result = "Error, falta id";
            $message->data = null;
        }
        break;
    default:
        $message->result = "Error: Acción no reconocida";
        $message->data = null;
}
echo json_encode($message);
