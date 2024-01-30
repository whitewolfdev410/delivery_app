<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;

class DeliveryController extends Controller
{

    public function delivery(Request $request) {
        $service_id = $request->service_id;
        $delivery_api = $this->getDeliveryApi($service_id);
        if(!$delivery_api) {
            return response()->json(['error' => 'Service not found']);
        }

        $bodyDataType = $this->getDeliveryBodyType($service_id);

        // Split the $bodyDataType string into an array of keys
        $keys = explode(',', $bodyDataType);

        // Initialize an array to store request data for each key
        $requestData = [];

        // Iterate through each key and retrieve request data
        foreach ($keys as $key) {
            // Access request data for each key
            $requestData[$key] = $request->{$key};
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $delivery_api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            return response()->json(['error' => 'Delivery order failed' . curl_error($ch)], 500);
        }
        curl_close($ch);
        // we can save the request data and response status in table, so if the response status is Success, then we can know this order is success.

        return response()->json(['Success' => 'Success'], 200);
    }

    public function getDeliveryApi($id) {
        // Delivery model is table for saving the delivery sites information like api, request body parameters.
        $serviceApi = Delivery::where('id', $id)->value('service_api');

        return $serviceApi;
    }
    public function getDeliveryBodyType($id) {
        //This will return the request body type about the selected service
        $bodyData = Delivery::where('id', $id)->value('body_type');
        return $bodyData;
    }

}
