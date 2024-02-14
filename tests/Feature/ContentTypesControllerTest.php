<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\ContentTypes\Enums\ContentTypeField;
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\Tests\TestCase;

class ContentTypesControllerTest extends TestCase
{
    use DatabaseTransactions;
    use CreateUsers;

    public function testIndex()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $contentTypes = ContentType::factory(15)->create();
        $contentType = $contentTypes->random();
        $params = ['search' => $contentType->label];

        $response = $this->get(route('content-types.index', $params));

        $response->assertStatus(200);
        $this->assertStringContainsString($contentType->label, $response->content());
    }

    public function testCreate()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('content-types.create'));

        $response->assertStatus(200);
        $this->assertStringContainsString(ContentTypeField::Label()->description, $response->content());
    }

    public function testEdit()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $contentType = ContentType::factory()->create();

        $response = $this->get(route('content-types.show', $contentType));

        $response->assertStatus(200);
        $this->assertStringContainsString($contentType->label, $response->content());
    }
}
