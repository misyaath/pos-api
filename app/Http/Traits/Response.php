<?php


namespace App\Http\Traits;


use Illuminate\Http\JsonResponse;

trait Response
{

    protected static $_STATUS_ERROR = 'error';
    protected static $_STATUS_SUCCESS = 'success';
    protected static $_STATUS_FAILED = 'failed';

    /**
     * Response to return api call
     * @param array $data
     * @param string $status
     * @param array $message
     * @return JsonResponse
     */
    public function returnResponse(array $data, string $status, $message = [])
    {
        $return = [
            'status' => $status,
            'data' => $data,
        ];
        if ($status === self::$_STATUS_ERROR || self::$_STATUS_FAILED) :
            $return['status'] = $message;
        endif;
        return response()->json($return);
    }
}
