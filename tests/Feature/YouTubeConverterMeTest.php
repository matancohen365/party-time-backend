<?php

namespace Tests\Feature;

use Exception;
use GuzzleHttp\Client;
use Tests\TestCase;
use Tuna\YouTube\Api as YouTube;
use Tuna\YouTubeConverterMe\Api;

class YouTubeConverterMeTest extends TestCase
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \Tuna\YouTube\Api
     */
    private $yt;

    /**
     * @var \Tuna\YouTubeConverterMe\Api
     */
    private $api;

    public function setUp()
    {
        parent::setUp();

        $this->client = resolve(Client::class);

        $this->api = new Api($this->client);
        $this->yt = resolve(YouTube::class);

    }

    public function testRealVideo()
    {

        $this->assertUri(
            $this->getDownloadUrl('D5drYkLiLI8')
        );
    }

    public function testFakeVideo()
    {
        $this->expectException(Exception::class);

        $this->getDownloadUrl('D5drYkLiL-8');
    }

    public function testOriginalVideo()
    {
        $faker = \Faker\Factory::create();

        $videos = $this->yt->searchVideos($faker->sentence(2));

        $this->assertUri(
            $this->getDownloadUrl($videos->first())
        );
    }

    private function getDownloadUrl($video)
    {
        if( is_string($video) )
            $video = $this->yt->video($video);

        return $this->api->getDownloadUrl($video);
    }

    private function assertUri($string)
    {
        $this->assertTrue(filter_var($string) !== false);
    }
}
