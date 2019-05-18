<?php namespace App\Http\Controllers;

use App\Track;
use Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

class Api2Controller
{
    /**
     * Get track info
     *
     * @param Request $request
     * @return \App\Track
     */
    public function info(Request $request)
    {
        $url = $request->input('url');

        if(filter_var($url, FILTER_VALIDATE_URL) === false)
            abort(404);

        /** @var Track $track */
        $track = (new Track)->firstOrCreate(compact('url'))->refresh();

        if( ! $track->hasMetadata()) {

            dd($track->getMetadata());

        }

        $track->download();

        return $track;
    }

    /**
     * Download a file
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request)
    {
        $track = (new Track)->where('url', $request->input('url'))->firstOrFail();
        
        if ($track->file->exists()) {
        
            BinaryFileResponse::trustXSendfileTypeHeader();
        
            return response()->download(
                $track->file->getFile(),
                $track->download['name'],
                [
                    'X-Sendfile' => $track->file->getFile()
                    'X-Name'     => rawurlencode($track->download['name']),
                ]
            )->deleteFileAfterSend(true);
        }

        abort(404);
    }
}