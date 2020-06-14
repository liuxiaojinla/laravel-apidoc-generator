<?php

namespace Xin\ApiDoc\Http;

use Illuminate\Support\Facades\Storage;

class Controller{

	public function html(){
		return view('vendor.apidoc.index');
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function json(){
		return response()->json(
			json_decode(Storage::disk(config('apidoc.storage'))->get('apidoc/collection.json'))
		);
	}
}
