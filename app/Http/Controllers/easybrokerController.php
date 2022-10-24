<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use App\Models\pages;

class easybrokerController extends Controller
{
    // Funcion para recorrer las paginas
    public function startPages()
    {
        //En que pagina me quede
        $pageStart = pages::select('page')->latest()->first();
        
        
        $pagination = $pageStart['page'];
        $loadingPages = false;

        //Inicio el recorrido de las paginas
        while ($loadingPages == false) {

            //Inicio a recorrer la pagina
            $loadingPages = $this->getClients($pagination);

            //Valido si termine el recorrido
            if($loadingPages == false){
                $pagination++;
            }
        }
        
        //Guardo en que pagina me quede
        $pageDB = new pages();
        $pageDB->page = $pagination-1;
        $pageDB->items = 0;
        $pageDB->save();

    }

    //Funcion para recuperar clientes
    public function getClients($pagination)
    {
        $clientes = collect();
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'X-Authorization' => 'wxs7dvrhx5meg7c1r2dpifeni1te64',
        ])->get('https://api.easybroker.com/v1/contact_requests?page='.$pagination);

        $result = json_decode($response);

        /*
        *   dd($result);
        */

        if (empty($result->content)) {

            /*
            *   dd('vacio');
            */

            return true;
            //Termino el recorrido por que la pagina siguiente esta vacia

        } else {

            /*
            *   dd('tiene algo');
            */

            $i = 0;

            //Inicio a recorrer los clientes y a los preparo para enviarlos a HS
            foreach ($result->content as $key) {
                $nombre = $key->name;
                $telefono = $key->phone;
                $correo = $key->email;
                $mensaje = $key->message;
                $propiedad = $this->getProperties($key->property_id);
                $item = array(
                    'name' => $nombre,
                    'phone' => $telefono,
                    'email' => $correo,
                    'message' => $mensaje,
                    'propertie' => $propiedad->public_url,
                    'id_propertie' => $propiedad->public_id,
                    'id_internal_propertie' => $propiedad->internal_id,
                    'title_propertie' => $propiedad->title,

                );

                $clientes = Arr::add($clientes, $i, $item);
                $i++;

                /*
                * dd($client_item['name']);
                */
            }

            /*
            *   dd($clientes);
            */
            $sendToHubspot = $this->sendHubspot($clientes);
            return false;
            
            //Envio la data a HS
            //$sendToHubspot = $this->sendHubspot($clientes);
        }
    }

    //Funcion para recuperar propiedades
    public function getProperties($id_propertie)
    {
        //Recupero las propiedades
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'X-Authorization' => 'wxs7dvrhx5meg7c1r2dpifeni1te64',
        ])->get('https://api.easybroker.com/v1/properties/' . $id_propertie);
        $result = json_decode($response);
        return ($result);
    }

    //Funcion para enviar la data de los clientes a Hubspot
    public function sendHubspot($clientes)
    {
        /*
        *   dd($clientes[0]);
        */
        
        foreach($clientes as $itemClients){
            
        }
    }
}
