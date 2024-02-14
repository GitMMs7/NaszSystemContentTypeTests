<?php

namespace SCTeam\ContentTypes\Tests;

use SCTeam\ContentTypes\SCTeamServiceProvider;

class TestCase extends \SCTeam\Base\Tests\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SCTeamServiceProvider::class,
        ];
    }
}