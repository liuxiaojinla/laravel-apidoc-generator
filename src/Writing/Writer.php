<?php

namespace Xin\ApiDoc\Writing;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Xin\ApiDoc\Tools\DocumentationConfig;

class Writer{

	/**
	 * @var Command
	 */
	protected $output;

	/**
	 * @var DocumentationConfig
	 */
	private $config;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * @var bool
	 */
	private $forceIt;

	/**
	 * @var array
	 */
	private $channels = [];

	/**
	 * @var string
	 */
	private $outputPath;

	public function __construct(Command $output, DocumentationConfig $config = null, bool $forceIt = false){
		// If no config is injected, pull from global
		$this->config = $config ?: new DocumentationConfig(config('apidoc'));
		$this->baseUrl = $this->config->get('base_url') ?? config('app.url');
		$this->forceIt = $forceIt;
		$this->output = $output;
		$this->channels = $this->config->get('channels', []);
		$this->outputPath = $this->config->get('output_folder', 'public/docs');
	}

	/**
	 * @param \Illuminate\Support\Collection $routes
	 * @throws \Exception
	 */
	public function writeDocs(Collection $routes){
		foreach($this->channels as $channel){
			$channelConfig = $this->config->get('writers.'.$channel, []);

			if('postman' === $channel){
				$this->writePostmanCollection($routes, $channelConfig);
				continue;
			}

			$driver = $channelConfig['driver'];
			/** @var WriterInterface $writer */
			$writer = app()->makeWith(
				$driver,
				[
					'routeGroups' => $routes,
					'baseUrl'     => $this->baseUrl,
					'config'      => $channelConfig,
				]
			);
			$writer->write();
		}
	}

	/**
	 * @param \Illuminate\Support\Collection $parsedRoutes
	 * @throws \Exception
	 */
	protected function writePostmanCollection(Collection $parsedRoutes, array $channelConfig){
		$this->output->info('Generating Postman collection');

		$collection = $this->generatePostmanCollection($parsedRoutes);
		$isStatic = $channelConfig['type'] == 0;
		if($isStatic){
			if(!FileFacade::isDirectory($this->outputPath)){
				FileFacade::makeDirectory($this->outputPath);
			}

			$collectionPath = "{$this->outputPath}/collection.json";
			file_put_contents($collectionPath, $collection);
		}else{
			$storage = $channelConfig['storage'];
			$storageInstance = Storage::disk($storage);
			$storageInstance->put('apidoc/collection.json', $collection, 'public');
			if($this->config->get('storage') == 'local'){
				$collectionPath = 'storage/app/apidoc/collection.json';
			}else{
				$collectionPath = $storageInstance->url('collection.json');
			}
		}

		$this->output->info("Wrote Postman collection to: {$collectionPath}");
	}

	/**
	 * Generate Postman collection JSON file.
	 *
	 * @param Collection $routes
	 * @return string
	 * @throws \Exception
	 */
	public function generatePostmanCollection(Collection $routes){
		/** @var PostmanCollectionWriter $writer */
		$writer = app()->makeWith(
			PostmanCollectionWriter::class,
			['routeGroups' => $routes, 'baseUrl' => $this->baseUrl]
		);

		return $writer->getCollection();
	}

}
