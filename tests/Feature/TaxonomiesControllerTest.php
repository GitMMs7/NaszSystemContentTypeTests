<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\ContentTypes\Enums\TaxonomyField;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Tests\TestCase;

class TaxonomiesControllerTest extends TestCase
{
    use DatabaseTransactions;
    use CreateUsers;

    public function testIndex()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $taxonomies = Taxonomy::factory(15)->create();
        $taxonomy = $taxonomies->random();
        $params = ['search' => $taxonomy->label];

        $response = $this->get(route('content-types.taxonomies.index', $params));

        $response->assertStatus(200);
        $this->assertStringContainsString($taxonomy->label, $response->content());
    }

    public function testCreate()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('content-types.taxonomies.create'));

        $response->assertStatus(200);
        $this->assertStringContainsString(TaxonomyField::Label()->description, $response->content());
    }

    public function testEdit()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $taxonomy = Taxonomy::factory()->create();

        $response = $this->get(route('content-types.taxonomies.show', $taxonomy));

        $response->assertStatus(200);
        $this->assertStringContainsString($taxonomy->label, $response->content());
    }
}
